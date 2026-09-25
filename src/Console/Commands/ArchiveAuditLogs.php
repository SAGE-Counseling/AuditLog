<?php

namespace SageCounseling\AuditLog\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Config-driven retention/archival for audit_logs. Intended to run on the consuming
 * app's scheduler rather than requiring manual invocation.
 *
 * Uses the migration_connection, not the runtime connection: the runtime connection's
 * credential is expected to be INSERT-only in production, so copying rows out and
 * deleting them from the primary table needs the more privileged connection already
 * used for schema migrations.
 */
class ArchiveAuditLogs extends Command
{
    protected $signature = 'audit-log:archive';

    protected $description = 'Archives audit_logs rows past the configured primary-retention period, and purges audit_log_archives rows past the configured archive-retention period.';

    public function handle(): int
    {
        $this->archivePrimaryRows();
        $this->purgeExpiredArchiveRows();

        return self::SUCCESS;
    }

    private function connection(): string
    {
        return config('audit-log.migration_connection', 'audit_migrator');
    }

    private function archivePrimaryRows(): void
    {
        $cutoff = now('UTC')->subDays((int) config('audit-log.retention.primary_days'));

        DB::connection($this->connection())
            ->table('audit_logs')
            ->where('occurred_at', '<', $cutoff)
            ->orderBy('id')
            ->chunkById(500, function ($rows) {
                $archiveRows = $rows->map(fn ($row) => (array) $row)->all();
                $ids = $rows->pluck('id');

                // Transaction per chunk: if the archive insert doesn't account for every
                // row in this chunk (a constraint failure, not just an idempotent
                // re-insert of an already-archived id), roll back rather than deleting
                // rows from audit_logs that were never safely copied out.
                DB::connection($this->connection())->transaction(function () use ($archiveRows, $ids) {
                    DB::connection($this->connection())
                        ->table('audit_log_archives')
                        ->insertOrIgnore($archiveRows);

                    $archivedCount = DB::connection($this->connection())
                        ->table('audit_log_archives')
                        ->whereIn('id', $ids)
                        ->count();

                    if ($archivedCount !== count($archiveRows)) {
                        throw new RuntimeException(
                            "Audit log archive insert only accounted for {$archivedCount} of ".count($archiveRows).' rows in this chunk; aborting delete to avoid data loss.'
                        );
                    }

                    DB::connection($this->connection())
                        ->table('audit_logs')
                        ->whereIn('id', $ids)
                        ->delete();
                });
            });
    }

    private function purgeExpiredArchiveRows(): void
    {
        $cutoff = now('UTC')->subDays((int) config('audit-log.retention.archive_days'));

        DB::connection($this->connection())
            ->table('audit_log_archives')
            ->where('occurred_at', '<', $cutoff)
            ->delete();
    }
}
