<?php

namespace Doctrine\ODM\MongoDB\Tests;

use Doctrine\ODM\MongoDB\Dehydrator\DefaultDehydrator;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class DehydratorTest extends BaseTest
{
    public function testDehydrator()
    {
        $dehydrator = new DefaultDehydrator($this->dm);
        $data = [
            '_id' => new \MongoId(),
            'name' => 'maciej',
            'birthdate' => new \MongoDate(strtotime('1961-01-01')),
            'referenceOne' => new \MongoId(),
            'referenceMany' => [
                new \MongoId(),
                new \MongoId(),
            ],
            'embedOne' => ['name' => 'maciej'],
            'embedMany' => [
                ['name' => 'maciej']
            ]
        ];
        $user = new DehydrationUser();
        $this->dm->getHydratorFactory()->hydrate($user, $data);
        $this->assertEquals($data, $dehydrator->dehydrate($user));
    }
}

/** @ODM\Document */
class DehydrationUser
{
    /** @ODM\Id */
    public $id;

    /** @ODM\Field(type="string") */
    public $name;

    /** @ODM\Field(type="date") */
    public $birthdate;

    /** @ODM\ReferenceOne(targetDocument="DehydrationDocumentReference", storeAs="id") */
    public $referenceOne;

    /** @ODM\ReferenceMany(targetDocument="DehydrationDocumentReference", storeAs="id")) */
    public $referenceMany = array();

    /** @ODM\EmbedOne(targetDocument="DehydrationDocumentEmbed") */
    public $embedOne;

    /** @ODM\EmbedMany(targetDocument="DehydrationDocumentEmbed") */
    public $embedMany = array();
}

/** @ODM\Document */
class DehydrationDocumentReference
{
    /** @ODM\Id */
    public $id;

    /** @ODM\Field(type="string") */
    public $name;
}

/** @ODM\EmbeddedDocument */
class DehydrationDocumentEmbed
{
    /** @ODM\Field(type="string") */
    public $name;
}
