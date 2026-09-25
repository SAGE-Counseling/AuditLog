<?php

namespace SageCounseling\AuditLog\Tests;

use SageCounseling\AuditLog\PurposeOfUse;
use SageCounseling\AuditLog\PurposeOfUseResolver;

class PurposeOfUseResolverTest extends TestCase
{
    public function test_resolves_purpose_from_matching_role(): void
    {
        $resolver = new PurposeOfUseResolver(
            [
                'admin' => PurposeOfUse::SystemAdministration,
                'parole' => PurposeOfUse::Legal,
                'user' => PurposeOfUse::HealthcareOperations,
            ],
            PurposeOfUse::HealthcareOperations,
        );

        $this->assertSame(PurposeOfUse::SystemAdministration, $resolver->resolve(['admin']));
    }

    public function test_most_specific_role_wins_when_a_user_holds_multiple_roles(): void
    {
        $resolver = new PurposeOfUseResolver(
            [
                'admin' => PurposeOfUse::SystemAdministration,
                'parole' => PurposeOfUse::Legal,
                'user' => PurposeOfUse::HealthcareOperations,
            ],
            PurposeOfUse::HealthcareOperations,
        );

        // 'parole' is listed before 'user', so it should win even though the caller
        // holds both roles simultaneously.
        $this->assertSame(PurposeOfUse::Legal, $resolver->resolve(['user', 'parole']));
    }

    public function test_falls_back_to_default_when_no_role_matches(): void
    {
        $resolver = new PurposeOfUseResolver(
            ['admin' => PurposeOfUse::SystemAdministration],
            PurposeOfUse::HealthcareOperations,
        );

        $this->assertSame(PurposeOfUse::HealthcareOperations, $resolver->resolve(['guest']));
    }
}
