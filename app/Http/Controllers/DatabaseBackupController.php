<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DatabaseBackupController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:database.backup.download');
    }

    public function download(): StreamedResponse
    {
        abort_unless(config('database.default') === 'mysql', 422, 'Database download currently supports MySQL only.');

        $database = (string) config('database.connections.mysql.database');
        $safeDatabase = preg_replace('/[^A-Za-z0-9_-]/', '_', $database) ?: 'crm';
        $filename = $safeDatabase.'_backup_'.now()->format('Y-m-d_H-i-s').'.sql';

        return response()->streamDownload(function () use ($database) {
            @set_time_limit(0);
            echo "-- CRM Database Backup\n";
            echo '-- Database: '.str_replace(["\r", "\n"], '', $database)."\n";
            echo '-- Generated: '.now()->toDateTimeString()."\n\n";
            echo "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\nSET NAMES utf8mb4;\n\n";

            $pdo = DB::connection()->getPdo();
            $tables = DB::select('SHOW FULL TABLES WHERE Table_type = ?', ['BASE TABLE']);

            foreach ($tables as $tableRow) {
                $values = array_values((array) $tableRow);
                $table = (string) ($values[0] ?? '');
                if ($table === '') continue;

                $quotedTable = '`'.str_replace('`', '``', $table).'`';
                $createRows = DB::select('SHOW CREATE TABLE '.$quotedTable);
                $create = (array) ($createRows[0] ?? []);
                $createSql = $create['Create Table'] ?? array_values($create)[1] ?? null;
                if (!$createSql) continue;

                echo "-- ---------------------------------------------\n";
                echo '-- Table structure for '.$quotedTable."\n";
                echo "DROP TABLE IF EXISTS {$quotedTable};\n{$createSql};\n\n";

                $statement = $pdo->query('SELECT * FROM '.$quotedTable);
                if (!$statement) continue;

                $columns = [];
                $batch = [];
                while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
                    if (!$columns) {
                        $columns = array_keys($row);
                    }
                    $valuesSql = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $valuesSql[] = 'NULL';
                        } elseif (is_int($value) || is_float($value)) {
                            $valuesSql[] = (string) $value;
                        } else {
                            $valuesSql[] = $pdo->quote((string) $value);
                        }
                    }
                    $batch[] = '('.implode(',', $valuesSql).')';
                    if (count($batch) >= 200) {
                        $columnSql = implode(',', array_map(fn ($column) => '`'.str_replace('`', '``', $column).'`', $columns));
                        echo "INSERT INTO {$quotedTable} ({$columnSql}) VALUES\n".implode(",\n", $batch).";\n";
                        $batch = [];
                    }
                }
                if ($batch && $columns) {
                    $columnSql = implode(',', array_map(fn ($column) => '`'.str_replace('`', '``', $column).'`', $columns));
                    echo "INSERT INTO {$quotedTable} ({$columnSql}) VALUES\n".implode(",\n", $batch).";\n";
                }
                echo "\n";
                if (function_exists('flush')) flush();
            }

            echo "SET FOREIGN_KEY_CHECKS=1;\n";
        }, $filename, [
            'Content-Type' => 'application/sql; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
