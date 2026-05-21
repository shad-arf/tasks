<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('redirects guests from the home route to the login page', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
});

it('renders the login page without the old side panel text', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee('چوونەژوورەوە')
        ->assertDontSee('Tasks System');
});

it('renders the task dashboard for regular users', function () {
    $assigner = User::factory()->create([
        'name' => 'Manager',
        'email' => 'manager@example.com',
    ]);
    $assignee = User::factory()->create([
        'name' => 'Shad',
        'email' => 'shad@example.com',
    ]);
    $otherUser = User::factory()->create([
        'name' => 'Aso',
        'email' => 'aso@example.com',
    ]);

    Task::create([
        'title' => 'Review invoice batch',
        'description' => 'Confirm all totals before close.',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
    ]);

    Task::create([
        'title' => 'Plan sprint retro',
        'description' => 'Bring the blockers list.',
        'assigned_by' => $assignee->id,
        'assigned_to' => $otherUser->id,
    ]);

    $this->actingAs($assignee)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('تاسکەکانم')
        ->assertSee('Review invoice batch')
        ->assertSee('Plan sprint retro');
});

it('renders the admin dashboard for managers', function () {
    $manager = User::factory()->manager()->create([
        'name' => 'Manager',
        'username' => 'manager-user',
    ]);
    $regularUser = User::factory()->create([
        'name' => 'Shad',
        'username' => 'shad-user',
    ]);

    $this->actingAs($manager)
        ->get(route('admin.dashboard'))
        ->assertOk()
        ->assertSee('بەڕێوەبردنی بەکارهێنەران')
        ->assertSee('زیادکردنی بەکارهێنەری نوێ')
        ->assertSee('لیستی بەکارهێنەران')
        ->assertSee('All Users')
        ->assertSee($regularUser->username);
});

it('allows managers to create users without email and with short passwords', function () {
    $manager = User::factory()->manager()->create();

    $this->actingAs($manager)
        ->post(route('admin.users.store'), [
            'name' => 'Short Password User',
            'username' => 'short-pass-user',
            'email' => '',
            'phone' => '+964 750 123 4567',
            'role' => 'user',
            'password' => '123',
        ])
        ->assertRedirect(route('admin.dashboard'));

    $createdUser = User::where('username', 'short-pass-user')->firstOrFail();

    expect($createdUser->email)->toBeNull();
    expect($createdUser->phone)->toBe('9647501234567');
    expect(Hash::check('123', $createdUser->password))->toBeTrue();
});

it('allows managers to update users without email and with short passwords', function () {
    $manager = User::factory()->manager()->create();
    $managedUser = User::factory()->create([
        'email' => 'managed@example.com',
    ]);

    $this->actingAs($manager)
        ->patch(route('admin.users.update', $managedUser), [
            'user_id' => $managedUser->id,
            'name' => $managedUser->name,
            'username' => $managedUser->username,
            'email' => '',
            'phone' => '+964-771-111-2222',
            'role' => $managedUser->role,
            'password' => '123',
        ])
        ->assertRedirect(route('admin.dashboard'));

    $managedUser->refresh();

    expect($managedUser->email)->toBeNull();
    expect($managedUser->phone)->toBe('9647711112222');
    expect(Hash::check('123', $managedUser->password))->toBeTrue();
});

it('creates a task from the dashboard flow', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'title' => 'Prepare weekly report',
            'description' => 'Include blockers and next steps.',
            'priority' => 'urgent',
            'due_date' => '2026-06-01',
            'assigned_to' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Prepare weekly report',
        'priority' => 'urgent',
        'status' => 'pending',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
        'is_completed' => false,
    ]);

    expect(Task::where('title', 'Prepare weekly report')->firstOrFail()->due_date?->format('Y-m-d'))
        ->toBe('2026-06-01');
});

it('sends a whatsapp message when requested during task creation', function () {
    Cache::flush();

    Http::fake([
        'https://wa.bazrgan.com/api/auth/token' => Http::response([
            'token' => 'fake-access-token',
            'expires_in' => 86400,
        ]),
        'https://wa.bazrgan.com/api/send' => Http::response([
            'success' => true,
            'message' => 'sent',
            'log_id' => '550e8400-e29b-41d4-a716-446655440000',
        ]),
    ]);

    config()->set('services.whatsapp', [
        'base_url' => 'https://wa.bazrgan.com',
        'send_endpoint' => '/api/send',
        'token_url' => 'https://wa.bazrgan.com/api/auth/token',
        'token' => null,
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'account' => 'main',
    ]);

    $assigner = User::factory()->create([
        'name' => 'Manager',
    ]);
    $assignee = User::factory()->create([
        'name' => 'Assignee',
        'phone' => '9647501234567',
    ]);

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'title' => 'Send WhatsApp notice',
            'description' => 'Include a direct message.',
            'priority' => 'high',
            'due_date' => '2026-06-08',
            'assigned_to' => $assignee->id,
            'send_whatsapp' => '1',
            'whatsapp_message' => 'Hello from the task board!',
        ])
        ->assertRedirect(route('tasks.index'));

    Http::assertSent(fn ($request) => $request->url() === 'https://wa.bazrgan.com/api/auth/token'
        && $request['client_id'] === 'client-id'
        && $request['client_secret'] === 'client-secret');

    Http::assertSent(fn ($request) => $request->url() === 'https://wa.bazrgan.com/api/send'
        && $request->hasHeader('Authorization', 'Bearer fake-access-token')
        && $request['phone'] === '9647501234567'
        && $request['message'] === 'Hello from the task board!'
        && $request['message_type'] === 'text'
        && $request['account'] === 'main');
});

it('creates the task and shows a warning when whatsapp is requested without an assignee phone number', function () {
    Http::fake();

    $assigner = User::factory()->create();
    $assignee = User::factory()->create([
        'phone' => null,
    ]);

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'title' => 'Missing phone test',
            'description' => 'The task should still be created.',
            'priority' => 'low',
            'assigned_to' => $assignee->id,
            'send_whatsapp' => '1',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('warning');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Missing phone test',
        'assigned_to' => $assignee->id,
    ]);

    Http::assertNothingSent();
});

it('shows a longer whatsapp failure warning with the api error details', function () {
    Cache::flush();

    Http::fake([
        'https://wa.bazrgan.com/api/auth/token' => Http::response([
            'token' => 'fake-access-token',
            'expires_in' => 86400,
        ]),
        'https://wa.bazrgan.com/api/send' => Http::response([
            'message' => 'Account books is not connected.',
        ], 422),
    ]);

    config()->set('services.whatsapp', [
        'base_url' => 'https://wa.bazrgan.com',
        'send_endpoint' => '/api/send',
        'token_url' => 'https://wa.bazrgan.com/api/auth/token',
        'token' => null,
        'client_id' => 'client-id',
        'client_secret' => 'client-secret',
        'account' => 'books',
    ]);

    $assigner = User::factory()->create();
    $assignee = User::factory()->create([
        'phone' => '9647501234567',
    ]);

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'title' => 'WhatsApp failure details',
            'description' => 'Should show the api error.',
            'priority' => 'high',
            'assigned_to' => $assignee->id,
            'send_whatsapp' => '1',
        ])
        ->assertRedirect(route('tasks.index'))
        ->assertSessionHas('warning', fn (string $warning) => str_contains($warning, 'WHATSAPP_ACCOUNT')
            && str_contains($warning, 'Account books is not connected.'));
});

it('allows the assignee to update a task status through the richer workflow', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $task = Task::create([
        'title' => 'Review launch checklist',
        'description' => 'Confirm blockers are cleared.',
        'priority' => 'high',
        'due_date' => '2026-06-04',
        'status' => 'pending',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
    ]);

    $this->actingAs($assignee)
        ->patch(route('tasks.status.update', $task), [
            'status' => 'pending_review',
        ])
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'pending_review',
        'is_completed' => false,
    ]);
});

it('hides completed tasks from the active dashboard unless requested', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();

    Task::create([
        'title' => 'Prepare handoff notes',
        'description' => null,
        'priority' => 'high',
        'status' => 'pending',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
        'is_completed' => false,
    ]);

    Task::create([
        'title' => 'Finalize release checklist',
        'description' => null,
        'priority' => 'low',
        'status' => 'completed',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
        'is_completed' => true,
    ]);

    $this->actingAs($assignee)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertSee('Prepare handoff notes')
        ->assertDontSee('Finalize release checklist');

    $this->actingAs($assignee)
        ->get(route('tasks.index', ['completed' => 'show']))
        ->assertOk()
        ->assertSee('Finalize release checklist');
});

it('lets task participants add comments with attachments', function () {
    Storage::fake('public');

    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $task = Task::create([
        'title' => 'Send final assets',
        'description' => null,
        'priority' => 'low',
        'status' => 'in_progress',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
    ]);

    $this->actingAs($assigner)
        ->post(route('tasks.comments.store', $task), [
            'comment' => 'Attached the supporting file.',
            'attachment' => UploadedFile::fake()->create('notes.pdf', 50, 'application/pdf'),
        ])
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('task_comments', [
        'task_id' => $task->id,
        'user_id' => $assigner->id,
        'comment' => 'Attached the supporting file.',
        'attachment_name' => 'notes.pdf',
    ]);
});

it('allows the assignee to toggle a task status', function () {
    $manager = User::factory()->create();
    $assignee = User::factory()->create();
    $task = Task::create([
        'title' => 'Ship release notes',
        'description' => null,
        'assigned_by' => $manager->id,
        'assigned_to' => $assignee->id,
        'is_completed' => false,
    ]);

    $this->actingAs($assignee)
        ->patch(route('tasks.toggle', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_completed' => true,
    ]);
});

it('archives completed tasks and shows them in the archive view', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();
    $task = Task::create([
        'title' => 'Close sprint summary',
        'description' => null,
        'priority' => 'high',
        'status' => 'completed',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
        'is_completed' => true,
    ]);

    $this->actingAs($assignee)
        ->patch(route('tasks.archive', $task), [
            'view' => 'active',
            'completed' => 'show',
        ])
        ->assertRedirect(route('tasks.index', ['completed' => 'show']));

    expect($task->fresh()->archived_at)->not->toBeNull();

    $this->actingAs($assignee)
        ->get(route('tasks.index'))
        ->assertOk()
        ->assertDontSee('Close sprint summary');

    $this->actingAs($assignee)
        ->get(route('tasks.index', ['view' => 'archived']))
        ->assertOk()
        ->assertSee('Close sprint summary');
});

it('blocks task status changes from non assignees', function () {
    $manager = User::factory()->create();
    $assignee = User::factory()->create();
    $otherUser = User::factory()->create();
    $task = Task::create([
        'title' => 'Approve campaign copy',
        'description' => null,
        'assigned_by' => $manager->id,
        'assigned_to' => $assignee->id,
        'is_completed' => false,
    ]);

    $this->actingAs($otherUser)
        ->patch(route('tasks.toggle', $task))
        ->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_completed' => false,
    ]);
});
