<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {

        $file = Storage::get('comuni.json');
        $items = json_decode($file, true);

        dd($items);

        dd(User::find(1)->avatar);

        $u =         User::whereHas('roles', function($ru) {
            $ru->whereSlug('organizer');
        })->get();

        dd($u);

        dd(User::find(1)->hasRole('user'));
    }
}
