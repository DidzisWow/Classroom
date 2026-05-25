<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        $admin = User::create([
            'name'     => 'Admin User',
            'email'    => 'admin@classnova.test',
            'password' => Hash::make('password'),
            'role'     => 'admin',
        ]);

        // Teacher
        $teacher = User::create([
            'name'     => 'Jane Smith',
            'email'    => 'teacher@classnova.test',
            'password' => Hash::make('password'),
            'role'     => 'teacher',
        ]);

        // Students
        $students = collect();
        foreach (['Alice Brown', 'Bob Johnson', 'Carol Davis', 'Dan Wilson'] as $name) {
            $students->push(User::create([
                'name'     => $name,
                'email'    => strtolower(explode(' ', $name)[0]) . '@classnova.test',
                'password' => Hash::make('password'),
                'role'     => 'student',
            ]));
        }

        // Class
        $class = Classroom::create([
            'name'       => 'Mathematics Grade 10',
            'description'=> 'Algebra, geometry and introductory calculus',
            'color'      => '#00e5ff',
            'code'       => 'MATH10',
            'teacher_id' => $teacher->id,
        ]);

        // Enrol students
        $class->students()->attach($students->pluck('id'));

        // Assignment
        Assignment::create([
            'classroom_id' => $class->id,
            'title'        => 'Chapter 3 Exercises',
            'description'  => "Complete exercises 1–20 from Chapter 3 of the textbook.\n\nShow all working.",
            'type'         => 'assignment',
            'due_date'     => now()->addDays(7),
        ]);

        // Announcement
        Assignment::create([
            'classroom_id' => $class->id,
            'title'        => 'Welcome to Mathematics Grade 10!',
            'description'  => "Welcome everyone! Please review the syllabus attached and let me know if you have any questions.",
            'type'         => 'announcement',
        ]);

        $this->command->info('✓ Seeded ClassNova with test accounts:');
        $this->command->info('  admin@classnova.test   / password  (Admin)');
        $this->command->info('  teacher@classnova.test / password  (Teacher)');
        $this->command->info('  alice@classnova.test   / password  (Student)');
    }
}
