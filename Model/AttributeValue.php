<?php

namespace Antigravity\AdvancedAttributes\Model;

use OxidEsales\Eshop\Core\Model\BaseModel;

/**
 * Model class for oxattributevalues logic
 */
class AttributeValue extends BaseModel
{
    /**
     * Core table name
     *
     * @var string
     */
    protected $_sCoreTable = 'oxattributevalues';
    protected $_sClassName = 'antigravity_attributevalue';

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->init('oxattributevalues');
    }
}