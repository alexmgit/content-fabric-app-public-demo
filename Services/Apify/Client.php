<?php

namespace App\Services\Apify;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class Client
{
    private readonly GuzzleClient $client;
    private readonly string $apiKey;
    private readonly string $apiVersion;
    private readonly string $apiUrl;

    public function __construct()
    {
        $this->apiKey = config('services.apify.api_key');
        $this->apiVersion = config('services.apify.api_version');
        $this->apiUrl = config('services.apify.api_url');

        $this->client = new GuzzleClient([
            'base_uri' => $this->apiUrl,
            'verify' => false,
        ]);
    }

    public function runActor(string $actor, array $options = []): array
    {
        return $this->request('post', '/' . $this->apiVersion . '/acts/' . $actor . '/runs', [
            'json' => $options,
        ], [
            'actor' => $actor,
            'options' => $options,
        ]);
    }

    public function getRun(string $runId): array
    {
        return $this->request('get', '/' . $this->apiVersion . '/actor-runs/' . $runId, [], [
            'run_id' => $runId,
        ]);
    }

    public function getDatasetItems(string $datasetId): array
    {
        return $this->request('get', '/' . $this->apiVersion . '/datasets/' . $datasetId . '/items', [], [
            'dataset_id' => $datasetId,
        ]);
    }

    private function request(string $method, string $uri, array $options = [], array $context = []): array
    {
        try {
            $requestOptions = array_replace_recursive([
                'query' => [
                    'token' => $this->apiKey,
                ],
            ], $options);

            Log::info('Apify API request', [
                'method' => $method,
                'uri' => $uri,
                'context' => $context,
            ]);

            $response = $this->client->request(strtoupper($method), $uri, $requestOptions);

            return $this->decodeResponse((string) $response->getBody(), $method, $uri, $context);
        } catch (GuzzleException $exception) {
            throw $this->buildRequestException($exception, $method, $uri, $context);
        }
    }

    private function decodeResponse(string $body, string $method, string $uri, array $context): array
    {
        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            Log::error('Apify API returned invalid JSON', [
                'method' => $method,
                'uri' => $uri,
                'context' => $context,
            ]);

            throw new RuntimeException('Apify API returned invalid JSON');
        }

        Log::info('Apify API response', [
            'method' => $method,
            'uri' => $uri,
            'context' => $context,
            'response' => $decoded,
        ]);

        return $decoded;
    }

    private function buildRequestException(
        GuzzleException $exception,
        string $method,
        string $uri,
        array $context
    ): RuntimeException {
        Log::error('Apify API request failed', [
            'method' => $method,
            'uri' => $uri,
            'context' => $context,
            'message' => $exception->getMessage(),
        ]);

        return new RuntimeException('Apify API request failed: ' . $exception->getMessage(), 0, $exception);
    }
}
