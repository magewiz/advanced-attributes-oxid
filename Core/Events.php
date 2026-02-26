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

            self::verifyAndPatchTemplate();

        }
        catch (\Exception $e) {
            // Log error if needed, but avoid breaking activation flow completely if possible
            error_log("AdvancedAttributes Migration Error: " . $e->getMessage());
        }
    }

    /**
     * Patches the administration template to include type selection
     */
    private static function verifyAndPatchTemplate()
    {
        $config = Registry::getConfig();
        $shopDir = $config->getConfigParam('sShopDir');
        
        $targetFile = $shopDir . '/Application/views/admin_twig/tpl/attribute_main.html.twig';
        $vendorFile = $shopDir . '/../vendor/oxid-esales/twig-admin-theme/tpl/attribute_main.html.twig';
        
        // Ensure directory exists
        $targetDir = dirname($targetFile);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        // Copy from vendor if not exists
        if (!file_exists($targetFile)) {
            if (file_exists($vendorFile)) {
                copy($vendorFile, $targetFile);
            } else {
                error_log("Vendor template not found: " . $vendorFile);
                return;
            }
        }

        $content = file_get_contents($targetFile);
        
        // Check if already patched
        if (strpos($content, 'name="editval[oxattribute__oxtype]"') !== false) {
            return;
        }

        // Define injection code
        $injection = '
            {# Added by Advanced Attributes Module #}
            <tr>
                <td class="edittext" width="120">
                    {{ translate({ ident: "DETAILS_Attributes_TYPE" }) }}
                </td>
                <td class="edittext">
                    <select name="editval[oxattribute__oxtype]" class="editinput" {{ readonly }}>
                        <option value="TEXT" {% if edit.oxattribute__oxtype.value == "TEXT" %} selected {% endif %}>Text</option>
                        <option value="SELECT" {% if edit.oxattribute__oxtype.value == "SELECT" %} selected {% endif %}>Selection</option>
                        <option value="MULTISELECT" {% if edit.oxattribute__oxtype.value == "MULTISELECT" %} selected {% endif %}>Multiselect</option>
                        <option value="BOOL" {% if edit.oxattribute__oxtype.value == "BOOL" %} selected {% endif %}>Boolean</option>
                        <option value="DATE" {% if edit.oxattribute__oxtype.value == "DATE" %} selected {% endif %}>Date</option>
                        <option value="COLOR" {% if edit.oxattribute__oxtype.value == "COLOR" %} selected {% endif %}>Color</option>
                        <option value="IMAGE" {% if edit.oxattribute__oxtype.value == "IMAGE" %} selected {% endif %}>Image</option>
                    </select>
                </td>
            </tr>';

        // Search for insertion point using unique helper ID
        $search = "{'sHelpId': help_id(\"HELP_ATTRIBUTE_MAIN_DISPLAYINBASKET\"), 'sHelpText': help_text(\"HELP_ATTRIBUTE_MAIN_DISPLAYINBASKET\")}";
        
        // If exact match fails (e.g. whitespace differences), try simpler match
        if (strpos($content, $search) === false) {
            $search = 'HELP_ATTRIBUTE_MAIN_DISPLAYINBASKET';
        }
        
        // Find position after the matched line's closing tags
        $pos = strpos($content, $search);
        if ($pos !== false) {
            // Find end of current row </tr>
            $endRow = strpos($content, '</tr>', $pos);
            if ($endRow !== false) {
                $newContent = substr_replace($content, $injection, $endRow + 5, 0);
                file_put_contents($targetFile, $newContent);
            }
        }
    }
}