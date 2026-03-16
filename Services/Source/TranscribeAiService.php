<?php

namespace App\Services\Source;

use App\Models\Source\PostTranscribe;
use App\Services\GPTClient;

class TranscribeAiService
{
    public function transcribeAudio(GPTClient $gptClient, string $path): string
    {
        $response = $gptClient->audio()->transcribe([
            'model' => 'whisper-1',
            'file' => fopen($path, 'r'),
            'response_format' => 'verbose_json',
            'prompt' => 'Отвечай всегда на русском языке.',
        ]);

        return $response->text ?? '';
    }

    public function analyzeTranscription(GPTClient $gptClient, PostTranscribe $postTranscribe, string $transcription): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => <<<'CONTENT'
Ты анализируешь вирусные видео для контент-команды.
Верни JSON с полями hook, viral_reasons, strong_points, weak_points, tags.
Ответ должен быть на русском языке.
CONTENT,
            ],
            [
                'role' => 'user',
                'content' => 'Описание видео: ' . ($postTranscribe->post->post_caption),
            ],
            [
                'role' => 'user',
                'content' => 'Расшифровка видео: ' . $transcription,
            ],
        ];

        $answer = $gptClient->chat()->create([
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
                            ],
                            'viral_reasons' => [
                                'type' => 'string',
                            ],
                            'strong_points' => [
                                'type' => 'string',
                            ],
                            'weak_points' => [
                                'type' => 'string',
                            ],
                            'tags' => [
                                'type' => 'array',
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

        return $answer->choices[0]->message->content ?? '';
    }
}
