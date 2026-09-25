<?php

namespace SageCounseling\AuditLog\Tests;

use SageCounseling\AuditLog\ActionType;
use SageCounseling\AuditLog\Actor;
use SageCounseling\AuditLog\AuditLogger;
use SageCounseling\AuditLog\InvalidResourceTypeException;
use SageCounseling\AuditLog\Models\AuditLogEntry;
use SageCounseling\AuditLog\NetworkContext;
use SageCounseling\AuditLog\PurposeOfUse;
use SageCounseling\AuditLog\PurposeOfUseResolver;
use SageCounseling\AuditLog\ResourceTypeAllowList;

class AuditLoggerTest extends TestCase
{
    private function makeLogger(): AuditLogger
    {
        return new AuditLogger(
            new ResourceTypeAllowList(['client']),
            new PurposeOfUseResolver(['admin' => PurposeOfUse::SystemAdministration], PurposeOfUse::HealthcareOperations),
        );
    }

    public function test_log_writes_a_single_row(): void
    {
        $entry = $this->makeLogger()->log(
            ActionType::Read,
            'client',
            '4502',
            new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']),
            new NetworkContext('127.0.0.1', 'phpunit'),
            true,
            200,
        );

        $this->assertInstanceOf(AuditLogEntry::class, $entry);
        $this->assertDatabaseHas('audit_logs', [
            'id' => $entry->id,
            'resource_type' => 'client',
            'resource_id' => '4502',
            'succeeded' => true,
            'status_code' => 200,
            'purpose_of_use' => PurposeOfUse::SystemAdministration->value,
        ], 'testing');
    }

    public function test_log_rejects_a_resource_type_outside_the_allow_list(): void
    {
        $this->expectException(InvalidResourceTypeException::class);

        $this->makeLogger()->log(
            ActionType::Read,
            'visit',
            '1',
            new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']),
            new NetworkContext('127.0.0.1', 'phpunit'),
            true,
            200,
        );
    }

    public function test_log_accepts_a_null_actor_id_for_a_failed_login(): void
    {
        $entry = $this->makeLogger()->log(
            ActionType::Read,
            'client',
            'n/a',
            new Actor(null, null, 'attempted@example.com', []),
            new NetworkContext('127.0.0.1', 'phpunit'),
            false,
            401,
        );

        $this->assertNull($entry->user_id);
        $this->assertNull($entry->user_name);
        $this->assertSame('attempted@example.com', $entry->email);
    }

    public function test_log_many_batches_one_row_per_resource_id(): void
    {
        $this->makeLogger()->logMany(
            ActionType::Read,
            'client',
            ['4502', '4503', '4504'],
            new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']),
            new NetworkContext('127.0.0.1', 'phpunit'),
            true,
            200,
        );

        $this->assertDatabaseCount('audit_logs', 3, 'testing');
        $this->assertDatabaseHas('audit_logs', ['resource_id' => '4503'], 'testing');
    }

    public function test_log_many_is_a_no_op_for_an_empty_resource_id_list(): void
    {
        $this->makeLogger()->logMany(
            ActionType::Read,
            'client',
            [],
            new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']),
            new NetworkContext('127.0.0.1', 'phpunit'),
            true,
            200,
        );

        $this->assertDatabaseCount('audit_logs', 0, 'testing');
    }

    public function test_log_many_rejects_a_resource_type_outside_the_allow_list(): void
    {
        $this->expectException(InvalidResourceTypeException::class);

        $this->makeLogger()->logMany(
            ActionType::Read,
            'visit',
            ['1'],
            new Actor(1, 'Jane Doe', 'jane@example.com', ['admin']),
            new NetworkContext('127.0.0.1', 'phpunit'),
            true,
            200,
        );
    }
}
