<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Allinea eventuali indici storici errati prima di aggiungere il vincolo corretto.
        DB::statement('DROP INDEX IF EXISTS event_user_user_id_unique');
        DB::statement('DROP INDEX IF EXISTS event_user_event_id_unique');

        // Rimuove eventuali duplicati storici mantenendo la riga più vecchia per coppia.
        $duplicates = DB::table('event_user')
            ->select('user_id', 'event_id', DB::raw('MIN(id) as keep_id'), DB::raw('COUNT(*) as total'))
            ->groupBy('user_id', 'event_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $duplicate) {
            DB::table('event_user')
                ->where('user_id', $duplicate->user_id)
                ->where('event_id', $duplicate->event_id)
                ->where('id', '!=', $duplicate->keep_id)
                ->delete();
        }

        DB::statement('CREATE UNIQUE INDEX IF NOT EXISTS event_user_user_id_event_id_unique ON event_user (user_id, event_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS event_user_user_id_event_id_unique');
    }
};
