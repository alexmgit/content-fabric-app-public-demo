<?php

namespace App\Repositories\Search;

use JasonGuru\LaravelMakeRepository\Repository\BaseRepository;
use App\Models\Search\Source as SearchSource;
use App\Models\User;
use App\Enums\Search\SourceInterestLevel;

/**
 * Class SourceRepository.
 */
class SourceRepository extends BaseRepository
{
    protected $filters = [];

    protected $sortField = 'created_at';
    protected $sortDirection = 'desc';

    /**
     * @return string
     *  Return the model
     */
    public function model()
    {
        return SearchSource::class;
    }

    public function getSources(User $user, $perPage = 100)
    {
        $query = $this->model::query()
            ->where('user_id', $user->id)
            ->with('search');

        if ($this->sortField === 'interest_level') {
            $ids = [
                SourceInterestLevel::LOW->value,
                SourceInterestLevel::MEDIUM->value,
                SourceInterestLevel::HIGH->value,
                SourceInterestLevel::VERY_HIGH->value,
                SourceInterestLevel::EXCELLENT->value,
            ];

            $query->orderByRaw('FIELD (interest_level, ' . implode(', ', array_map(fn($id) => "'{$id}'", $ids)) . ') ' . $this->sortDirection);
        } 
        else
        {
            $query->orderBy($this->sortField, $this->sortDirection);
        }

        if (isset($this->filters['search_query'])) {
            $query->whereHas('search', function ($query) {
                $query->where('query', $this->filters['search_query']);
            });
        }

        if (isset($this->filters['interest_level'])) {
            $query->where('interest_level', $this->filters['interest_level']);
        }
        
        if ($perPage) {
            return $query->paginate($perPage);
        }

        return $query->get();
    }

    public function setSearchQueryFilter($searchQuery)
    {
        $this->filters['search_query'] = $searchQuery;
    }

    public function setInterestLevelFilter($interestLevel)
    {
        $this->filters['interest_level'] = $interestLevel;  
    }

    public function setSortField($sortField)
    {
        $this->sortField = $sortField;
    }

    public function setSortDirection($sortDirection)
    {
        $this->sortDirection = $sortDirection;
    }
}