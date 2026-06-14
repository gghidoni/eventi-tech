<?php

use App\Enums\CfpSubmissionStatus;
use App\Models\Cfp;
use App\Models\User;
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
        Schema::create('cfp_submissions', function (Blueprint $table): void {
            $table->id();
            $table->foreignIdFor(Cfp::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('abstract');
            $table->enum('status', array_column(CfpSubmissionStatus::cases(), 'value'))->default(CfpSubmissionStatus::Draft->value)->index();
            $table->timestamp('submitted_at')->nullable()->index();
            $table->timestamps();

            $table->index(['cfp_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cfp_submissions');
    }
};
