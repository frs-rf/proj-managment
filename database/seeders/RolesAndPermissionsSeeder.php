<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // create permissions
        $permissions = [
            'project.create', 'project.update',
            'task.assign', 'task.view.assigned',
            'progress.view', 'progress.update',
            'report.view',
            'member.manage',
            'attachment.upload',
            'notification.view',
            // admin specific (to give admin 'all' or explicit ones)
            'admin.access' 
        ];

        foreach ($permissions as $permission) {
            \Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permission]);
        }

        // create roles and assign created permissions

        // Team Member
        $roleTeamMember = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'team_member']);
        $roleTeamMember->givePermissionTo([
            'task.view.assigned',
            'progress.update',
            'attachment.upload',
            'notification.view'
        ]);

        // Project Manager
        $roleProjectManager = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'project_manager']);
        $roleProjectManager->givePermissionTo([
            'project.create',
            'project.update',
            'task.assign',
            'progress.view',
            'report.view',
            'member.manage'
        ]);

        // Administrator
        $roleAdmin = \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'administrator']);
        $roleAdmin->givePermissionTo(\Spatie\Permission\Models\Permission::all());
    }
}
