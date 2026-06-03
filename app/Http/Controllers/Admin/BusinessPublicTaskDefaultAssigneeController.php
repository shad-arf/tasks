<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BusinessPublicTaskDefaultAssigneeController extends Controller
{
    public function update(Request $request, Business $business): RedirectResponse
    {
        $manager = $request->user();

        abort_if($manager === null, 401);
        abort_if(
            $manager->business_id !== null && $manager->business_id !== $business->id,
            403,
            'You cannot update defaults outside your business.'
        );

        $data = $request->validate([
            'default_public_task_assignee_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'user')
                    ->where('business_id', $business->id)),
            ],
        ]);

        $business->update([
            'default_public_task_assignee_id' => (int) $data['default_public_task_assignee_id'],
        ]);

        return to_route('admin.dashboard')->with('success', 'Default public task assignee updated.');
    }
}
