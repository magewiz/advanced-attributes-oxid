<?php

$sMetadataVersion = '2.1';

$aModule = [
    'id' => 'advanced_attributes',
    'title' => 'Advanced Attributes',
    'description' => 'Extends OXID attribute system with structured types: Text, Selection, Multi-Select, Yes/No, Date, Date & Time, Price, Color, Image, Text Swatch, Visual Swatch.',
    'thumbnail' => '',
    'version' => '1.1.0',
    'author' => 'Antigravity',
    'extend' => [
        \OxidEsales\Eshop\Application\Model\Attribute::class => \Antigravity\AdvancedAttributes\Model\Attribute::class ,
        \OxidEsales\Eshop\Application\Model\Article::class => \Antigravity\AdvancedAttributes\Model\Article::class ,
        \OxidEsales\Eshop\Application\Model\AttributeList::class => \Antigravity\AdvancedAttributes\Model\AttributeList::class,
        \OxidEsales\Eshop\Application\Controller\Admin\ArticleAttributeAjax::class => \Antigravity\AdvancedAttributes\Controller\Admin\ArticleAttributeAjax::class ,
        \OxidEsales\Eshop\Application\Component\Widget\ArticleDetails::class => \Antigravity\AdvancedAttributes\Component\Widget\ArticleDetails::class ,
    ],
    'controllers' => [
         'attribute_value' => \Antigravity\AdvancedAttributes\Controller\Admin\AttributeValue::class ,
       'tbclattribute_value' => \Antigravity\AdvancedAttributes\Controller\Admin\AttributeValue::class ,

    ],
    'templates' => [
        'advanced_attribute_value.html.twig' => 'views/twig/admin/tpl/advanced_attribute_value.html.twig',
        'popups/article_attribute.html.twig' => 'views/twig/admin/tpl/popups/article_attribute.html.twig',
        'page/details/inc/attributes.html.twig' => 'views/twig/frontend/tpl/page/details/inc/attributes.html.twig',
    ],
    'events' => [
        'onActivate' => '\Antigravity\AdvancedAttributes\Core\Events::onActivate',
    ],
];