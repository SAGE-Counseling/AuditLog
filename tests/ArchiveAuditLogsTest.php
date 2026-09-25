<?php

namespace SageCounseling\AuditLog\Tests;

use Illuminate\Support\Facades\DB;

class ArchiveAuditLogsTest extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $app['config']->set('audit-log.retention.primary_days', 365);
        $app['config']->set('audit-log.retention.archive_days', 2190);
    }

    private function insertAuditLogRow(array $overrides = []): void
    {
        DB::connection('testing')->table('audit_logs')->insert(array_merge([
            'user_id' => 1,
            'user_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'roles' => json_encode(['admin']),
            'action_type' => 'read',
            'resource_type' => 'client',
            'resource_id' => '4502',
            'occurred_at' => now('UTC')->subDays(400),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'succeeded' => true,
            'status_code' => 200,
            'purpose_of_use' => 'HOPERAT',
        ], $overrides));
    }

    public function test_archives_rows_past_primary_retention_and_removes_them_from_the_primary_table(): void
    {
        $this->insertAuditLogRow();

        $this->artisan('audit-log:archive')->assertSuccessful();

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
        $this->assertDatabaseHas('audit_log_archives', ['resource_id' => '4502'], 'testing');
    }

    public function test_leaves_rows_within_the_primary_retention_window_alone(): void
    {
        $this->insertAuditLogRow(['occurred_at' => now('UTC')->subDays(10)]);

        $this->artisan('audit-log:archive')->assertSuccessful();

        $this->assertDatabaseCount('audit_logs', 1, 'testing');
        $this->assertDatabaseCount('audit_log_archives', 0, 'testing');
    }

    public function test_purges_archive_rows_past_archive_retention(): void
    {
        DB::connection('testing')->table('audit_log_archives')->insert([
            'id' => 1,
            'user_id' => 1,
            'user_name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'roles' => json_encode(['admin']),
            'action_type' => 'read',
            'resource_type' => 'client',
            'resource_id' => '4502',
            'occurred_at' => now('UTC')->subDays(2200),
            'ip_address' => '127.0.0.1',
            'user_agent' => 'phpunit',
            'succeeded' => true,
            'status_code' => 200,
            'purpose_of_use' => 'HOPERAT',
        ]);

        $this->artisan('audit-log:archive')->assertSuccessful();

        $this->assertDatabaseCount('audit_log_archives', 0, 'testing');
    }
}
