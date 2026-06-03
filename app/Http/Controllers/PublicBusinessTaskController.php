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
use Illuminate\Validation\Rule;

class PublicBusinessTaskController extends Controller
{
    public function create(string $businessName): View
    {
        $business = $this->resolveBusiness($businessName);

        return view('public.bazrgan', [
            'business' => $business,
            'businessSlug' => Str::slug($business->name),
            'users' => $this->assignableUsers($business),
        ]);
    }

    public function store(StorePublicBusinessTaskRequest $request, string $businessName): RedirectResponse
    {
        $business = $this->resolveBusiness($businessName);
        $data = $request->validated();

        validator($data, [
            'assigned_to' => [
                'required',
                'integer',
                Rule::exists('users', 'id')->where(fn ($query) => $query
                    ->where('role', 'user')
                    ->where('business_id', $business->id)),
            ],
        ])->validate();

        $assignee = User::query()
            ->select('id', 'name', 'business_id')
            ->where('role', 'user')
            ->where('business_id', $business->id)
            ->findOrFail((int) $data['assigned_to']);

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

        $imagePath = $request->file('image')->store('public-business-submissions', 'public');

        TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $assigner->id,
            'comment' => 'Image submitted through the public '.$business->name.' form.',
            'attachment_path' => $imagePath,
            'attachment_name' => $request->file('image')->getClientOriginalName(),
        ]);

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

    private function assignableUsers(Business $business)
    {
        return User::query()
            ->select('id', 'name', 'username', 'business_id')
            ->where('role', 'user')
            ->where('business_id', $business->id)
            ->orderBy('name')
            ->get();
    }
}
