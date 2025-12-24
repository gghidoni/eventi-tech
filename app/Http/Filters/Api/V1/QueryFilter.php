<?php

declare(strict_types=1);

namespace App\Http\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

abstract class QueryFilter
{
    protected $builder;

    protected Request $request;

    protected $sortable = [];

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    final public function apply(Builder $builder)
    {
        $this->builder = $builder;

        foreach ($this->request->all() as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $builder;
    }

    protected function filter($arr)
    {
        foreach ($arr as $key => $value) {
            if (method_exists($this, $key)) {
                $this->$key($value);
            }
        }

        return $this->builder;
    }

    protected function sort($value)
    {
        $sortAttributes = explode(',', $value);

        foreach ($sortAttributes as $sa) {
            $direction = 'asc';
            if (mb_strpos($sa, '-') === 0) {
                $direction = 'desc';
                $sa = mb_substr($sa, 1);
            }

            if (!in_array($sa, $this->sortable) && !array_key_exists($sa, $this->sortable)) {
                continue;
            }

            $columnName = $this->sortable[$sa];

            $this->builder->orderBy($columnName, $direction);

        }
    }
}
