<?php

namespace App\Services\Apify;

use Illuminate\Support\Facades\Log;

class ActorFabric
{
    private array $actors;

    public function __construct(private Client $client)
    {
        $this->actors = config('services.apify.actors');
    }

    public function createActor(array $type): ActorInterface
    {
        foreach ($this->actors as $actor) {
            if (array_diff($type, $actor['tags']) === [] && array_diff($actor['tags'], $type) === []) {
                $actorPath = $actor['actor'];
                $actorClass = 'App\Services\Apify\Actors\\' . $actorPath . '\Actor';
                if (is_subclass_of($actorClass, ActorInterface::class)) {
                    return new $actorClass($this->client);
                }
            }
        }

        throw new \Exception('Actor not found for type: ' . implode(', ', $type));
    }

    public function createActorByActorId(string $actorId): ActorInterface
    {
        $path = __DIR__ . '/Actors/';

        $files = glob($path . '*');

        foreach ($files as $file) {
            $className = 'App\Services\Apify\Actors\\' . basename($file, '.php') . '\Actor';
            if (is_subclass_of($className, ActorInterface::class)) {
                $actor = new $className($this->client);
                if ($actor->getActorId() === $actorId) {
                    return $actor;
                }
            }
        }

        throw new \Exception('Actor not found for actorId: ' . $actorId);
    }
}