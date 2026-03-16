<?php

namespace App\Services\Source;

use App\Enums\Source\PostTranscribeStatus;
use App\Models\Source\PostTranscribe;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class TranscribeMediaService
{
    public function download(PostTranscribe $postTranscribe): string
    {
        $path = $this->buildStoragePath($postTranscribe);
        $this->cleanup($postTranscribe);

        try {
            if (config('services.n8n.downloader')) {
                $client = new Client([
                    'verify' => false,
                    'timeout' => 120,
                ]);

                $response = $client->get(config('services.n8n.downloader'), [
                    'query' => ['url' => base64_encode($postTranscribe->file_url)],
                ]);

                $data = json_decode($response->getBody()->getContents(), true);
                file_put_contents($path, base64_decode($data['data'] ?? ''));

                return $path;
            }

            $client = new Client([
                'verify' => false,
                'timeout' => 120,
                'proxy' => config('services.transcribe.proxy'),
            ]);

            $response = $client->get($postTranscribe->file_url);
            file_put_contents($path, $response->getBody());

            return $path;
        } catch (\Throwable $exception) {
            Log::error('Ошибка при загрузке файла для транскрибации', [
                'id' => $postTranscribe->id,
                'error' => $exception->getMessage(),
            ]);

            $postTranscribe->update([
                'status' => PostTranscribeStatus::FAILED->value,
            ]);

            throw $exception;
        }
    }

    public function convertToWav(PostTranscribe $postTranscribe, string $path): string
    {
        $wavPath = $path . '.wav';
        exec('ffmpeg -i ' . escapeshellarg($path) . ' -vn -acodec pcm_s16le -ar 16000 -ac 1 ' . escapeshellarg($wavPath), $output, $code);

        if ($code !== 0) {
            $postTranscribe->update([
                'status' => PostTranscribeStatus::FAILED->value,
            ]);

            throw new RuntimeException('Не удалось конвертировать файл в wav');
        }

        return $wavPath;
    }

    public function cleanup(PostTranscribe $postTranscribe): void
    {
        $path = $this->buildStoragePath($postTranscribe);

        @unlink($path);
        @unlink($path . '.wav');
    }

    private function buildStoragePath(PostTranscribe $postTranscribe): string
    {
        return storage_path('app/' . sha1((string) $postTranscribe->file_url));
    }
}
