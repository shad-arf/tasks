<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Business;
use App\Models\User;
use App\Support\WhatsAppAccountSynchronizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    public function store(StoreUserRequest $request, WhatsAppAccountSynchronizer $whatsAppAccountSynchronizer): RedirectResponse
    {
        $data = $request->validated();
        $business = $this->resolveBusiness($data['business_name']);
        $whatsAppAccountSynchronizer->sync($business->name);

        User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?? null,
            'business_id' => $business->id,
            'role' => $data['role'],
            'password' => Hash::make($data['password']),
        ]);

        return to_route('admin.dashboard')->with('success', 'بەکارهێنەری نوێ زیادکرا.');
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        WhatsAppAccountSynchronizer $whatsAppAccountSynchronizer
    ): RedirectResponse
    {
        $data = $request->validated();
        $business = $this->resolveBusiness($data['business_name']);
        $whatsAppAccountSynchronizer->sync($business->name);

        abort_if(
            auth()->id() === $user->id && $data['role'] !== 'manager',
            422,
            'ناتوانیت ڕۆڵی خۆت بگۆڕیت بۆ user.'
        );

        $payload = [
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'] ?: null,
            'phone' => $data['phone'] ?? null,
            'business_id' => $business->id,
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

    private function resolveBusiness(string $name): Business
    {
        return Business::firstOrCreate([
            'name' => trim($name),
        ]);
    }
}
