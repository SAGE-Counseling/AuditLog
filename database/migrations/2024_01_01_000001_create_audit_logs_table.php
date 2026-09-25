<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $connection = config('audit-log.migration_connection', 'audit_migrator');

        if (Schema::connection($connection)->hasTable('audit_logs')) {
            return;
        }

        Schema::connection($connection)->create('audit_logs', function (Blueprint $table) {
            $table->id();
            // Nullable: a failed login attempt has no authenticated user to attach,
            // only the submitted email — see SageCounseling\AuditLog\Actor.
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
            ->dropIfExists('audit_logs');
    }
};
