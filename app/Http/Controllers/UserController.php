<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;

class UserController extends Controller
{
    public function index()
    {
        return Inertia::render('Users/Index')->with([
            'users' => User::all(),
        ]);
    }

    public function edit(User $user)
    {
        return Inertia::render('Users/Edit')->with([
            'user' => $user,
        ]);
    }

    public function create()
    {
        return Inertia::render('Users/Create');
    }

    public function store()
    {
        User::create(request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed|min:6',
        ]));

        return redirect()->route('users.index');
    }

    public function update(User $user)
    {
        request()->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'password' => 'nullable|confirmed|min:6',
        ]);
        $user->fill(request()->only(['name', 'email']));
        if (request()->input('password')) {
            $user->fill(['password' => request()->input('password')]);
        }
        $user->save();

        $user->givePermissionTo([
            'books.read',
        ]);

        return redirect()->route('users.index');
    }

    public function destroy(User $user)
    {
        $user->delete();

        return redirect()->route('users.index');
    }

}
