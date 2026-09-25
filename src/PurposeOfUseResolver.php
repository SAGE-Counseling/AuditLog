<?php

namespace SageCounseling\AuditLog;

/**
 * Derives purpose_of_use automatically from the acting user's role(s) — there is no
 * manual picker. The role-to-purpose mapping is registered by the consuming app (not
 * hardcoded here), since each consumer's user classes need their own mapping.
 */
final class PurposeOfUseResolver
{
    /**
     * @param  array<string, PurposeOfUse>  $purposeOfUseByRole  Checked in the order
     *         given, most-specific role first — a user can hold multiple roles
     *         simultaneously, so mapping order settles which purpose wins.
     */
    public function __construct(
        private readonly array $purposeOfUseByRole,
        private readonly PurposeOfUse $default,
    ) {
    }

    /**
     * @param  array<int, string>  $roles
     */
    public function resolve(array $roles): PurposeOfUse
    {
        foreach ($this->purposeOfUseByRole as $role => $purpose) {
            if (in_array($role, $roles, true)) {
                return $purpose;
            }
        }

        return $this->default;
    }
}
