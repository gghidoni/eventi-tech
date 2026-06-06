<?php

use App\Models\CfpSchema;
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
        Schema::create('cfp_options', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(CfpSchema::class);
            $table->string('label');
            $table->json('options');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfp_options');
    }
};
