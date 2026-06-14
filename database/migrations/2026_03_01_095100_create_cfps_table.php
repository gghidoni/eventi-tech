<?php

use App\Enums\CfpMode;
use App\Enums\CfpStatus;
use App\Models\CfpTemplate;
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
        Schema::create('cfps', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Event::class)->constrained()->cascadeOnDelete()->unique();
            $table->foreignIdFor(CfpTemplate::class)->nullable()->constrained()->nullOnDelete();
            $table->enum('mode', array_column(CfpMode::cases(), 'value'))->default(CfpMode::External->value);
            $table->enum('status', array_column(CfpStatus::cases(), 'value'))->default(CfpStatus::Draft->value)->index();
            $table->string('title');
            $table->text('description')->nullable();
            $table->datetime('opens_at');
            $table->datetime('closes_at');
            $table->string('external_url')->nullable();
            $table->timestamps();

            $table->index(['mode', 'status']);
            $table->index(['opens_at', 'closes_at']);
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
