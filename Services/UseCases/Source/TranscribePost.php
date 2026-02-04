<?php

namespace App\Services\UseCases\Source;

use App\Contracts\Logger;
use App\Enums\Apify\JobStatus;
use App\Enums\Source\PostTranscribeStatus;
use App\Models\Apify\Job;
use App\Models\Source\PostTranscribe;
use App\Services\Apify\ActorFabric;
use App\Services\GPTClient;

class TranscribePost
{
    private const JOB_CHECK_DELAY = 30;
    private const JOB_PROCESS_DELAY = 20;
    private const RETRY_DELAY = 10;

    public function __construct(
        private Logger $logger,
        private GPTClient $gptClient,
    )
    {
    }

    public function handle(PostTranscribe $postTranscribe, ActorFabric $actorFabric): TranscribePlan
    {
        if ($postTranscribe->status !== PostTranscribeStatus::WAITING->value) {
            return TranscribePlan::done();
        }

        if ($postTranscribe->job === null) {
            $job = $this->createJob($postTranscribe, $actorFabric);
            return TranscribePlan::dispatchJob($job, self::JOB_PROCESS_DELAY, self::JOB_CHECK_DELAY);
        }

        if (empty($postTranscribe->file_url)) {
            return $this->processJobResult($postTranscribe, $actorFabric);
        }

        if (empty($postTranscribe->transcription) && empty($postTranscribe->result)) {
            $this->processTranscription($postTranscribe);
        }

        return TranscribePlan::done();
    }

    public function failed(PostTranscribe $postTranscribe): void
    {
        $this->logger->info('Transcribe post fail', ['id' => $postTranscribe->id]);
     
        $path = storage_path('app/' . sha1($postTranscribe->file_url));

        @unlink($path);
        @unlink($path . '.wav');

        $this->markFailed($postTranscribe);
    }

    private function createJob(PostTranscribe $postTranscribe, ActorFabric $actorFabric): Job
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $actorPosts = $actorFabric->createActor([$postTranscribe->post->source->type, 'post']);

        $runPost = $actorPosts->run([
            'username' => $postTranscribe->post->post_url,
            'limit' => 1,
        ]);

        $jobPost = Job::create([
            'actor' => $actorPosts->getActorId(),
            'job_id' => $runPost->getRunId(),
            'job_options' => json_encode($runPost->getOptions()),
            'job_data' => json_encode($runPost->getData()),
            'job_status' => $runPost->getData()['status'] ?? JobStatus::CREATED->value,
            'job_error' => $runPost->getData()['statusMessage'] ?? '',
            'user_id' => $postTranscribe->user_id,
            'team_id' => $postTranscribe->team_id,
        ]);

        $postTranscribe->update([
            'job_id' => $jobPost->id,
        ]);

        return $jobPost;
    }

    private function processSucceededJob(PostTranscribe $postTranscribe, ActorFabric $actorFabric): void
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $postActor = $actorFabric->createActorByActorId($postTranscribe->job->actor);
        $postItems = $postActor->parseDatasetItems($postTranscribe->job->job_result);

        foreach ($postItems as $postItem) {
            if (mb_stripos($postItem->url(), $postTranscribe->post->post_url) === 0 && $postItem->videoFileUrl()) {
                $postTranscribe->update([
                    'file_url' => $postItem->videoFileUrl(),
                ]);

                $this->logger->info('Transcribe post. End ' . __METHOD__, ['id' => $postTranscribe->id]);

                return;
            }
        }

        $this->markFailed($postTranscribe);
    }

    private function processFailedJob(PostTranscribe $postTranscribe): void
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $this->markFailed($postTranscribe);
    }

    private function processDownloadFile(PostTranscribe $postTranscribe): string
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $path = storage_path('app/' . sha1($postTranscribe->file_url));

        @unlink($path);
        @unlink($path . '.wav');

        if (config('services.n8n.downloader')) {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 120,
            ]);

            try {
                $response = $client->get(config('services.n8n.downloader'), [
                    'query' => ['url' => base64_encode($postTranscribe->file_url)]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                file_put_contents($path, base64_decode($data['data'] ?? null));
            } catch (\Exception $e) {
                $this->logger->error('Ошибка при загрузке файла для транскрибации', [
                    'id' => $postTranscribe->id,
                    'error' => $e->getMessage(),
                ]);

                $this->markFailed($postTranscribe);

                throw $e;
            }
        } else {
            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'timeout' => 120,
                'proxy' => config('services.transcribe.proxy'),
            ]);
    
            try {
                $response = $client->get($postTranscribe->file_url);
    
                file_put_contents($path, $response->getBody());
            } catch (\Exception $e) {
                $this->logger->error('Ошибка при загрузке файла для транскрибации', [
                    'id' => $postTranscribe->id,
                    'error' => $e->getMessage(),
                ]);
    
                $this->markFailed($postTranscribe);
    
                throw $e;
            }
        }

        $this->logger->info('Transcribe post. End ' . __METHOD__, ['id' => $postTranscribe->id]);

        return $path;
    }

    private function processConvertFile(string $path, PostTranscribe $postTranscribe): string
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        exec('ffmpeg -i ' . $path . ' -vn -acodec pcm_s16le -ar 16000 -ac 1 ' . $path . '.wav');

        $this->logger->info('Transcribe post. End ' . __METHOD__, ['id' => $postTranscribe->id]);

        return $path . '.wav';
    }

    private function prcessTranscribeFile(string $path, PostTranscribe $postTranscribe): string
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $response = $this->gptClient->audio()->transcribe([
            'model' => 'whisper-1', 
            'file' => fopen($path, 'r'),
            'response_format' => 'verbose_json',
            'prompt' => 'Отвечай всегда на русском языке.',
            // 'timestamp_granularities' => ['word'],
        ]);

        $this->logger->info('Transcribe post. End ' . __METHOD__, ['id' => $postTranscribe->id]);

        return $response->text ?? '';
    }

    private function prcessAnalizeTranscribe(string $transcribe, PostTranscribe $postTranscribe): string
    {
        $this->logger->info('Transcribe post. Start ' . __METHOD__, ['id' => $postTranscribe->id]);

        $messages = [
            [
                'role' => 'system',
                'content' => <<<CONTENT
Ты получаешь на вход расшифровку видео и его описание.
Твоя цель — проанализировать материал и выдать структурированный отчёт, который поможет понять, 
почему видео могло стать вирусным, и как его использовать для вдохновения.

Алгоритм анализа:

Хук
- Определи первую фразу или момент, который цепляет внимание зрителя.
- Если хук слабый — предложи альтернативный вариант.
- Опиши почему именно этот хук работает, что делает его цепляющим 

Возможные причины вирусности
- Эмоции: вызывает ли смех, удивление, спор, эмпатию?
- Полезность: даёт ли быстрый совет, лайфхак, решение боли?
- Формат: монтаж, музыка, трендовый звук, визуал.
- Соц. триггеры: вызывает ли желание поделиться или обсудить?

Сильные элементы
- Что именно удерживает внимание (динамика, сторителлинг, факты, провокация).

Слабые места
- Где теряется внимание, что можно улучшить.

Теги
- Определи релевантные теги (по теме, нише, эмоциям, формату).
- От 5 до 10 тегов.

Важно:
- Ничего лишнего не пиши, только анализ
- Верни результат в формате json
- Не ссылайся на визуал в видео, потому что ты его не видел
CONTENT,
            ],
        ];

        $messages[] = [
            'role' => 'user',
            'content' => 'Описание видео: ' . ($postTranscribe->post->post_caption),
        ];

        $messages[] = [
            'role' => 'user',
            'content' => 'Расшифровка видео: ' . ($transcribe),
        ];

        $answer = $this->gptClient->chat()->create([
            'model' => 'gpt-4o',
            'temperature' => 0.6,
            'messages' => $messages,
            'response_format' => [
                'type' => 'json_schema',
                'json_schema' => [
                    'name' => 'reasoning_schema',
                    'strict' => true,
                    'schema' => [
                        'type' => 'object',
                        'properties' => [
                            'hook' => [
                                'type' => 'string',
                                'description' => 'Анализ хука/хуков'
                            ],
                            'viral_reasons' => [
                                'type' => 'string',
                                'description' => 'Анализ причин вирусности'
                            ],
                            'strong_points' => [
                                'type' => 'string',
                                'description' => 'Анализ сильных мест'
                            ],
                            'weak_points' => [
                                'type' => 'string',
                                'description' => 'Анализ слабых мест'
                            ],
                            'tags' => [
                                'type' => 'array',
                                'description' => 'Теги через запятую',
                                'items' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'required' => ['hook', 'viral_reasons', 'strong_points', 'weak_points', 'tags'],
                        'additionalProperties' => false,
                    ],
                ],
            ],
        ]);

        $this->logger->info('Transcribe post. End ' . __METHOD__, ['id' => $postTranscribe->id]);

        return $answer->choices[0]->message->content ?? '';
    }

    private function processJobResult(PostTranscribe $postTranscribe, ActorFabric $actorFabric): TranscribePlan
    {
        if ($postTranscribe->job->job_status === JobStatus::SUCCEEDED->value) {
            $this->processSucceededJob($postTranscribe, $actorFabric);
            return TranscribePlan::retry(self::RETRY_DELAY);
        }

        if ($postTranscribe->job->job_status === JobStatus::FAILED->value) {
            $this->processFailedJob($postTranscribe);
        }

        return TranscribePlan::done();
    }

    private function processTranscription(PostTranscribe $postTranscribe): void
    {
        $path = $this->processDownloadFile($postTranscribe);
        $pathWav = $this->processConvertFile($path, $postTranscribe);
        $transcribe = $this->prcessTranscribeFile($pathWav, $postTranscribe);
        $analize = $this->prcessAnalizeTranscribe($transcribe, $postTranscribe);
                
        $postTranscribe->update([
            'result' => $analize,
            'transcription' => $transcribe,
            'status' => PostTranscribeStatus::COMPLETE->value,
        ]);

        @unlink($path);
        @unlink($path . '.wav');

        $this->logger->info('Transcribe post end', ['id' => $postTranscribe->id]);
    }

    private function markFailed(PostTranscribe $postTranscribe): void
    {
        $postTranscribe->update([
            'status' => PostTranscribeStatus::FAILED->value,
        ]);
    }
}
