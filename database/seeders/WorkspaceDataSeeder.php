<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Tag;
use Illuminate\Database\Seeder;

class WorkspaceDataSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            ['name' => 'Software Engineering', 'code' => 'SWE'],
            ['name' => 'Human Resources', 'code' => 'HR'],
            ['name' => 'Marketing & Sales', 'code' => 'MKT'],
            ['name' => 'Finance & Accounting', 'code' => 'FIN'],
        ] as $department) {
            Department::query()->updateOrCreate(
                ['name' => $department['name']],
                ['code' => $department['code'], 'is_active' => true],
            );
        }

        foreach ([
            ['name' => 'Technical Support', 'color' => '#EF4444'],
            ['name' => 'Project Management', 'color' => '#3B82F6'],
            ['name' => 'Infrastructure & Operations', 'color' => '#F59E0B'],
            ['name' => 'Design & UI/UX', 'color' => '#8B5CF6'],
            ['name' => 'Documentation & Research', 'color' => '#10B981'],
        ] as $category) {
            Category::query()->updateOrCreate(
                ['name' => $category['name']],
                ['color' => $category['color']],
            );
        }

        foreach (['Urgent', 'Bug', 'Feature', 'In Review', 'Maintenance'] as $tag) {
            Tag::query()->firstOrCreate(['name' => $tag]);
        }
    }
}
