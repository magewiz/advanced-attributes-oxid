<?php

// Adjust path to bootstrap if necessary
require_once __DIR__ . '/../../bootstrap.php';

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;

try {
    $db = DatabaseProvider::getDb();
    $sqlFile = __DIR__ . '/install.sql';

    if (!file_exists($sqlFile)) {
        die("install.sql not found.\n");
    }

    $sqlContent = file_get_contents($sqlFile);
    $queries = explode(';', $sqlContent);

    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }
        try {
            $db->execute($query);
            echo "Executed: " . substr($query, 0, 50) . "...\n";
        }
        catch (\Exception $e) {
            // Ignore "Duplicate column name" or "Table already exists" errors to allow re-runs
            if (strpos($e->getMessage(), 'Duplicate column name') !== false || strpos($e->getMessage(), 'already exists') !== false) {
                echo "Skipped (already exists): " . substr($query, 0, 50) . "...\n";
            }
            else {
                echo "Error: " . $e->getMessage() . "\n";
            }
        }
    }

    // Clear cache to ensure schema changes are picked up
    Registry::getUtils()->oxResetFileCache();
    echo "Migration completed.\n";

}
catch (\Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
    exit(1);
}