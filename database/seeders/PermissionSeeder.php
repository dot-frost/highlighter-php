<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        Permission::create(['name' => 'users.*']);
        Permission::create(['name' => 'users.read']);
        Permission::create(['name' => 'users.create']);
        Permission::create(['name' => 'users.update']);
        Permission::create(['name' => 'users.delete']);

        Permission::create(['name' => 'books.*']);
        Permission::create(['name' => 'books.read']);
        Permission::create(['name' => 'books.create']);
        Permission::create(['name' => 'books.update']);
        Permission::create(['name' => 'books.delete']);

        Permission::create(['name' => 'pages.*']);
        Permission::create(['name' => 'pages.read']);
        Permission::create(['name' => 'pages.create']);
        Permission::create(['name' => 'pages.update']);
        Permission::create(['name' => 'pages.delete']);


        Role::create(['name' => 'Super-Admin']);
        User::query()->where('email', 'admin@admin.com')->first()->assignRole('Super-Admin');
    }
}
