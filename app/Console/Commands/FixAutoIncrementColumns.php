<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class FixAutoIncrementColumns extends Command
{
	protected $signature = 'db:fix-auto-increment {--dry-run : Show changes without executing}';

	protected $description = 'Ensure id columns are AUTO_INCREMENT on all tables';

	public function handle(): int
	{
		$database = DB::getDatabaseName();
		$tables = DB::select('SHOW TABLES');

		$tableKey = 'Tables_in_' . $database;
		$dryRun = (bool) $this->option('dry-run');

		foreach ($tables as $table) {
			$tableName = $table->{$tableKey};

			if (! Schema::hasColumn($tableName, 'id')) {
				continue;
			}

			$column = DB::selectOne("
                SELECT COLUMN_NAME, COLUMN_TYPE, EXTRA, COLUMN_KEY
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = ?
                  AND TABLE_NAME = ?
                  AND COLUMN_NAME = 'id'
            ", [$database, $tableName]);

			if (! $column) {
				continue;
			}

			// Skip if already auto increment
			if (str_contains((string) $column->EXTRA, 'auto_increment')) {
				continue;
			}

			// Only allow integer-based ids
			if (! str_contains($column->COLUMN_TYPE, 'int')) {
				$this->warn("Skipped {$tableName}.id (not integer)");
				continue;
			}

			$sql = sprintf(
				'ALTER TABLE `%s` MODIFY `id` %s NOT NULL AUTO_INCREMENT',
				$tableName,
				$column->COLUMN_TYPE
			);

			if ($column->COLUMN_KEY !== 'PRI') {
				$sql .= ', ADD PRIMARY KEY (`id`)';
			}

			if ($dryRun) {
				$this->line("[DRY RUN] {$sql}");
			} else {
				DB::statement($sql);
				$this->info("Fixed {$tableName}.id");
			}
		}

		return self::SUCCESS;
	}
}
