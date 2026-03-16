<?php

namespace App\Services\Apify;

use RuntimeException;

class ActorFabric
{
    private readonly array $actors;

    public function __construct(private Client $client)
    {
        $this->actors = config('services.apify.actors');
    }

    public function createActor(array $type): ActorInterface
    {
        foreach ($this->actors as $actor) {
            if (array_diff($type, $actor['tags']) === [] && array_diff($actor['tags'], $type) === []) {
                return $this->makeActor($this->buildActorClass($actor['actor']));
            }
        }

        throw new RuntimeException('Actor not found for type: ' . implode(', ', $type));
    }

    public function createActorByActorId(string $actorId): ActorInterface
    {
        $path = __DIR__ . '/Actors/';

        $files = glob($path . '*');

        foreach ($files as $file) {
            $className = 'App\Services\Apify\Actors\\' . basename($file, '.php') . '\Actor';
            if (is_subclass_of($className, ActorInterface::class)) {
                $actor = $this->makeActor($className);
                if ($actor->getActorId() === $actorId) {
                    return $actor;
                }
            }
        }

        throw new RuntimeException('Actor not found for actorId: ' . $actorId);
    }

    private function buildActorClass(string $actorPath): string
    {
        return 'App\Services\Apify\Actors\\' . $actorPath . '\Actor';
    }

    private function makeActor(string $actorClass): ActorInterface
    {
        if (! is_subclass_of($actorClass, ActorInterface::class)) {
            throw new RuntimeException('Actor class does not implement contract: ' . $actorClass);
        }

        return new $actorClass($this->client);
    }
}
