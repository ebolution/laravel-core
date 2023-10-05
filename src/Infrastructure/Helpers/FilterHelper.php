<?php

namespace Ebolution\Core\Infrastructure\Helpers;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * Use this helper on an Eloquent repository class to implement
 * search functionality on you get-all end-point.
 *
 * This will try to use the data on the query parameters as search filters.
 *
 * There are two special query parameters:
 * - or: Use OR logic to join filters (default is AND)
 * - like: Use LIKE comparison to match data (default is =)
 */
trait FilterHelper
{
    /**
     * Search for data meeting the filters criteria.
     *
     * @param Model $model A subclass of Model for your data
     * @param array $allowed_filters A list of allowed filters
     * @return Collection Collection of models that meet the criteria
     */
    public function filter(Model $model, array $allowed_filters): Collection
    {
        $or_conditions = false;
        $like_operator = false;

        $query = $model;
        $filters = request()->query();

        if ( array_key_exists('or', $filters) ) {
            if (filter_var($filters['or'], FILTER_VALIDATE_BOOLEAN)) {
                $or_conditions = true;
            }
            unset($filters['or']);
        }

        if ( array_key_exists('like', $filters) ) {
            if ( filter_var($filters['like'], FILTER_VALIDATE_BOOLEAN) ) {
                $like_operator = true;
            }
            unset($filters['like']);
        }

        if (sizeof($filters) === 0) {
            return $query->all();
        }

        foreach ($filters as $field => $filter) {
            if ( in_array($field, $allowed_filters) ) {
                $operator = $like_operator ? 'like' : '=';
                $filter = $like_operator ? "%{$filter}%" : $filter;
                if  ($or_conditions) {
                    $query = $query->orWhere($field, $operator, $filter);
                } else {
                    $query = $query->where($field, $operator, $filter);
                }
            }
        }
        return $query->get();
    }
}
