<?php

namespace App\Http\Controllers;

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function pageSetUsersPermission(Page $page, Request $request)
    {
        $this->validate($request, [
            'users' => ['required', 'array'],
            'users.*' => ['required','array'],
            'users.*.id' => ['required', 'integer', 'exists:users,id'],
            'users.*.permissions' => ['array'],
            'users.*.permissions.*' => ['required', 'string', 'in:read,update']
        ]);
        $permissions = [];
        collect(['read', 'update'])->each(function ($permission) use (&$permissions, $page) {
            $permission = "pages.{$permission}.{$page->id}";
            if (!Permission::getPermission(['name' => $permission, 'guard_name' => 'web'])){
                Permission::create(['name' => $permission, 'guard_name' => 'web']);
            }
            $permissions[] = $permission;
        });

        collect($request->users)->each(function ($userData) use ($page,$permissions) {
            $user = User::find($userData['id']) ?? new User();

            $user->revokePermissionTo($permissions);

            $permissions = [];
            collect($userData['permissions'])->each(function ($permission) use (&$permissions, $page) {
                $permissions[] = "pages.{$permission}.{$page->id}";
            });
            $user->givePermissionTo($permissions);

        });

        return response()->json(['success' => true]);
    }

    public function pageGetUsersPermission(Page $page)
    {
        $users = User::with('permissions')->get();

    }
}
