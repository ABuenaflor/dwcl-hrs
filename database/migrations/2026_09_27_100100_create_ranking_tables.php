<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Faculty ranking: editable rubrics (per the Faculty Manual), a faculty
 * member's ranking submission per cycle, the per-item SR/DRC/CRTC scores
 * with evidence, and an audit trail of every hand-off between committees.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rubrics', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index(); // basic_ed | tertiary
            $table->string('name', 150);
            $table->text('description')->nullable();
            $table->decimal('total_points', 7, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('rubric_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rubric_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('rubric_items')->cascadeOnDelete();
            $table->string('code', 20)->nullable();
            $table->string('title');
            $table->string('guide')->nullable();                 // e.g. "2 pts / 8 hrs"
            $table->decimal('weight_percent', 5, 2)->nullable(); // basic ed "%" column
            $table->decimal('credit_points', 7, 2)->nullable();  // basic ed "criteria credit points"
            $table->decimal('credit_in_field', 7, 2)->nullable(); // tertiary "in the field"
            $table->decimal('credit_related', 7, 2)->nullable();  // tertiary "in related field"
            $table->decimal('max_points', 7, 2)->nullable();     // cap applied to this node's subtotal
            $table->boolean('is_scorable')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['rubric_id', 'parent_id', 'sort_order']);
        });

        Schema::create('faculty_rankings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_id')->constrained()->restrictOnDelete();
            $table->string('cycle', 20);
            $table->string('status', 20)->default('draft')->index();
            $table->decimal('sr_total', 8, 2)->default(0);
            $table->decimal('drc_total', 8, 2)->nullable();
            $table->decimal('final_total', 8, 2)->nullable();
            $table->foreignId('current_rank_id')->nullable()->constrained('academic_ranks')->nullOnDelete();
            $table->foreignId('recommended_rank_id')->nullable()->constrained('academic_ranks')->nullOnDelete();
            $table->string('certificate_no', 40)->nullable()->unique();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('certified_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'cycle']);
        });

        Schema::create('faculty_ranking_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_ranking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rubric_item_id')->constrained()->cascadeOnDelete();
            $table->decimal('sr_points', 7, 2)->nullable();
            $table->decimal('drc_points', 7, 2)->nullable();
            $table->decimal('final_points', 7, 2)->nullable();
            $table->string('evidence_path')->nullable();
            $table->string('evidence_name')->nullable();
            $table->timestamps();

            $table->unique(['faculty_ranking_id', 'rubric_item_id']);
        });

        Schema::create('ranking_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faculty_ranking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->text('remarks')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ranking_reviews');
        Schema::dropIfExists('faculty_ranking_scores');
        Schema::dropIfExists('faculty_rankings');
        Schema::dropIfExists('rubric_items');
        Schema::dropIfExists('rubrics');
    }
};
