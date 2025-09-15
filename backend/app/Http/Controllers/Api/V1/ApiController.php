<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;

class ApiController extends Controller
{
    use ApiResponses;
    use AuthorizesRequests;

    // protected $policyClass;

    public function __construct()
    {
        // Gate::guessPolicyNamesUsing(function () {
        //     return $this->policyClass;
        // });
    }

    public function include(string $relationship): bool
    {
        $param = request()->get('include');
        if (! isset($param)) {
            return false;
        }

        $includeValues = explode(',', mb_strtolower($param));

        return in_array(mb_strtolower($relationship), $includeValues);
    }

    // public function isAble($ability, $target)
    // {
    //     dd($this->authorize($ability, $target));
    //     return $this->authorize($ability, $target);
    // }
}
