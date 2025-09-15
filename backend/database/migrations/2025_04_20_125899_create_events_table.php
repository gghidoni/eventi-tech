<?php

declare(strict_types=1);

use App\Enums\EventStatus;
use App\Enums\EventType;
use App\Models\AddressBook\AddressBook;
use App\Models\Community;
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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Community::class);
            $table->string('title')->index();
            $table->enum('status', array_column(EventStatus::cases(), 'value'))->index();
            $table->string('description');
            $table->enum('type', array_column(EventType::cases(), 'value'))->index();
            $table->foreignIdFor(AddressBook::class);
            $table->datetime('start_date')->index();
            $table->datetime('end_date');
            $table->string('website')->nullable();
            $table->string('poster')->nullable();
            $table->string('tickets_url')->nullable();
            $table->string('cfp_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
