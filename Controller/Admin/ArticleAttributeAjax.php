<?php

namespace Antigravity\AdvancedAttributes\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\ArticleAttributeAjax as CoreArticleAttributeAjax;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Extension of ArticleAttributeAjax to include attribute type
 * and advanced value handling.
 */
class ArticleAttributeAjax extends CoreArticleAttributeAjax
{
    /**
     * Extend init to add oxtype column to container2
     * without completely overriding $_aColumns.
     */
    public function __construct()
    {
        parent::__construct();

        // Make oxvalue visible in container2
        foreach ($this->_aColumns['container2'] as &$aCol) {
            if ($aCol[0] == 'oxvalue') {
                $aCol[2] = 1; // Set visible to true
                $aCol[4] = 0; // Ensure ident is false
            }
        }

        // Add 'oxtype' as a hidden ident column to container2
        // Format: [field, table, visible, multilang, ident]
        $this->_aColumns['container2'][] = ['oxtype', 'oxattribute', 0, 0, 1];
        $this->_aColumns['container2'][] = ['oxattrvalueid', 'oxobject2attribute', 0, 0, 1];
    }

    /**
     * Override getQuery to JOIN oxattribute for container2 so oxtype is available.
     *
     * @return string
     */
    protected function getQuery()
    {
        $oDb = DatabaseProvider::getDb();
        $sArtId = Registry::getRequest()->getRequestEscapedParameter('oxid');
        $sSynchArtId = Registry::getRequest()->getRequestEscapedParameter('synchoxid');

        $sAttrViewName = $this->getViewName('oxattribute');
        $sO2AViewName = $this->getViewName('oxobject2attribute');

        if ($sArtId) {
            // Assigned attributes — join oxattribute so oxtype is available
            $sQAdd = " from {$sO2AViewName} left join {$sAttrViewName} " .
                     "on {$sAttrViewName}.oxid={$sO2AViewName}.oxattrid " .
                     " where {$sO2AViewName}.oxobjectid = " . $oDb->quote($sArtId) . " ";
        } else {
            $sQAdd = " from {$sAttrViewName} where {$sAttrViewName}.oxid not in ( select {$sO2AViewName}.oxattrid " .
                     "from {$sO2AViewName} left join {$sAttrViewName} " .
                     "on {$sAttrViewName}.oxid={$sO2AViewName}.oxattrid " .
                     " where {$sO2AViewName}.oxobjectid = " . $oDb->quote($sSynchArtId) . " ) ";
        }

        return $sQAdd;
    }

    /**
     * Extends parent saveAttributeValue to also store the OXATTRVALUEID
     * when a structured value (dropdown) is selected.
     */
    public function saveAttributeValue()
    {
        // Let the parent handle the standard save (OXVALUE update)
        parent::saveAttributeValue();

        // Additionally store the value ID linkage
        $sValueId = Registry::getRequest()->getRequestEscapedParameter('attr_value_id');
        if ($sValueId) {
            // No longer truncating, as we increased OXATTRVALUEID to VARCHAR(255)
            $sArticleId = Registry::getRequest()->getRequestEscapedParameter('oxid');
            $sAttrId = Registry::getRequest()->getRequestEscapedParameter('attr_oxid');

            if ($sArticleId && $sAttrId) {
                $oDb = DatabaseProvider::getDb();
                $sViewName = $this->getViewName('oxobject2attribute');

                $sQ = "UPDATE {$sViewName} 
                       SET OXATTRVALUEID = " . $oDb->quote($sValueId) . "
                       WHERE OXOBJECTID = " . $oDb->quote($sArticleId) . "
                       AND OXATTRID = " . $oDb->quote($sAttrId);

                $oDb->execute($sQ);
            }
        }
    }

    /**
     * Helper to get values for an attribute.
     * Called via AJAX from the popup template.
     */
    public function getAttributeValuesLegacy()
    {
        $sAttrId = Registry::getRequest()->getRequestEscapedParameter('attr_oxid');
        if ($sAttrId) {
            $oAttribute = oxNew(\Antigravity\AdvancedAttributes\Model\Attribute::class);
            if ($oAttribute->load($sAttrId)) {
                $oList = $oAttribute->getAvailableValues();
                $aValues = [];
                foreach ($oList as $oVal) {
                    $aValues[] = [
                        'id' => $oVal->oxattributevalues__oxid->value,
                        'value' => $oVal->oxattributevalues__oxvalue->value,
                        'sort' => $oVal->oxattributevalues__oxsort->value
                    ];
                }
                echo json_encode($aValues);
                exit;
            }
        }
        echo "[]";
        exit;
    }
}