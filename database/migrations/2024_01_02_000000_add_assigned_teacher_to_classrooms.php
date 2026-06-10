<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pievieno assigned_teacher_id tikai ja kolonna neeksistē
        if (Schema::hasTable('classrooms') && !Schema::hasColumn('classrooms', 'assigned_teacher_id')) {
            Schema::table('classrooms', function (Blueprint $table) {
                $table->foreignId('assigned_teacher_id')->nullable()->constrained('users')->nullOnDelete()->after('teacher_id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('classrooms') && Schema::hasColumn('classrooms', 'assigned_teacher_id')) {
            Schema::table('classrooms', function (Blueprint $table) {
                $table->dropForeign(['assigned_teacher_id']);
                $table->dropColumn('assigned_teacher_id');
            });
        }
    }
};