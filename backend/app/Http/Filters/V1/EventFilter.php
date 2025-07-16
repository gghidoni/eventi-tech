<?php

namespace App\Http\Filters\V1;

use App\Models\Event;
use Illuminate\Support\Facades\Log;

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
        $likeStr = '%' . str_replace('*', '%', $value) . '%';
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

        Log::debug($results);

        if (count($ids) === 0) {
            // Nessun risultato: forza query a non tornare nulla
            return $this->builder->whereRaw('0 = 1');
        }

        return $this->builder->whereIn('id', $ids);
    }
}
