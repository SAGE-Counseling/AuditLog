<?php

namespace SageCounseling\AuditLog\Models;

use Illuminate\Database\Eloquent\Model;
use SageCounseling\AuditLog\ActionType;
use SageCounseling\AuditLog\PurposeOfUse;

/**
 * A single, immutable audit-log row. No updated_at column — the schema itself
 * communicates the immutability a DB-level insert-only grant is expected to enforce
 * in production. occurred_at is set explicitly by the writer, not a DB-default
 * timestamp.
 */
class AuditLogEntry extends Model
{
    protected $table = 'audit_logs';

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'roles' => 'array',
        'action_type' => ActionType::class,
        'occurred_at' => 'datetime',
        'succeeded' => 'boolean',
        'purpose_of_use' => PurposeOfUse::class,
    ];

    /**
     * Connection name is config-driven, not a fixed property, so each consuming app
     * can name its isolated audit database connection however it wants.
     */
    public function getConnectionName(): ?string
    {
        return config('audit-log.connection', 'audit');
    }
}
