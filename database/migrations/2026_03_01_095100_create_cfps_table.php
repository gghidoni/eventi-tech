<?php

use App\Models\CfpSchema;
use App\Models\Event;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cfps', function (Blueprint $table) {
            $table->id();
            // Vincolo logico 1:1: ogni evento puo avere al massimo un CFP.
            $table->foreignIdFor(Event::class)->unique();
            $table->foreignIdFor(CfpSchema::class)->nullable();
            $table->datetime('opens_at');
            $table->datetime('closes_at');
            $table->string('cfp_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfps');
    }
};
