<?php

namespace Antigravity\AdvancedAttributes\Core;

use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DbMetaDataHandler;

class Events
{
    public static function onActivate()
    {
        try {
            $db = DatabaseProvider::getDb();
            $sqlFile = __DIR__ . '/../install.sql';

            if (!file_exists($sqlFile)) {
                return;
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
                }
                catch (\Exception $e) {
                // Ignore errors for existing tables/columns to allow safe re-activation
                }
            }


            // Clear cache and regenerate views
            $metaDataHandler = oxNew(DbMetaDataHandler::class);
            $metaDataHandler->updateViews();
            Registry::getUtils()->oxResetFileCache();

        }
        catch (\Exception $e) {
            error_log("AdvancedAttributes Migration Error: " . $e->getMessage());
        }
    }
}