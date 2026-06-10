<?php

namespace Database\Seeders;

use App\Models\Assignment;
use App\Models\Classroom;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

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

        // Teacher 1
        $teacher1 = User::create([
            'name'     => 'Jane Smith',
            'email'    => 'teacher@classnova.test',
            'password' => Hash::make('password'),
            'role'     => 'teacher',
        ]);

        // Teacher 2
        $teacher2 = User::create([
            'name'     => 'John Doe',
            'email'    => 'john.doe@classnova.test',
            'password' => Hash::make('password'),
            'role'     => 'teacher',
        ]);

        // Students
        $students = collect();
        foreach (['Alice Brown', 'Bob Johnson', 'Carol Davis', 'Dan Wilson', 'Emma White', 'Frank Green'] as $name) {
            $students->push(User::create([
                'name'     => $name,
                'email'    => strtolower(explode(' ', $name)[0]) . '@classnova.test',
                'password' => Hash::make('password'),
                'role'     => 'student',
            ]));
        }

        // Class 1 - Mathematics (assigned to Jane Smith)
        $class1 = Classroom::create([
            'name'       => 'Mathematics Grade 10',
            'description'=> 'Algebra, geometry and introductory calculus',
            'color'      => '#00e5ff',
            'teacher_id' => $teacher1->id,
            'assigned_teacher_id' => $teacher1->id, // Svarīgi!
        ]);

        // Class 2 - Physics (assigned to John Doe)
        $class2 = Classroom::create([
            'name'       => 'Physics Grade 11',
            'description'=> 'Mechanics, thermodynamics and electromagnetism',
            'color'      => '#a78bfa',
            'teacher_id' => $teacher2->id,
            'assigned_teacher_id' => $teacher2->id, // Svarīgi!
        ]);

        // Class 3 - Chemistry (created by admin, assigned to Jane Smith)
        $class3 = Classroom::create([
            'name'       => 'Chemistry Grade 10',
            'description'=> 'Introduction to chemistry, periodic table, and chemical reactions',
            'color'      => '#34d399',
            'teacher_id' => $admin->id,
            'assigned_teacher_id' => $teacher1->id, // Piešķirta Jane Smith
        ]);

        // Enrol students to classes
        $class1->students()->attach($students->pluck('id'));
        $class2->students()->attach($students->take(4)->pluck('id'));
        $class3->students()->attach($students->skip(2)->take(3)->pluck('id'));

        // Assignments for Math class
        Assignment::create([
            'classroom_id' => $class1->id,
            'title'        => 'Chapter 3 Exercises',
            'description'  => "Complete exercises 1–20 from Chapter 3 of the textbook.\n\nShow all working.",
            'type'         => 'assignment',
            'due_date'     => now()->addDays(7),
        ]);

        Assignment::create([
            'classroom_id' => $class1->id,
            'title'        => 'Welcome to Mathematics Grade 10!',
            'description'  => "Welcome everyone! Please review the syllabus attached and let me know if you have any questions.",
            'type'         => 'announcement',
        ]);

        // Assignments for Physics class
        Assignment::create([
            'classroom_id' => $class2->id,
            'title'        => 'Newton\'s Laws Lab Report',
            'description'  => "Write a lab report about Newton's Second Law experiment.",
            'type'         => 'assignment',
            'due_date'     => now()->addDays(10),
        ]);

        $this->command->info('✓ Seeded ClassNova with test accounts:');
        $this->command->info('  admin@classnova.test     / password  (Admin)');
        $this->command->info('  teacher@classnova.test   / password  (Teacher - Jane Smith)');
        $this->command->info('  john.doe@classnova.test  / password  (Teacher - John Doe)');
        $this->command->info('  alice@classnova.test     / password  (Student)');
        $this->command->info('  bob@classnova.test       / password  (Student)');
        $this->command->info('');
        $this->command->info('Classes created:');
        $this->command->info('  Mathematics Grade 10 (Teacher: Jane Smith)');
        $this->command->info('  Physics Grade 11 (Teacher: John Doe)');
        $this->command->info('  Chemistry Grade 10 (Teacher: Jane Smith)');
    }
}