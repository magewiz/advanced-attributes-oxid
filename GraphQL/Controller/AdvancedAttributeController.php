<?php

declare(strict_types=1);

namespace Antigravity\AdvancedAttributes\GraphQL\Controller;

use Antigravity\AdvancedAttributes\Model\Attribute;
use Antigravity\AdvancedAttributes\Model\AttributeList;
use Antigravity\AdvancedAttributes\GraphQL\Input\AdvancedAttributeInput;
use Antigravity\AdvancedAttributes\GraphQL\Input\AdvancedAttributeValueInput;
use TheCodingMachine\GraphQLite\Annotations\Query;
use TheCodingMachine\GraphQLite\Annotations\Mutation;
use OxidEsales\Eshop\Application\Model\Article;
use OxidEsales\Eshop\Core\DatabaseProvider;
use OxidEsales\Eshop\Core\Model\BaseModel;
use OxidEsales\Eshop\Core\Model\ListModel;

final class AdvancedAttributeController
{
    #[Query]
    public function advancedAttribute(string $id): ?Attribute
    {
        $attribute = oxNew(Attribute::class);
        if ($attribute->load($id)) {
            return $attribute;
        }
        return null;
    }

    /**
     * @return Attribute[]
     */
    #[Query]
    public function advancedAttributes(): array
    {
        $list = oxNew(AttributeList::class);
        $list->selectString('SELECT * FROM oxattribute');
        return $list->getArray();
    }

    #[Mutation]
    public function createAdvancedAttribute(AdvancedAttributeInput $input): Attribute
    {
        $attribute = oxNew(Attribute::class);
        $attribute->setTitle($input->getTitle());
        $attribute->assign([
            'oxtitle' => $input->getTitle(),
            'oxtype' => $input->getType()
        ]);
        $attribute->save();

        return $attribute;
    }

    #[Mutation]
    public function updateAdvancedAttribute(string $id, AdvancedAttributeInput $input): ?Attribute
    {
        $attribute = oxNew(Attribute::class);
        if (!$attribute->load($id)) {
            return null;
        }
        $attribute->setTitle($input->getTitle());
        $attribute->assign([
            'oxtitle' => $input->getTitle(),
            'oxtype' => $input->getType()
        ]);
        $attribute->save();

        return $attribute;
    }

    #[Mutation]
    public function deleteAdvancedAttribute(string $id): bool
    {
        $attribute = oxNew(Attribute::class);
        if ($attribute->load($id)) {
            return (bool) $attribute->delete();
        }
        return false;
    }

    #[Mutation]
    public function assignAdvancedAttributeValue(string $articleId, string $attributeId, string $value): bool
    {
        // Check if article exists
        $article = oxNew(Article::class);
        if (!$article->exists($articleId)) {
             return false;
        }

        // Use oxbase to handle oxobject2attribute table interaction
        // Check if assignment already exists
        $db = DatabaseProvider::getDb();
        $oxid = $db->getOne("SELECT OXID FROM oxobject2attribute WHERE OXOBJECTID = ? AND OXATTRID = ?", [$articleId, $attributeId]);

        $assignment = oxNew(BaseModel::class);
        $assignment->init('oxobject2attribute');

        if ($oxid) {
            $assignment->load($oxid);
        } else {
            $assignment->assign([
                'oxobjectid' => $articleId,
                'oxattrid' => $attributeId
            ]);
        }
        
        $assignment->assign(['oxvalue' => $value]);
        return (bool) $assignment->save();
    }

    /**
     * @return \Antigravity\AdvancedAttributes\Model\AttributeValue[]
     */
    #[Query]
    public function advancedAttributeValues(string $attributeId): array
    {
        $list = oxNew(ListModel::class);
        $list->init('antigravity_attributevalue');
        $list->selectString("SELECT * FROM oxattributevalues WHERE oxattrid = '" . $attributeId . "' ORDER BY oxsort");
        return $list->getArray();
    }

    #[Mutation]
    public function createAdvancedAttributeValue(string $attributeId, AdvancedAttributeValueInput $input): \Antigravity\AdvancedAttributes\Model\AttributeValue
    {
        $value = oxNew(\Antigravity\AdvancedAttributes\Model\AttributeValue::class);
        $value->assign([
            'oxattrid' => $attributeId,
            'oxvalue' => $input->getValue()
        ]);
        $value->save();
        return $value;
    }
}
