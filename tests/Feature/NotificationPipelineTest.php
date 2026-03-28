<?php

namespace Tests\Feature;

use App\Jobs\SendFcmNotificationJob;
use App\Models\AppNotification;
use App\Models\FollowUp;
use App\Models\Lead;
use App\Models\LeadAssignment;
use App\Models\Role;
use App\Models\TelecallerTask;
use App\Models\User;
use App\Services\NotificationService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class NotificationPipelineTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('database.default', 'sqlite');
        Config::set('broadcasting.default', 'log');
        Config::set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]);

        DB::purge('sqlite');
        DB::reconnect('sqlite');
        DB::setDefaultConnection('sqlite');

        $this->createSchema();
        Carbon::setTestNow(Carbon::parse('2026-03-28 10:00:00'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_lead_assignment_creates_per_lead_notifications_and_queues_fcm(): void
    {
        Queue::fake();

        $salesExecutiveRole = $this->createRole(Role::SALES_EXECUTIVE);
        $assignee = $this->createUser($salesExecutiveRole);
        $firstLead = $this->createLead('Lead One');
        $secondLead = $this->createLead('Lead Two');

        $service = app(NotificationService::class);
        $service->notifyNewLead($assignee, $firstLead, 'https://example.test/leads/1');
        $service->notifyNewLead($assignee, $secondLead, 'https://example.test/leads/2');

        $this->assertEquals(2, AppNotification::where('user_id', $assignee->id)->where('type', AppNotification::TYPE_NEW_LEAD)->count());
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $assignee->id,
            'title' => 'New Lead Assigned',
            'message' => 'New lead assigned: Lead One',
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $assignee->id,
            'title' => 'New Lead Assigned',
            'message' => 'New lead assigned: Lead Two',
        ]);

        Queue::assertPushed(SendFcmNotificationJob::class, 2);
    }

    public function test_followup_reminder_command_notifies_creator_and_current_owner_once(): void
    {
        Queue::fake();

        $managerRole = $this->createRole(Role::SALES_MANAGER);
        $salesExecutiveRole = $this->createRole(Role::SALES_EXECUTIVE);

        $manager = $this->createUser($managerRole, ['name' => 'Manager']);
        $creator = $this->createUser($salesExecutiveRole, ['name' => 'Creator', 'manager_id' => $manager->id]);
        $owner = $this->createUser($salesExecutiveRole, ['name' => 'Owner', 'manager_id' => $manager->id]);

        $lead = $this->createLead('Reminder Lead');
        $this->assignLead($lead, $owner);

        FollowUp::create([
            'lead_id' => $lead->id,
            'created_by' => $creator->id,
            'type' => 'call',
            'notes' => 'Follow up',
            'scheduled_at' => now()->addMinutes(5)->copy()->startOfMinute()->addSeconds(10),
            'status' => 'scheduled',
        ]);

        Artisan::call('notifications:followup-reminders');

        $this->assertEquals(2, AppNotification::where('type', AppNotification::TYPE_FOLLOWUP_REMINDER)->count());
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $creator->id,
            'type' => AppNotification::TYPE_FOLLOWUP_REMINDER,
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $owner->id,
            'type' => AppNotification::TYPE_FOLLOWUP_REMINDER,
        ]);

        $this->assertNotNull(FollowUp::first()->reminder_sent_at);
        Queue::assertPushed(SendFcmNotificationJob::class, 2);
    }

    public function test_followup_reschedule_resets_tracking_markers(): void
    {
        $role = $this->createRole(Role::SALES_EXECUTIVE);
        $user = $this->createUser($role);
        $lead = $this->createLead();

        $followup = FollowUp::create([
            'lead_id' => $lead->id,
            'created_by' => $user->id,
            'type' => 'call',
            'notes' => 'Scheduled',
            'scheduled_at' => now()->addMinutes(5),
            'status' => 'scheduled',
            'reminder_sent_at' => now(),
            'overdue_notified_at' => now(),
        ]);

        $followup->update([
            'scheduled_at' => now()->addHour(),
        ]);

        $followup->refresh();

        $this->assertNull($followup->reminder_sent_at);
        $this->assertNull($followup->overdue_notified_at);
    }

    public function test_overdue_task_notifications_repeat_after_thirty_minutes_and_stop_when_completed(): void
    {
        Queue::fake();

        $managerRole = $this->createRole(Role::SALES_MANAGER);
        $salesExecutiveRole = $this->createRole(Role::SALES_EXECUTIVE);

        $manager = $this->createUser($managerRole, ['name' => 'Task Manager']);
        $assignee = $this->createUser($salesExecutiveRole, ['name' => 'Task Owner', 'manager_id' => $manager->id]);
        $lead = $this->createLead('Overdue Lead');

        $task = TelecallerTask::create([
            'lead_id' => $lead->id,
            'assigned_to' => $assignee->id,
            'task_type' => 'calling',
            'status' => 'pending',
            'scheduled_at' => now()->subMinutes(20),
            'created_by' => $manager->id,
        ]);
        $task->update(['overdue_notified_at' => now()->subMinutes(20)]);

        Artisan::call('notifications:overdue-tasks');
        $this->assertEquals(0, AppNotification::where('type', AppNotification::TYPE_TASK_OVERDUE)->count());

        $task->update(['overdue_notified_at' => now()->subMinutes(31)]);
        Artisan::call('notifications:overdue-tasks');

        $this->assertEquals(2, AppNotification::where('type', AppNotification::TYPE_TASK_OVERDUE)->count());
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $assignee->id,
            'type' => AppNotification::TYPE_TASK_OVERDUE,
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $manager->id,
            'type' => AppNotification::TYPE_TASK_OVERDUE,
        ]);

        $task->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        Artisan::call('notifications:overdue-tasks');
        $this->assertEquals(2, AppNotification::where('type', AppNotification::TYPE_TASK_OVERDUE)->count());
    }

    public function test_overdue_followup_notifications_go_to_responsible_user_and_manager(): void
    {
        Queue::fake();

        $managerRole = $this->createRole(Role::SALES_MANAGER);
        $salesExecutiveRole = $this->createRole(Role::SALES_EXECUTIVE);

        $manager = $this->createUser($managerRole, ['name' => 'Followup Manager']);
        $creator = $this->createUser($salesExecutiveRole, ['name' => 'Creator', 'manager_id' => $manager->id]);
        $owner = $this->createUser($salesExecutiveRole, ['name' => 'Responsible', 'manager_id' => $manager->id]);
        $lead = $this->createLead('Followup Overdue');
        $this->assignLead($lead, $owner);

        FollowUp::create([
            'lead_id' => $lead->id,
            'created_by' => $creator->id,
            'type' => 'call',
            'notes' => 'Pending follow-up',
            'scheduled_at' => now()->subMinutes(10),
            'status' => 'scheduled',
            'overdue_notified_at' => now()->subMinutes(31),
        ]);

        Artisan::call('notifications:overdue-followups');

        $this->assertEquals(2, AppNotification::where('type', AppNotification::TYPE_FOLLOWUP_OVERDUE)->count());
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $owner->id,
            'type' => AppNotification::TYPE_FOLLOWUP_OVERDUE,
        ]);
        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $manager->id,
            'type' => AppNotification::TYPE_FOLLOWUP_OVERDUE,
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->unsignedBigInteger('role_id')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('status')->default('new');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('next_followup_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('lead_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('assigned_to');
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->dateTime('assigned_at')->nullable();
            $table->dateTime('unassigned_at')->nullable();
            $table->timestamps();
        });

        Schema::create('follow_ups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('created_by');
            $table->string('type');
            $table->text('notes');
            $table->dateTime('scheduled_at');
            $table->dateTime('reminder_sent_at')->nullable();
            $table->dateTime('overdue_notified_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->string('status')->default('scheduled');
            $table->text('outcome')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('telecaller_tasks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('lead_id');
            $table->unsignedBigInteger('meeting_id')->nullable();
            $table->unsignedBigInteger('assigned_to');
            $table->string('task_type');
            $table->string('status')->default('pending');
            $table->dateTime('scheduled_at');
            $table->dateTime('completed_at')->nullable();
            $table->string('outcome')->nullable();
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->dateTime('notification_sent_at')->nullable();
            $table->dateTime('overdue_notified_at')->nullable();
            $table->dateTime('moved_to_pending_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('telecaller_task_id')->nullable();
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('action_type')->nullable();
            $table->text('action_url')->nullable();
            $table->dateTime('read_at')->nullable();
            $table->dateTime('clicked_at')->nullable();
            $table->timestamps();
        });
    }

    private function createRole(string $slug): Role
    {
        return Role::create([
            'name' => ucfirst(str_replace('_', ' ', $slug)),
            'slug' => $slug,
        ]);
    }

    private function createUser(Role $role, array $attributes = []): User
    {
        static $counter = 1;

        return User::create(array_merge([
            'name' => 'User ' . $counter,
            'email' => 'user' . $counter++ . '@example.test',
            'password' => bcrypt('secret'),
            'role_id' => $role->id,
            'is_active' => true,
        ], $attributes));
    }

    private function createLead(string $name = 'Lead'): Lead
    {
        return Lead::create([
            'name' => $name,
            'phone' => '9999999999',
            'status' => 'new',
        ]);
    }

    private function assignLead(Lead $lead, User $owner): LeadAssignment
    {
        return LeadAssignment::create([
            'lead_id' => $lead->id,
            'assigned_to' => $owner->id,
            'assigned_by' => $owner->manager_id,
            'is_active' => true,
            'assigned_at' => now(),
        ]);
    }
}
