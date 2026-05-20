<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return to_route('admin.dashboard')->with('success', 'بەکارهێنەری نوێ زیادکرا.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        abort_if(
            auth()->id() === $user->id && $data['role'] !== 'manager',
            422,
            'ناتوانیت ڕۆڵی خۆت بگۆڕیت بۆ user.'
        );

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'role' => $data['role'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return to_route('admin.dashboard')->with('success', 'زانیاری بەکارهێنەر نوێکرایەوە.');
    }

    public function destroy(User $user): RedirectResponse
    {
        abort_if(
            auth()->id() === $user->id,
            422,
            'ناتوانیت خۆت بسڕیتەوە.'
        );

        $user->delete();

        return to_route('admin.dashboard')->with('success', 'بەکارهێنەر سڕایەوە.');
    }
}
