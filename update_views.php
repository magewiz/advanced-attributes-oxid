<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../bootstrap.php';

use OxidEsales\Eshop\Core\DbMetaDataHandler;
use OxidEsales\Eshop\Core\Registry;

echo "Updating views...\n";

try {
    $meta = oxNew(DbMetaDataHandler::class);
    $meta->updateViews();

    // Also clear cache to be sure
    Registry::getUtils()->oxResetFileCache();

    echo "Views updated successfully.\n";
}
catch (\Exception $e) {
    echo "Error updating views: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}