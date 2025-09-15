<?php

declare(strict_types=1);

namespace App\Http\Filters\V1;

use App\Models\Event;

class EventFilter extends QueryFilter
{
    protected $sortable = [
        'title' => 'title',
        'status' => 'status',
        'createdAt' => 'created_at',
    ];

    public function include($value)
    {
        return $this->builder->with(explode(',', $value));
    }

    public function status($value)
    {
        return $this->builder->whereIn('status', explode(',', $value));
    }

    public function title($value)
    {
        $likeStr = '%'.str_replace('*', '%', $value).'%';

        return $this->builder->where('title', 'like', $likeStr);
    }

    public function createdAt($value)
    {
        $dates = explode(',', $value);

        if (count($dates) > 1) {
            return $this->builder->whereBetween('created_at', $dates);
        }

        return $this->builder->whereDate('created_at', $value);
    }

    public function search($value)
    {
        $results = Event::search($value)->get();

        $ids = $results->pluck('id')->toArray();

        if (count($ids) === 0) {
            // Nessun risultato: forza query a non tornare nulla
            return $this->builder->whereRaw('0 = 1');
        }

        return $this->builder->whereIn('id', $ids);
    }

    public function location($value)
    {
        $field = explode(',', $value)[0];
        $id = explode(',', $value)[1];

        if (! in_array($field, ['province_id', 'city_id', 'region_id'])) {
            return $this->builder;
        }

        return $this->builder->whereHas('address_book', function ($q) use ($field, $id): void {
            $q->where($field, $id);
        });
    }
}
