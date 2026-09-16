<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Jobs\ProcessPrivateUpload;
use App\Models\User;
use App\Notifications\QueuedResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class QueueOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_queue_counts_use_the_configured_database_connection_and_table(): void
    {
        config(['database.connections.queue_monitor_test' => [
            'driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '',
        ], 'queue.default' => 'custom_database', 'queue.connections.custom_database' => [
            'driver' => 'database', 'connection' => 'queue_monitor_test', 'table' => 'pending_work',
        ]]);
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        try {
            $this->actingAs($admin)->get(route('admin.notifications.view'))
                ->assertOk()->assertViewHas('counts', fn ($counts) => $counts->isEmpty());
            $connection = DB::connection('queue_monitor_test');
            $connection->getSchemaBuilder()->create('pending_work', function ($table) {
                $table->string('queue');
            });
            $connection->table('pending_work')->insert([
                ['queue' => 'uploads'], ['queue' => 'uploads'], ['queue' => 'notifications'],
            ]);
            $this->get(route('admin.notifications.view'))->assertOk()
                ->assertViewHas('counts', fn ($counts) => $counts->get('uploads') === 2 && $counts->get('notifications') === 1);
        } finally {
            DB::purge('queue_monitor_test');
        }
    }

    public function test_admin_can_retry_supported_failed_job_without_exposing_payload(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN]);
        $uuid = (string) Str::uuid();
        $id = Queue::connection('database')->push((new ProcessPrivateUpload('workspace', 999, 'workspace/private-name.pdf'))->beforeCommit(), '', 'uploads');
        $payload = DB::table('jobs')->where('id', $id)->value('payload');
        DB::table('jobs')->where('id', $id)->delete();
        DB::table('failed_jobs')->insert(['uuid' => $uuid, 'connection' => 'database', 'queue' => 'uploads', 'payload' => $payload, 'exception' => 'secret-provider-message', 'failed_at' => now()]);
        $this->actingAs($admin)->get(route('admin.notifications.view'))->assertOk()->assertDontSee('secret-provider-message')->assertDontSee('private-name.pdf');
        $this->post(route('admin.queue.retry', $uuid), ['current_password' => 'wrong'])->assertSessionHasErrors('current_password');
        $this->post(route('admin.queue.retry', $uuid), ['current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('failed_jobs', ['uuid' => $uuid]);
        $this->assertDatabaseHas('jobs', ['queue' => 'uploads']);
        $this->assertDatabaseHas('audit_events', ['event' => 'queue.retried:'.$uuid]);
        $this->post(route('admin.queue.retry', $uuid), ['current_password' => 'password'])->assertNotFound();
    }

    public function test_non_admin_and_unsupported_jobs_cannot_be_retried(): void
    {
        $uuid = (string) Str::uuid();
        DB::table('failed_jobs')->insert(['uuid' => $uuid, 'connection' => 'database', 'queue' => 'notifications', 'payload' => json_encode(['displayName' => QueuedResetPassword::class]), 'exception' => 'hidden', 'failed_at' => now()]);
        $this->actingAs(User::factory()->create())->post(route('admin.queue.retry', $uuid), ['current_password' => 'password'])->assertForbidden();
        $this->actingAs(User::factory()->create(['role' => UserRole::ADMIN]))->post(route('admin.queue.retry', $uuid), ['current_password' => 'password'])->assertUnprocessable();
        $this->assertDatabaseHas('failed_jobs', ['uuid' => $uuid]);
    }
}
