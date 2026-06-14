<?php

use App\Enums\CfpFieldType;
use App\Models\CfpTemplate;
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
        Schema::create('cfp_template_fields', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(CfpTemplate::class)->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->string('label');
            $table->enum('type', array_column(CfpFieldType::cases(), 'value'));
            $table->boolean('required')->default(false);
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();
            $table->json('options')->nullable();
            $table->json('validation')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['cfp_template_id', 'key']);
            $table->index(['cfp_template_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfp_template_fields');
    }
};
