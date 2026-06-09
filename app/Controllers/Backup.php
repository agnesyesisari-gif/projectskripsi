<?php

namespace App\Controllers;

class Backup extends BaseController
{
    public function index(): string
    {
        return $this->render('backup/index', [
            'pageTitle'  => 'Backup Database',
            'activePage' => 'backup',
        ]);
    }

    public function proses()
    {
        $db     = \Config\Database::connect();
        $tables = $db->listTables();
        $sql    = "-- GKJ PENARUBAN Backup\n";
        $sql   .= "-- Tanggal: " . date('Y-m-d H:i:s') . "\n\n";
        $sql   .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // CREATE TABLE
            $createResult = $db->query("SHOW CREATE TABLE `$table`")->getRowArray();
            $createSQL    = $createResult['Create Table'] ?? '';
            $sql .= "DROP TABLE IF EXISTS `$table`;\n$createSQL;\n\n";

            // INSERT DATA
            $rows = $db->table($table)->get()->getResultArray();
            if (! empty($rows)) {
                $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
                $sql .= "INSERT INTO `$table` ($cols) VALUES\n";
                $vals = [];
                foreach ($rows as $row) {
                    $escaped = array_map(fn($v) => $v === null ? 'NULL' : "'" . addslashes($v) . "'", $row);
                    $vals[]  = '(' . implode(', ', $escaped) . ')';
                }
                $sql .= implode(",\n", $vals) . ";\n\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";

        $filename = 'backup_gkj_' . date('Ymd_His') . '.sql';

        return $this->response
            ->setHeader('Content-Type', 'application/octet-stream')
            ->setHeader('Content-Disposition', "attachment; filename=\"$filename\"")
            ->setHeader('Content-Length', strlen($sql))
            ->setBody($sql);
    }
}
