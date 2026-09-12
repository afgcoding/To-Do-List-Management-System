<?php

namespace Database\Seeders;

use App\Enums\UserStatus;
use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            SystemSettingSeeder::class,
            RolesAndPermissionsSeeder::class,
            WorkspaceDataSeeder::class,
        ]);

        $engineering = Department::query()->firstOrCreate(
            ['name' => 'Engineering'],
            ['code' => 'ENG', 'is_active' => true],
        );

        $assignWorkspaceRole = function (User $user): void {
            $user->syncWorkspaceRole($user->role->spatieName());
        };

        $assignWorkspaceRole(User::factory()->superAdmin()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'phone' => '+93 700 000 001',
            'job_title' => 'Workspace Administrator',
            'department_id' => $engineering->id,
            'status' => UserStatus::Active,
        ]));

        $assignWorkspaceRole(User::factory()->admin()->create([
            'name' => 'Neda Ahmadi',
            'email' => 'neda@example.com',
            'phone' => '+93 700 000 004',
            'job_title' => 'Operations Admin',
            'department_id' => $engineering->id,
        ]));

        $assignWorkspaceRole(User::factory()->manager()->create([
            'name' => 'Amina Karimi',
            'email' => 'amina@example.com',
            'phone' => '+93 700 000 002',
            'job_title' => 'Engineering Manager',
            'department_id' => $engineering->id,
        ]));

        User::factory()->count(4)->employee()->create([
            'department_id' => $engineering->id,
        ])->each($assignWorkspaceRole);
    }
}
