<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Applicant filtering: job postings, the SAW criteria attached to each
 * posting, applicant profiles and the applications being ranked.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('title', 150);
            $table->foreignId('campus_id')->constrained()->restrictOnDelete();
            $table->foreignId('department_id')->constrained()->restrictOnDelete();
            $table->string('category', 20);          // academic | non_academic
            $table->string('employment_type', 20);   // full_time | part_time
            $table->string('schedule', 60)->nullable();
            $table->unsignedSmallInteger('slots')->default(1);
            $table->text('description')->nullable();
            $table->json('qualifications');
            $table->string('status', 20)->default('open')->index(); // draft | open | closed
            $table->date('closes_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // One row per criterion per posting, so HR can tune weights per vacancy.
        Schema::create('saw_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('key', 40);
            $table->string('name', 100);
            $table->string('description')->nullable();
            $table->decimal('weight', 5, 4);
            $table->string('type', 10)->default('benefit');   // benefit | cost
            $table->string('source', 10)->default('manual');  // auto | manual
            $table->decimal('scale_max', 6, 2)->nullable();   // rating scale for manual criteria
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['job_posting_id', 'key']);
        });

        Schema::create('applicant_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('sex', 10)->nullable();
            $table->string('civil_status', 20)->nullable();
            $table->string('citizenship', 60)->nullable();
            $table->string('religion', 60)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('weight_kg', 5, 1)->nullable();
            $table->string('blood_type', 5)->nullable();
            // Government IDs are encrypted at rest (see ApplicantProfile casts).
            $table->text('pagibig_no')->nullable();
            $table->text('philhealth_no')->nullable();
            $table->text('tin_no')->nullable();
            $table->text('sss_no')->nullable();
            $table->string('contact_number', 30)->nullable();
            $table->string('residential_address')->nullable();
            $table->string('permanent_address')->nullable();
            $table->string('father_name', 150)->nullable();
            $table->string('mother_maiden_name', 150)->nullable();
            $table->timestamps();
        });

        Schema::create('educations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('level', 20); // elementary | secondary | college | masters | doctorate
            $table->string('school', 150);
            $table->string('course', 150)->nullable();
            $table->string('inclusive_dates', 40)->nullable();
            $table->unsignedSmallInteger('year_graduated')->nullable();
            $table->string('honors', 150)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'level']);
        });

        Schema::create('applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('job_posting_id')->constrained()->cascadeOnDelete();
            $table->string('status', 20)->default('submitted')->index();
            $table->decimal('years_experience', 4, 1)->default(0);
            $table->text('work_experience')->nullable();
            $table->text('trainings')->nullable();
            $table->unsignedSmallInteger('trainings_count')->default(0);
            $table->text('skills')->nullable();
            $table->string('license', 120)->nullable();
            $table->text('cover_letter')->nullable();
            // Cached SAW result, recomputed whenever the posting's matrix changes.
            $table->decimal('saw_score', 8, 6)->nullable();
            $table->unsignedInteger('saw_rank')->nullable();
            $table->text('hr_notes')->nullable();
            $table->dateTime('interview_at')->nullable();
            $table->timestamp('decided_at')->nullable();
            $table->timestamps();

            // One application per applicant per vacancy (fixes legacy double inserts).
            $table->unique(['user_id', 'job_posting_id']);
            $table->index(['job_posting_id', 'saw_rank']);
        });

        Schema::create('application_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->string('type', 20); // resume | certificate | transcript | license | other
            $table->string('original_name');
            $table->string('path');
            $table->unsignedInteger('size');
            $table->timestamps();
        });

        Schema::create('application_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('application_id')->constrained()->cascadeOnDelete();
            $table->foreignId('saw_criterion_id')->constrained('saw_criteria')->cascadeOnDelete();
            $table->decimal('value', 8, 2);
            $table->foreignId('rated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['application_id', 'saw_criterion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('application_scores');
        Schema::dropIfExists('application_documents');
        Schema::dropIfExists('applications');
        Schema::dropIfExists('educations');
        Schema::dropIfExists('applicant_profiles');
        Schema::dropIfExists('saw_criteria');
        Schema::dropIfExists('job_postings');
    }
};
