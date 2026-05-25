<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'role')) {
                $table->enum('role', ['admin', 'teacher', 'student'])
                      ->default('student')
                      ->after('password');
            }
            if (!Schema::hasColumn('users', 'avatar')) {
                $table->string('avatar')->nullable()->after('role');
            }
        });

        if (!Schema::hasTable('classrooms')) {
            Schema::create('classrooms', function (Blueprint $table) {
                $table->id();
                $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('color', 20)->default('#00e5ff');
                $table->string('code', 6)->unique();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('classroom_user')) {
            Schema::create('classroom_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['classroom_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('assignments')) {
            Schema::create('assignments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('type', ['assignment', 'announcement'])->default('assignment');
                $table->timestamp('due_date')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('assignment_files')) {
            Schema::create('assignment_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name');
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('submissions')) {
            Schema::create('submissions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->decimal('grade', 4, 2)->nullable();
                $table->text('feedback')->nullable();
                $table->timestamps();
                $table->unique(['assignment_id', 'user_id']);
            });
        }

        if (!Schema::hasTable('submission_files')) {
            Schema::create('submission_files', function (Blueprint $table) {
                $table->id();
                $table->foreignId('submission_id')->constrained()->cascadeOnDelete();
                $table->string('path');
                $table->string('original_name');
                $table->unsignedBigInteger('size')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('comments')) {
            Schema::create('comments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('assignment_id')->constrained()->cascadeOnDelete();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->text('body');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('action_logs')) {
            Schema::create('action_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->text('action');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('action_logs');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('submission_files');
        Schema::dropIfExists('submissions');
        Schema::dropIfExists('assignment_files');
        Schema::dropIfExists('assignments');
        Schema::dropIfExists('classroom_user');
        Schema::dropIfExists('classrooms');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar']);
        });
    }
};