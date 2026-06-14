<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cfp_submission_answers')) {
            return;
        }

        if (!Schema::hasColumn('cfp_submission_answers', 'cfp_field_id')) {
            return;
        }

        if (!Schema::hasColumn('cfp_submission_answers', 'cfp_template_field_id')) {
            Schema::table('cfp_submission_answers', function (Blueprint $table): void {
                $table->foreignId('cfp_template_field_id')->nullable();
            });
        }

        if (Schema::hasTable('cfp_fields') && Schema::hasColumn('cfp_fields', 'cfp_template_field_id')) {
            DB::statement(<<<'SQL'
                update cfp_submission_answers
                set cfp_template_field_id = cfp_fields.cfp_template_field_id
                from cfp_fields
                where cfp_submission_answers.cfp_field_id = cfp_fields.id
                  and cfp_submission_answers.cfp_template_field_id is null
            SQL);
        } else {
            DB::statement(<<<'SQL'
                update cfp_submission_answers
                set cfp_template_field_id = cfp_field_id
                where cfp_template_field_id is null
            SQL);
        }

        DB::table('cfp_submission_answers')
            ->whereNull('cfp_template_field_id')
            ->delete();

        DB::statement('alter table cfp_submission_answers drop constraint if exists cfp_submission_answers_cfp_submission_id_cfp_field_id_unique');
        DB::statement('alter table cfp_submission_answers drop constraint if exists cfp_submission_answers_cfp_field_id_foreign');

        Schema::table('cfp_submission_answers', function (Blueprint $table): void {
            $table->dropColumn('cfp_field_id');
        });

        DB::statement('alter table cfp_submission_answers alter column cfp_template_field_id set not null');

        if (!$this->constraintExists('cfp_submission_answers', 'cfp_submission_answers_cfp_template_field_id_foreign')) {
            Schema::table('cfp_submission_answers', function (Blueprint $table): void {
                $table->foreign('cfp_template_field_id')
                    ->references('id')
                    ->on('cfp_template_fields')
                    ->cascadeOnDelete();
            });
        }

        if (!$this->constraintExists('cfp_submission_answers', 'cfp_submission_answers_cfp_submission_id_cfp_template_field_id_unique')) {
            Schema::table('cfp_submission_answers', function (Blueprint $table): void {
                $table->unique(['cfp_submission_id', 'cfp_template_field_id']);
            });
        }

        Schema::dropIfExists('cfp_fields');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('cfp_submission_answers')) {
            return;
        }

        if (Schema::hasColumn('cfp_submission_answers', 'cfp_template_field_id')) {
            DB::statement('alter table cfp_submission_answers drop constraint if exists cfp_submission_answers_cfp_submission_id_cfp_template_field_id_unique');
            DB::statement('alter table cfp_submission_answers drop constraint if exists cfp_submission_answers_cfp_template_field_id_foreign');

            if (!Schema::hasColumn('cfp_submission_answers', 'cfp_field_id')) {
                Schema::table('cfp_submission_answers', function (Blueprint $table): void {
                    $table->foreignId('cfp_field_id')->nullable();
                });
            }

            DB::statement(<<<'SQL'
                update cfp_submission_answers
                set cfp_field_id = cfp_template_field_id
                where cfp_field_id is null
            SQL);

            Schema::table('cfp_submission_answers', function (Blueprint $table): void {
                $table->dropColumn('cfp_template_field_id');
            });
        }
    }

    private function constraintExists(string $table, string $constraint): bool
    {
        return DB::table('pg_constraint')
            ->where('conrelid', DB::raw("'{$table}'::regclass"))
            ->where('conname', $constraint)
            ->exists();
    }
};
