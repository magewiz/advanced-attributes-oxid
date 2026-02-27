<?php

namespace Antigravity\AdvancedAttributes\Model;

use OxidEsales\Eshop\Application\Model\Attribute as AttributionModel;

/**
 * Extension of Attribute model to handle types.
 */
class Attribute extends AttributionModel
{
    const TYPE_TEXT = 'TEXT';
    const TYPE_SELECT = 'SELECT';
    const TYPE_MULTISELECT = 'MULTISELECT';
    const TYPE_BOOL = 'BOOL';
    const TYPE_DATE = 'DATE';
    const TYPE_DATETIME = 'DATETIME';
    const TYPE_PRICE = 'PRICE';
    const TYPE_COLOR = 'COLOR';
    const TYPE_IMAGE = 'IMAGE';
    const TYPE_TEXT_SWATCH = 'TEXT_SWATCH';
    const TYPE_VISUAL_SWATCH = 'VISUAL_SWATCH';

    const TYPES_WITH_VALUES = [
        self::TYPE_SELECT,
        self::TYPE_MULTISELECT,
        self::TYPE_COLOR,
        self::TYPE_IMAGE,
        self::TYPE_TEXT_SWATCH,
        self::TYPE_VISUAL_SWATCH,
    ];

    /**
     * @return bool
     */
    public function hasStructuredValues(): bool
    {
        return in_array($this->getType(), self::TYPES_WITH_VALUES);
    }

    /**
     * Get attribute type
     *
     * @return string
     */
    public function getType()
    {
        if (isset($this->oxattribute__oxtype) && $this->oxattribute__oxtype->value !== null && $this->oxattribute__oxtype->value !== '') {
            return $this->oxattribute__oxtype->value;
        }

        // If not loaded (e.g. in list), fetch it
        $sOxtype = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->getOne(
            "SELECT OXTYPE FROM oxattribute WHERE OXID = " . \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($this->getId())
        );

        if ($sOxtype) {
            $this->oxattribute__oxtype = new \OxidEsales\Eshop\Core\Field($sOxtype);
            return $sOxtype;
        }

        return 'TEXT';
    }

    /**
     * Check if attribute is boolean
     *
     * @return bool
     */
    public function isBoolean()
    {
        return $this->getType() === 'BOOL';
    }

    /**
     * Get available values for this attribute
     *
     * @param int|null $iLang Language ID
     *
     * @return \OxidEsales\Eshop\Core\Model\ListModel
     */
    public function getAvailableValues($iLang = null)
    {
        if ($iLang === null) {
            $iLang = \OxidEsales\Eshop\Core\Registry::getLang()->getBaseLanguage();
        }

        $oList = oxNew(\OxidEsales\Eshop\Core\Model\ListModel::class);
        $oList->init(\Antigravity\AdvancedAttributes\Model\AttributeValue::class);
 //       $oList->init('antigravity_attributevalue');

        $sTable = 'oxattributevalues';
        $sAttrId = \OxidEsales\Eshop\Core\DatabaseProvider::getDb()->quote($this->getId());
        $iLang = (int)$iLang;

        $sQ = "SELECT * FROM $sTable WHERE OXATTRID = $sAttrId AND OXLANG = $iLang ORDER BY OXSORT, OXVALUE";

        $oList->selectString($sQ);

        return $oList;
    }
}