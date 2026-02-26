<?php

namespace Antigravity\AdvancedAttributes\Component\Widget;

use stdClass;

/**
 * Extension of ArticleDetails widget to include advanced attribute data.
 */
class ArticleDetails extends ArticleDetails_parent
{
    /**
     * Overridden to include advanced attribute fields (like type and color) in the view data.
     *
     * @return array
     */
    public function getAttributes()
    {
        if ($this->_aAttributes === null) {
            $this->_aAttributes = [];
            
            // Get attributes from the product (which is already extended via our Model\Article extension if active)
            $aArtAttributes = $this->getProduct()->getAttributes();

            foreach ($aArtAttributes as $sKey => $oAttribute) {
                // Instead of creating a new stdClass and manually copying fields, 
                // we should pass the attribute object itself or a comprehensive clone/wrapper.
                // However, existing templates might expect specific structure.
                // The cleanest way is to pass the object but ensure our extended fields are accessible.
                
                // Let's clone it to be safe and ensure we don't modify the original reference if it's used elsewhere
                $oAttr = clone $oAttribute;

                // Ensure title and value are accessible as top-level properties if the template expects them
                // (Standard OXID attributes usually have oxattribute__oxtitle and oxattribute__oxvalue)
                $oAttr->title = $oAttribute->oxattribute__oxtitle->value;
                $oAttr->value = $oAttribute->oxattribute__oxvalue->value;
                
                // Ensure extended fields are present even if empty
                if (!isset($oAttr->oxattribute__oxtype)) {
                     $oAttr->oxattribute__oxtype = new \OxidEsales\Eshop\Core\Field('');
                }
                if (!isset($oAttr->oxattribute__oxcolor)) {
                     $oAttr->oxattribute__oxcolor = new \OxidEsales\Eshop\Core\Field('');
                }
                 if (!isset($oAttr->oxattribute__oximage)) {
                     $oAttr->oxattribute__oximage = new \OxidEsales\Eshop\Core\Field('');
                }


                $this->_aAttributes[$sKey] = $oAttr;
            }

        }

        return $this->_aAttributes;
    }
}
