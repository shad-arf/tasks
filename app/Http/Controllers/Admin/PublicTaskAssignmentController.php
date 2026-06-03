<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PublicTaskAssignmentController extends Controller
{
    public function update(Request $request, Task $task): RedirectResponse
    {
        $manager = $request->user();

        abort_if($manager === null, 401);
        abort_if(
            $manager->business_id !== null && $task->business_id !== $manager->business_id,
            403,
            'You cannot assign tasks outside your business.'
        );
        abort_unless(
            str_starts_with((string) $task->description, 'Public business form submission'),
            403,
            'Only public form tasks can be assigned here.'
        );

        $data = $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'user')
                    ->where('business_id', $task->business_id)),
            ],
        ]);

        $assignee = User::query()
            ->select('id', 'business_id')
            ->whereKey((int) $data['assigned_to'])
            ->firstOrFail();

        abort_if($assignee->business_id !== $task->business_id, 422, 'The assignee must belong to the same business as the task.');

        $task->update([
            'assigned_to' => $assignee->id,
            'assigned_by' => $manager->id,
        ]);

        return to_route('admin.dashboard')->with('success', 'Public task assignee updated.');
    }
}
