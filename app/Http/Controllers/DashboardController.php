<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function bookmarks(): View
    {
        return view('dashboard.bookmarks');
    }

    public function communities(): View
    {
        return view('dashboard.communities');
    }
}
