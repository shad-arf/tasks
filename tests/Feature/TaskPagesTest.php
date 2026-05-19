<?php

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('renders the task dashboard on the home route', function () {
    $manager = User::factory()->create([
        'name' => 'Manager',
        'email' => 'manager@example.com',
    ]);
    $assignee = User::factory()->create([
        'name' => 'Shad',
        'email' => 'shad@example.com',
    ]);

    Task::create([
        'title' => 'Review invoice batch',
        'description' => 'Confirm all totals before close.',
        'assigned_by' => $manager->id,
        'assigned_to' => $assignee->id,
    ]);

    $this->get(route('home', ['user' => $assignee->id]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tasks/Index')
            ->where('currentUser.id', $assignee->id)
            ->has('users', 2)
            ->has('assignedToMe', 1)
            ->has('assignedByMe', 0)
        );
});

it('creates a task from the dashboard flow', function () {
    $manager = User::factory()->create();
    $assignee = User::factory()->create();

    $this->post(route('tasks.store'), [
        'acting_as' => $manager->id,
        'title' => 'Prepare weekly report',
        'description' => 'Include blockers and next steps.',
        'assigned_to' => $assignee->id,
    ])->assertRedirect(route('home', ['user' => $manager->id]));

    $this->assertDatabaseHas('tasks', [
        'title' => 'Prepare weekly report',
        'assigned_by' => $manager->id,
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

    $this->patch(route('tasks.toggle', $task), [
        'acting_as' => $assignee->id,
    ])->assertRedirect(route('home', ['user' => $assignee->id]));

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

    $this->patch(route('tasks.toggle', $task), [
        'acting_as' => $otherUser->id,
    ])->assertForbidden();

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'is_completed' => false,
    ]);
});
