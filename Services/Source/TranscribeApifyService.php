<?php

namespace App\Services\Source;

use App\Enums\Source\PostTranscribeStatus;
use App\Models\Source\PostTranscribe;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\ApifyJobPersister;

class TranscribeApifyService
{
    public function createJob(
        PostTranscribe $postTranscribe,
        ActorFabric $actorFabric,
        ApifyJobPersister $jobPersister,
    ) {
        $actorPosts = $actorFabric->createActor([$postTranscribe->post->source->type, 'post']);
        $runPost = $actorPosts->run([
            'username' => $postTranscribe->post->post_url,
            'limit' => 1,
        ]);

        $jobPost = $jobPersister->createFromRunResult(
            $runPost,
            $actorPosts->getActorId(),
            $postTranscribe->user_id,
            $postTranscribe->team_id,
        );

        $postTranscribe->update([
            'job_id' => $jobPost->id,
        ]);

        return $jobPost;
    }

    public function syncFileUrlFromSucceededJob(PostTranscribe $postTranscribe, ActorFabric $actorFabric): bool
    {
        $postActor = $actorFabric->createActorByActorId($postTranscribe->job->actor);
        $postItems = $postActor->parseDatasetItems($postTranscribe->job->job_result);

        foreach ($postItems as $postItem) {
            if (mb_stripos($postItem->url(), $postTranscribe->post->post_url) === 0 && $postItem->videoFileUrl()) {
                $postTranscribe->update([
                    'file_url' => $postItem->videoFileUrl(),
                ]);

                return true;
            }
        }

        $postTranscribe->update([
            'status' => PostTranscribeStatus::FAILED->value,
        ]);

        return false;
    }
}
