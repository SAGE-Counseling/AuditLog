<?php

namespace SageCounseling\AuditLog\Tests;

use SageCounseling\AuditLog\InvalidResourceTypeException;
use SageCounseling\AuditLog\ResourceTypeAllowList;

class ResourceTypeAllowListTest extends TestCase
{
    public function test_allowed_resource_type_passes(): void
    {
        $allowList = new ResourceTypeAllowList(['client', 'visit']);

        $this->assertTrue($allowList->isAllowed('client'));
        $allowList->assertAllowed('client');
        $this->assertTrue(true);
    }

    public function test_disallowed_resource_type_fails(): void
    {
        $allowList = new ResourceTypeAllowList(['client']);

        $this->assertFalse($allowList->isAllowed('visit'));
    }

    public function test_assert_allowed_throws_for_unregistered_resource_type(): void
    {
        $allowList = new ResourceTypeAllowList(['client']);

        $this->expectException(InvalidResourceTypeException::class);
        $this->expectExceptionMessage('Resource type [visit] is not registered with the audit log allow-list.');

        $allowList->assertAllowed('visit');
    }
}
