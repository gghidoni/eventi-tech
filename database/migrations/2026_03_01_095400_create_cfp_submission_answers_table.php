<?php

use App\Models\CfpSubmission;
use App\Models\CfpTemplateField;
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
        Schema::create('cfp_submission_answers', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(CfpSubmission::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(CfpTemplateField::class)->constrained()->cascadeOnDelete();
            $table->json('value')->nullable();
            $table->timestamps();

            $table->unique(['cfp_submission_id', 'cfp_template_field_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfp_submission_answers');
    }
};
