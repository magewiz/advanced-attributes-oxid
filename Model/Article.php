<?php

namespace Antigravity\AdvancedAttributes\Model;

use OxidEsales\Eshop\Application\Model\Article as ArticleModel;
use OxidEsales\Eshop\Core\Field;
use OxidEsales\Eshop\Core\DatabaseProvider;

/**
 * Extension of Article model to handle advanced attributes.
 */
class Article extends ArticleModel
{
    /**
     * Overridden to fetch advanced attributes
     *
     * @return \OxidEsales\Eshop\Application\Model\AttributeList|array
     */
    public function getAttributes()
    {
        $oAttributes = parent::getAttributes();
        if ($oAttributes) {
            $oDb = DatabaseProvider::getDb();
            foreach ($oAttributes as $oAttr) {
                // Ensure oxtype is available
                $sType = $oAttr->getType();
                $sAttrId = $oAttr->getId();
                $sValue = $oAttr->oxattribute__oxvalue->value;
                
                if (!$sValue) continue;

                if ($sType == 'COLOR' || $sType == 'SELECT') {
                    // Fetch color from oxattributevalues for the FIRST value if it's a comma separated list
                    // (SELECT is usually single, COLOR might be used as SELECT)
                    $aVals = explode(',', $sValue);
                    $sVal = trim($aVals[0]);
                    
                    $sQ = "SELECT OXCOLOR FROM oxattributevalues 
                           WHERE OXATTRID = " . $oDb->quote($sAttrId) . " 
                           AND OXVALUE = " . $oDb->quote($sVal) . " 
                           LIMIT 1";

                    $sColor = $oDb->getOne($sQ);
                    if ($sColor) {
                        $oAttr->oxattribute__oxcolor = new Field($sColor);
                    }
                }
                
                if ($sType == 'IMAGE') {
                    $aVals = explode(',', $sValue);
                    $sVal = trim($aVals[0]);
                    
                    $sQ = "SELECT OXIMAGE FROM oxattributevalues 
                           WHERE OXATTRID = " . $oDb->quote($sAttrId) . " 
                           AND OXVALUE = " . $oDb->quote($sVal) . " 
                           LIMIT 1";

                    $sImage = $oDb->getOne($sQ);
                    if ($sImage) {
                        $oAttr->oxattribute__oximage = new Field($sImage);
                    }
                }

                if ($sType == 'MULTISELECT') {
                    // MULTISELECT handles multiple values. 
                    // Explode the value into an array for the template to handle
                    $oAttr->aValues = explode(',', $sValue);
                    $oAttr->aValues = array_map('trim', $oAttr->aValues);
                }
            }
        }
        return $oAttributes;
    }
}