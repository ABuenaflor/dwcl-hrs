<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Institution lookups: campuses, departments and the academic ranks a
 * faculty member can be promoted to. Created before `users` because
 * employee accounts reference them.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('campuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campus_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 150);
            $table->string('code', 20)->nullable();
            // basic_ed | tertiary | non_academic — decides which ranking rubric applies.
            $table->string('level', 20)->index();
            $table->timestamps();

            $table->unique(['campus_id', 'name']);
        });

        Schema::create('academic_ranks', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->index();
            $table->string('name', 100);
            $table->decimal('min_points', 7, 2)->default(0);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['level', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('academic_ranks');
        Schema::dropIfExists('departments');
        Schema::dropIfExists('campuses');
    }
};
