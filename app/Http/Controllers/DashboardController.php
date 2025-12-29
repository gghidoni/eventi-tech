<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function bookmarks(): View
    {
        return view('dashboard.bookmarks');
    }
}
