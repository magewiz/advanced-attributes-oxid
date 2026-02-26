<?php

namespace Antigravity\AdvancedAttributes\Model;

use OxidEsales\Eshop\Core\TableViewNameGenerator;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Application\Model\AttributeList as AttributeListModel;

/**
 * Extension of AttributeList model to ensure advanced attribute fields are loaded.
 */
class AttributeList extends AttributeListModel
{
    /**
     * Load attributes by article Id. Overridden to include OXTYPE.
     *
     * @param string $sArticleId article id
     * @param string $sParentId  article parent id
     */
    public function loadAttributes($sArticleId, $sParentId = null)
    {
        if ($sArticleId) {
            $oDb = DatabaseProvider::getDb(DatabaseProvider::FETCH_MODE_ASSOC);

            $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
            $sAttrViewName = $tableViewNameGenerator->getViewName('oxattribute');
            $sViewName = $tableViewNameGenerator->getViewName('oxobject2attribute');

            // Added {$sAttrViewName}.`oxtype` to the selection
            $sSelect = "select {$sAttrViewName}.`oxid`, {$sAttrViewName}.`oxtitle`, {$sAttrViewName}.`oxtype`, o2a.`oxvalue` from {$sViewName} as o2a ";
            $sSelect .= "left join {$sAttrViewName} on {$sAttrViewName}.oxid = o2a.oxattrid ";
            $sSelect .= "where o2a.oxobjectid = :oxobjectid and o2a.oxvalue != '' ";
            $sSelect .= "order by o2a.oxpos, {$sAttrViewName}.oxpos";

            $aAttributes = $oDb->getAll($sSelect, [
                ':oxobjectid' => $sArticleId
            ]);

            if ($sParentId) {
                $aParentAttributes = $oDb->getAll($sSelect, [
                    ':oxobjectid' => $sParentId
                ]);
                $aAttributes = $this->mergeAttributes($aAttributes, $aParentAttributes);
            }

            $this->assignArray($aAttributes);
        }
    }

    /**
     * Load all attributes by article Id's. Overridden to include OXTYPE.
     *
     * @param array $aIds article id's
     *
     * @return array $aAttributes;
     */
    public function loadAttributesByIds($aIds)
    {
        if (!count($aIds)) {
            return;
        }

        $tableViewNameGenerator = oxNew(TableViewNameGenerator::class);
        $sAttrViewName = $tableViewNameGenerator->getViewName('oxattribute');
        $sViewName = $tableViewNameGenerator->getViewName('oxobject2attribute');

        $oxObjectIdsSql = implode(',', DatabaseProvider::getDb()->quoteArray($aIds));

        // Added {$sAttrViewName}.oxtype
        $sSelect = "select $sAttrViewName.oxid, $sAttrViewName.oxtitle, {$sViewName}.oxvalue, {$sViewName}.oxobjectid, $sAttrViewName.oxtype ";
        $sSelect .= "from {$sViewName} left join $sAttrViewName on $sAttrViewName.oxid = {$sViewName}.oxattrid ";
        $sSelect .= "where {$sViewName}.oxobjectid in ( " . $oxObjectIdsSql . " ) ";
        $sSelect .= "order by {$sViewName}.oxpos, $sAttrViewName.oxpos";

        return $this->createAttributeListFromSql($sSelect);
    }

    /**
     * Fills array with keys and products with value. Overridden to handle oxtype.
     *
     * @param string $sSelect SQL select
     *
     * @return array $aAttributes
     */
    protected function createAttributeListFromSql($sSelect)
    {
        $aAttributes = [];
        $rs = DatabaseProvider::getDb()->select($sSelect);
        if ($rs != false && $rs->count() > 0) {
            while (!$rs->EOF) {
                if (!isset($aAttributes[$rs->fields[0]])) {
                    $aAttributes[$rs->fields[0]] = new \stdClass();
                }

                $aAttributes[$rs->fields[0]]->title = $rs->fields[1];
                $aAttributes[$rs->fields[0]]->oxtype = $rs->fields[4] ?? 'TEXT'; // Field 4 is oxtype
                
                if (!isset($aAttributes[$rs->fields[0]]->aProd[$rs->fields[3]])) {
                    $aAttributes[$rs->fields[0]]->aProd[$rs->fields[3]] = new \stdClass();
                }
                $aAttributes[$rs->fields[0]]->aProd[$rs->fields[3]]->value = $rs->fields[2];
                $rs->fetchRow();
            }
        }

        return $aAttributes;
    }
}
