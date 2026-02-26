<?php

namespace Antigravity\AdvancedAttributes\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\AdminDetailsController;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Request;

/**
 * Admin controller for managing attribute values.
 */
class AttributeValue extends AdminDetailsController
{
    /**
     * Current class template name.
     *
     * @var string
     */
    protected $_sThisTemplate = '@advanced_attributes/admin/tpl/advanced_attribute_value.html.twig';

    /**
     * Executes parent method parent::render(), creates oxattribute object,
     * passes it's data to smarty engine and returns name of template file
     * "attribute_value.html.twig".
     *
     * @return string
     */
    public function render()
    {
        parent::render();

        $this->_aViewData['edit'] = $oAttribute = oxNew(\Antigravity\AdvancedAttributes\Model\Attribute::class);

        $sOxId = $this->getEditObjectId();
        if (isset($sOxId) && $sOxId != "-1") {
            // load object
            $oAttribute->load($sOxId);

            // Fetch available values using the model method we created
            $a = $oAttribute->getAvailableValues($this->_iEditLang);
            $this->_aViewData['attributeValues'] = $a;
        }

        return $this->_sThisTemplate;
    }

    /**
     * Saves attribute value changes.
     */
    public function save()
    {
        $oAttribute = oxNew(\OxidEsales\Eshop\Application\Model\Attribute::class);
        if ($oAttribute->load($this->getEditObjectId())) {
            $aValues = Registry::getRequest()->getRequestEscapedParameter("values");
            if (is_array($aValues)) {
                foreach ($aValues as $sValueId => $aFields) {
                    $oVal = oxNew(\Antigravity\AdvancedAttributes\Model\AttributeValue::class);
                    if ($oVal->load($sValueId)) {
                        $sOldValue = $oVal->oxattributevalues__oxvalue->value;
                        
                        // Assign new values
                        $oVal->assign($aFields);
                        
                        // Handle Image Deletion
                        $aDeleteImages = Registry::getRequest()->getRequestEscapedParameter("deleteImage");
                        if (isset($aDeleteImages[$sValueId])) {
                             $oVal->oxattributevalues__oximage = new \OxidEsales\Eshop\Core\Field("");
                        }

                        // Handle Image Upload
                        $sNewImage = $this->_handleUpload('image', $sValueId);
                        if ($sNewImage) {
                            $oVal->oxattributevalues__oximage = new \OxidEsales\Eshop\Core\Field($sNewImage);
                        }

                        // Save current language object
                        $oVal->save();

                        // Synch shared fields (Sort, Color, Image) to other languages
                        $this->_syncSharedFields($oVal);

                        $sNewValue = $oVal->oxattributevalues__oxvalue->value;
                        
                        // Cascading update if value changed (only for this language)
                        if ($sOldValue != $sNewValue) {
                            $this->_updateAssignedArticles($oAttribute->getId(), $sValueId, $sOldValue, $sNewValue);
                        }
                    }
                }
            }
        }
    }

    /**
     * Synchronize shared fields across all languages for the same OXOBJID
     * @param \Antigravity\AdvancedAttributes\Model\AttributeValue $oSourceVal
     */
    protected function _syncSharedFields($oSourceVal)
    {
        $sObjId = $oSourceVal->oxattributevalues__oxobjid->value;
        if (!$sObjId) return;

        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        
        $sSort = $oDb->quote($oSourceVal->oxattributevalues__oxsort->value);
        $sColor = $oDb->quote($oSourceVal->oxattributevalues__oxcolor->value);
        $sImage = $oDb->quote($oSourceVal->oxattributevalues__oximage->value);
        
        $sQ = "UPDATE oxattributevalues 
               SET OXSORT = $sSort, OXCOLOR = $sColor, OXIMAGE = $sImage 
               WHERE OXOBJID = " . $oDb->quote($sObjId);
               
        $oDb->execute($sQ);
    }

    /**
     * Update assigned articles when value changes
     */
    protected function _updateAssignedArticles($sAttrId, $sValueId, $sOldValue, $sNewValue)
    {
        $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
        $sQuotedNew = $oDb->quote($sNewValue);
        $sQuotedOld = $oDb->quote($sOldValue);
        $sQuotedId = $oDb->quote($sValueId);
        $sQuotedAttrId = $oDb->quote($sAttrId);

        // 1. Update by ID linkage
        $sQ = "UPDATE oxobject2attribute SET OXVALUE = $sQuotedNew WHERE OXATTRVALUEID = $sQuotedId";
        $oDb->execute($sQ);

        // 2. Update by Text linkage (fallback)
        $sQ2 = "UPDATE oxobject2attribute 
                SET OXVALUE = $sQuotedNew 
                WHERE OXATTRID = $sQuotedAttrId 
                AND OXVALUE = $sQuotedOld 
                AND (OXATTRVALUEID IS NULL OR OXATTRVALUEID = '')";
        $oDb->execute($sQ2);
    }

    /**
     * Creates a new value.
     */
    public function create()
    {
        $sOxId = $this->getEditObjectId();
        $sNewValue = Registry::getRequest()->getRequestEscapedParameter("newValue");
        $sNewSort = Registry::getRequest()->getRequestEscapedParameter("newSort");
        $sNewColor = Registry::getRequest()->getRequestEscapedParameter("newColor");

        if ($sOxId && $sNewValue) {
            $sObjId = \OxidEsales\Eshop\Core\Registry::getUtilsObject()->generateUid();
            $aLangs = \OxidEsales\Eshop\Core\Registry::getLang()->getLanguageNames();
            
            // Handle Image Upload for Creation
            $sImage = $this->_handleUpload('newImage');

            foreach ($aLangs as $iLang => $sLangName) {
                $oVal = oxNew(\Antigravity\AdvancedAttributes\Model\AttributeValue::class);
                $oVal->oxattributevalues__oxobjid = new \OxidEsales\Eshop\Core\Field($sObjId);
                $oVal->oxattributevalues__oxattrid = new \OxidEsales\Eshop\Core\Field($sOxId);
                $oVal->oxattributevalues__oxvalue = new \OxidEsales\Eshop\Core\Field($sNewValue);
                $oVal->oxattributevalues__oxlang = new \OxidEsales\Eshop\Core\Field($iLang);
                
                $oVal->oxattributevalues__oxsort = new \OxidEsales\Eshop\Core\Field((int)$sNewSort);
                $oVal->oxattributevalues__oxcolor = new \OxidEsales\Eshop\Core\Field($sNewColor);
                if ($sImage) {
                    $oVal->oxattributevalues__oximage = new \OxidEsales\Eshop\Core\Field($sImage);
                }

                $oVal->save();
            }
        }
    }

    /**
     * Handle generic image upload
     * @param string $sInputName
     * @param string|null $sKey
     * @return string|null Filename
     */
    protected function _handleUpload($sInputName, $sKey = null)
    {
        $aFile = $_FILES[$sInputName] ?? null;
        if (!$aFile) return null;

        $sName = ($sKey !== null) ? ($aFile['name'][$sKey] ?? '') : ($aFile['name'] ?? '');
        $sTmpName = ($sKey !== null) ? ($aFile['tmp_name'][$sKey] ?? '') : ($aFile['tmp_name'] ?? '');
        $iError = ($sKey !== null) ? ($aFile['error'][$sKey] ?? 4) : ($aFile['error'] ?? 4);

        if ($sName && $iError === 0 && is_uploaded_file($sTmpName)) {
            $sDir = Registry::getConfig()->getPictureDir(false) . 'master/attributes/';
            if (!is_dir($sDir)) {
                mkdir($sDir, 0777, true);
            }
            
            // Clean filename
            $sName = preg_replace('/[^a-zA-Z0-9._-]/', '', basename($sName));
            // Avoid duplicates
            while (file_exists($sDir . $sName)) {
                $sName = 'copy_' . $sName;
            }
            
            if (move_uploaded_file($sTmpName, $sDir . $sName)) {
                return $sName;
            }
        }
        return null;
    }

    /**
     * Deletes a value.
     */
    public function delete()
    {
        $sValueId = Registry::getRequest()->getRequestEscapedParameter("valueId");
        if ($sValueId) {
            $oVal = oxNew(\Antigravity\AdvancedAttributes\Model\AttributeValue::class);
            if ($oVal->load($sValueId)) {
                $oDb = \OxidEsales\Eshop\Core\DatabaseProvider::getDb();
                $sObjId = $oVal->oxattributevalues__oxobjid->value;

                if ($sObjId) {
                    $sQuotedObjId = $oDb->quote($sObjId);

                    // Clean up product assignments for all sibling value IDs
                    $aIds = $oDb->getCol("SELECT OXID FROM oxattributevalues WHERE OXOBJID = $sQuotedObjId");
                    foreach ($aIds as $sId) {
                        $oDb->execute("DELETE FROM oxobject2attribute WHERE OXATTRVALUEID = " . $oDb->quote($sId));
                    }

                    // Delete all language rows for this value
                    $oDb->execute("DELETE FROM oxattributevalues WHERE OXOBJID = $sQuotedObjId");
                } else {
                    // Single row, no OBJID grouping
                    $oDb->execute("DELETE FROM oxobject2attribute WHERE OXATTRVALUEID = " . $oDb->quote($sValueId));
                    $oVal->delete();
                }
            }
        }
    }
}