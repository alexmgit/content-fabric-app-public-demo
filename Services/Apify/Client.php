<?php

namespace App\Services\Apify;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Support\Facades\Log;

class Client
{
    private GuzzleClient $client;
    private string $apiKey;
    private string $apiVersion;
    private string $apiUrl;

    public function __construct() {

        $this->apiKey = config('services.apify.api_key');
        $this->apiVersion = config('services.apify.api_version');
        $this->apiUrl = config('services.apify.api_url');

        $this->client = new GuzzleClient([
            'base_uri' => $this->apiUrl,
            'verify' => false,
        ]);
    }

    public function runActor(string $actor, array $options = [])
    {
        try {
            Log::info('Running actor: ' . $actor);

            $response = $this->client->post('/' . $this->apiVersion . '/acts/' . $actor . '/runs', [
                'json' => $options,
                'query' => [
                    'token' => $this->apiKey,
                ],
            ]);

            $body = $response->getBody()->getContents();

            Log::info('Actor run response: ', ['response' => $body]);

            return json_decode($body, true);
        } catch (\Exception $e) {
            Log::error('Apify API error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getRun(string $runId)
    {
        try {
            $response = $this->client->get('/' . $this->apiVersion . '/actor-runs/' . $runId, [
                'query' => [
                    'token' => $this->apiKey,
                ],
            ]);
    
            $body = $response->getBody()->getContents();
    
            return json_decode($body, true);
        } catch (\Exception $e) {
            Log::error('Apify API error: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getDatasetItems(string $datasetId)
    {
        try {
            $response = $this->client->get('/' . $this->apiVersion . '/datasets/' . $datasetId . '/items', [
                'query' => [
                    'token' => $this->apiKey,
                ],
            ]);
    
            $body = $response->getBody()->getContents();
    
            return json_decode($body, true);
        } catch (\Exception $e) {
            Log::error('Apify API error: ' . $e->getMessage());
            throw $e;
        }
        
    }
}