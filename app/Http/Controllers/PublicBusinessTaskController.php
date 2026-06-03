<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePublicBusinessTaskRequest;
use App\Models\Business;
use App\Models\Task;
use App\Models\TaskComment;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PublicBusinessTaskController extends Controller
{
    public function create(string $businessName): View
    {
        $business = $this->resolveBusiness($businessName);

        return view('public.bazrgan', [
            'business' => $business,
            'businessSlug' => Str::slug($business->name),
        ]);
    }

    public function store(StorePublicBusinessTaskRequest $request, string $businessName): RedirectResponse
    {
        $business = $this->resolveBusiness($businessName);
        $data = $request->validated();

        $assignee = $this->defaultAssignee($business);

        $assigner = User::query()
            ->select('id', 'business_id')
            ->where('role', 'manager')
            ->where('business_id', $business->id)
            ->first()
            ?? User::query()
                ->select('id', 'business_id')
                ->where('business_id', $business->id)
                ->whereKeyNot($assignee->id)
                ->first()
            ?? $assignee;

        $requestedAt = $data['date'].' '.$data['time'];
        $note = trim((string) ($data['note'] ?? ''));

        $descriptionLines = [
            'Public business form submission',
            'Business: '.$business->name,
            'Customer name: '.$data['customer_name'],
            'Requested date and time: '.$requestedAt,
        ];

        if ($note !== '') {
            $descriptionLines[] = 'Note: '.$note;
        }

        $task = Task::create([
            'title' => $business->name.' request - '.$data['customer_name'],
            'description' => implode("\n", $descriptionLines),
            'priority' => Task::PRIORITY_HIGH,
            'due_date' => $data['date'],
            'status' => Task::STATUS_PENDING,
            'is_completed' => false,
            'business_id' => $business->id,
            'assigned_by' => $assigner->id,
            'assigned_to' => $assignee->id,
        ]);

        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('public-business-submissions', 'public');

            TaskComment::create([
                'task_id' => $task->id,
                'user_id' => $assigner->id,
                'comment' => 'Image submitted through the public '.$business->name.' form.',
                'attachment_path' => $imagePath,
                'attachment_name' => $request->file('image')->getClientOriginalName(),
            ]);
        }

        return to_route('public.business.create', ['businessName' => Str::slug($business->name)])
            ->with('success', 'Your request was submitted successfully.');
    }

    private function resolveBusiness(string $businessName): Business
    {
        $business = Business::query()
            ->where('name', $businessName)
            ->first();

        if ($business !== null) {
            return $business;
        }

        $business = Business::query()
            ->get()
            ->first(fn (Business $business): bool => Str::slug($business->name) === Str::slug($businessName));

        abort_if($business === null, 404, 'Business page not found.');

        return $business;
    }

    private function defaultAssignee(Business $business): User
    {
        if ($business->default_public_task_assignee_id !== null) {
            $configuredAssignee = User::query()
                ->select('id', 'name', 'business_id')
                ->where('role', 'user')
                ->where('business_id', $business->id)
                ->whereKey($business->default_public_task_assignee_id)
                ->first();

            if ($configuredAssignee !== null) {
                return $configuredAssignee;
            }
        }

        $assignee = User::query()
            ->select('id', 'name', 'business_id')
            ->where('role', 'user')
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->first();

        abort_if($assignee === null, 422, 'This business does not have a task assignee yet.');

        return $assignee;
    }
}
