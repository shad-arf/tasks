<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('redirects guests from the home route to the login page', function () {
    $this->get(route('home'))
        ->assertRedirect(route('login'));
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
        ->assertSee($regularUser->username);
});

it('creates a task from the dashboard flow', function () {
    $assigner = User::factory()->create();
    $assignee = User::factory()->create();

    $this->actingAs($assigner)
        ->post(route('tasks.store'), [
            'title' => 'Prepare weekly report',
            'description' => 'Include blockers and next steps.',
            'assigned_to' => $assignee->id,
        ])
        ->assertRedirect(route('tasks.index'));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Prepare weekly report',
        'assigned_by' => $assigner->id,
        'assigned_to' => $assignee->id,
        'is_completed' => false,
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
