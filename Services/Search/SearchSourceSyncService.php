<?php

namespace App\Services\Search;

use App\Enums\Search\SourceInterestLevel;
use App\Enums\Source\SourceTypes;
use App\Models\Search\Run;
use App\Models\Search\Source;
use App\Services\Apify\ActorFabric;
use App\Services\Apify\SearchParserInterface;
use App\Services\Apify\SearchPostParserInterface;
use App\Services\SourceInterestDetector;

class SearchSourceSyncService
{
    public function __construct(
        private readonly SourceInterestDetector $sourceInterestDetector,
    ) {
    }

    public function syncFromSucceededSearchJob(Run $run, ActorFabric $actorFabric): void
    {
        $searchJob = $run->searchJob;
        $searchActor = $actorFabric->createActorByActorId($searchJob->actor);
        $searchItems = $searchActor->parseDatasetItems($searchJob->job_result);
        $accounts = $this->aggregateAccounts($searchItems);

        foreach ($accounts as $account => $data) {
            $hash = md5($account);

            $source = Source::query()
                ->where('hash', $hash)
                ->where('user_id', $run->user_id)
                ->first() ?? new Source([
                    'search_id' => $run->search_id,
                    'run_id' => $run->id,
                    'user_id' => $run->user_id,
                    'team_id' => $run->team_id,
                    'source_type' => $run->search->search_type,
                    'source_url' => SourceTypes::makeUrl($run->search->search_type, $account),
                    'hash' => $hash,
                ]);

            $source->source_follows_count = 0;
            $source->source_followers_count = 0;
            $source->source_posts_count = 0;
            $source->search_views_count = $data['views'];
            $source->search_likes_count = $data['likes'];
            $source->search_comments_count = $data['comments'];
            $source->interest_level = $this->sourceInterestDetector->detect($source);

            if ($source->interest_level !== SourceInterestLevel::LOW) {
                $source->save();
            }
        }
    }

    private function aggregateAccounts(iterable $searchItems): array
    {
        $accounts = [];

        /** @var SearchParserInterface $searchItem */
        foreach ($searchItems as $searchItem) {
            /** @var SearchPostParserInterface $post */
            foreach ($searchItem->posts() as $post) {
                if (! isset($accounts[$post->ownerUsername()])) {
                    $accounts[$post->ownerUsername()] = [
                        'likes' => 0,
                        'comments' => 0,
                        'views' => 0,
                    ];
                }

                $accounts[$post->ownerUsername()]['likes'] += $post->likesCount();
                $accounts[$post->ownerUsername()]['comments'] += $post->commentsCount();
                $accounts[$post->ownerUsername()]['views'] += $post->viewsCount();
            }
        }

        return $accounts;
    }
}
