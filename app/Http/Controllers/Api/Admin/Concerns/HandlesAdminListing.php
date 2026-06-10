<?php

namespace App\Http\Controllers\Api\Admin\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HandlesAdminListing
{
    protected function applyAdminFilters(Builder $query, Request $request, string $searchColumn = 'title'): Builder
    {
        if ($request->filled('status')) {
            $isPublished = $request->query('status') === 'published';
            $query->where('is_published', $isPublished);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $query->where($searchColumn, 'like', '%'.$request->query('search').'%');
        }

        return $query;
    }

    protected function applyActiveFilter(Builder $query, Request $request): Builder
    {
        if ($request->filled('status')) {
            $isActive = $request->query('status') === 'active';
            $query->where('is_active', $isActive);
        }

        if ($request->filled('category')) {
            $query->where('category', $request->query('category'));
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', '%'.$request->query('search').'%');
        }

        return $query;
    }

    protected function resolvePagination(Builder $query, Request $request, int $defaultPerPage = 15, int $maxPerPage = 50): mixed
    {
        if ($request->filled('page')) {
            $perPage = min(max((int) $request->query('per_page', $defaultPerPage), 1), $maxPerPage);

            return $query->paginate($perPage);
        }

        return $query->get();
    }
}
