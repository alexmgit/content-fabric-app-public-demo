<?php

namespace App\Jobs\Source;

use App\Enums\Apify\JobStatus;
use App\Enums\Source\PostTranscribeStatus;
use App\Jobs\Apify\JobProcess;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Queue\Queueable;
use App\Models\Source\PostTranscribe;
use App\Services\GPTClient;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;
use App\Services\Source\TranscribeAiService;
use App\Services\Source\TranscribeApifyService;
use App\Services\Source\TranscribeMediaService;

class TranscribeProcess implements ShouldQueue, ShouldBeUnique
{
    use Queueable;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 5;
 
    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;
    
    /**
     * The backoff for the job.
     *
     * @var array
     */
    public $backoff = [10, 30, 60, 120, 240, 480, 960, 1920, 3840, 7680];
    
    /**
     * The number of seconds after which the job's unique lock will be released.
     *
     * @var int
     */
    public $uniqueFor = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(public PostTranscribe $postTranscribe)
    {
        //
    }

    public function uniqueId(): string
    {
        return $this->postTranscribe->id;
    }

    /**
     * Execute the job.
     */
    public function handle(
        ActorFabric $actorFabric,
        ApifyJobPersister $jobPersister,
        GPTClient $gptClient,
        TranscribeApifyService $transcribeApifyService,
        TranscribeMediaService $transcribeMediaService,
        TranscribeAiService $transcribeAiService,
    ): void {
        if ($this->postTranscribe->status !== PostTranscribeStatus::WAITING->value) {
            return;
        }

        if ($this->postTranscribe->job === null) {
            $job = $transcribeApifyService->createJob($this->postTranscribe, $actorFabric, $jobPersister);
            JobProcess::dispatch($job)->delay(20);
            self::dispatch($this->postTranscribe)->delay(30);
            return;
        }

        if (empty($this->postTranscribe->file_url)) {
            if ($this->postTranscribe->job->job_status === JobStatus::SUCCEEDED->value) {
                $synced = $transcribeApifyService->syncFileUrlFromSucceededJob($this->postTranscribe, $actorFabric);

                if ($synced) {
                    self::dispatch($this->postTranscribe)->delay(10);
                }

                return;
            }

            if ($this->postTranscribe->job->job_status === JobStatus::FAILED->value) {
                $this->markFailed();
            }

            return;
        }

        if (empty($this->postTranscribe->transcription) && empty($this->postTranscribe->result)) {
            $path = $transcribeMediaService->download($this->postTranscribe);
            $wavPath = $transcribeMediaService->convertToWav($this->postTranscribe, $path);

            try {
                $transcription = $transcribeAiService->transcribeAudio($gptClient, $wavPath);
                $analysis = $transcribeAiService->analyzeTranscription($gptClient, $this->postTranscribe, $transcription);

                $this->postTranscribe->update([
                    'result' => $analysis,
                    'transcription' => $transcription,
                    'status' => PostTranscribeStatus::COMPLETE->value,
                ]);

                Log::info('Transcribe post end', ['id' => $this->postTranscribe->id]);
            } finally {
                $transcribeMediaService->cleanup($this->postTranscribe);
            }
        }
    }

    public function failed(?Throwable $exception): void
    {
        Log::info('Transcribe post fail', ['id' => $this->postTranscribe->id]);

        $this->postTranscribe->update([
            'status' => PostTranscribeStatus::FAILED->value,
        ]);
    }

    private function markFailed(): void
    {
        $this->postTranscribe->update([
            'status' => PostTranscribeStatus::FAILED->value,
        ]);
    }
}
