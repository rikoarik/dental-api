<?php

namespace App\Http\Controllers\Api\Public\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HandlesPublicListing
{
    protected function applyLimitOrPaginate(Builder $query, Request $request, int $defaultPerPage = 15, int $maxPerPage = 50): mixed
    {
        if ($request->filled('page')) {
            $perPage = min(max((int) $request->query('per_page', $defaultPerPage), 1), $maxPerPage);

            return $query->paginate($perPage);
        }

        if ($request->filled('limit')) {
            $limit = min(max((int) $request->query('limit'), 1), 20);

            return $query->limit($limit)->get();
        }

        return $query->get();
    }

    protected function applySearch(Builder $query, Request $request, string $column = 'title'): Builder
    {
        if ($request->filled('search')) {
            $search = $request->query('search');
            $query->where($column, 'like', '%'.$search.'%');
        }

        return $query;
    }

    protected function applyCategory(Builder $query, Request $request): Builder
    {
        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        return $query;
    }
}
