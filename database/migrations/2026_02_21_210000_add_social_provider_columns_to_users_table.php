<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Esegue la migrazione aggiungendo gli identificativi social.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('github_id')->nullable()->unique()->after('email');
            $table->string('google_id')->nullable()->unique()->after('github_id');
        });
    }

    /**
     * Ripristina lo schema precedente rimuovendo le colonne social.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropUnique('users_github_id_unique');
            $table->dropUnique('users_google_id_unique');
            $table->dropColumn(['github_id', 'google_id']);
        });
    }
};
