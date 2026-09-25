<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Same shape as audit_logs, minus updated_at (rows are immutable) — id is
     * preserved verbatim from the source row on archival rather than reassigned, so
     * SageCounseling\AuditLog\Console\Commands\ArchiveAuditLogs can insertOrIgnore
     * idempotently. Lives on the same migration_connection-owned database as
     * audit_logs rather than a separate connection: the isolation that matters is
     * this data being unreachable from the app's normal read/write credentials,
     * which already holds for this table.
     */
    public function up(): void
    {
        $connection = config('audit-log.migration_connection', 'audit_migrator');

        if (Schema::connection($connection)->hasTable('audit_log_archives')) {
            return;
        }

        Schema::connection($connection)->create('audit_log_archives', function (Blueprint $table) {
            $table->unsignedBigInteger('id')->primary();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('user_name')->nullable();
            $table->string('email');
            $table->json('roles');
            $table->string('action_type', 20);
            $table->string('resource_type', 50);
            $table->string('resource_id');
            $table->timestampTz('occurred_at');
            $table->string('ip_address', 45);
            $table->text('user_agent');
            $table->boolean('succeeded');
            $table->unsignedSmallInteger('status_code');
            $table->string('purpose_of_use', 20);

            $table->index(['resource_type', 'resource_id']);
            $table->index('user_id');
            $table->index('occurred_at');
        });
    }

    public function down(): void
    {
        Schema::connection(config('audit-log.migration_connection', 'audit_migrator'))
            ->dropIfExists('audit_log_archives');
    }
};
