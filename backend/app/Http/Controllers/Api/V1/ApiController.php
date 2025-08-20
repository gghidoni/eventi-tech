<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponses;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

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

        $includeValues = explode(',', strtolower($param));

        return in_array(strtolower($relationship), $includeValues);
    }

    // public function isAble($ability, $target)
    // {
    //     dd($this->authorize($ability, $target));
    //     return $this->authorize($ability, $target);
    // }
}
