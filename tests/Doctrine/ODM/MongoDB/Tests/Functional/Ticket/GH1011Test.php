<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class GH1011Test extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testSchedulingCollections()
    {
        $doc = new GH1011Documnet();
        $doc->embeds->add(new GH1011Embedded('test1'));
        $this->dm->persist($doc);
        $this->dm->flush();
        $this->dm->clear();
        $test = $this->dm->getRepository(get_class($doc))->find($doc->id);
        $test->embeds->add(new GH1011Embedded('test2'));
        $this->uow->computeChangeSets();
        $this->assertTrue($this->uow->isCollectionScheduledForUpdate($test->embeds));
        $this->assertFalse($this->uow->isCollectionScheduledForDeletion($test->embeds));
    }
}

/** @ODM\Document */
class GH1011Documnet
{
    /** @ODM\Id */
    public $id;

    /** @ODM\EmbedMany(targetDocument="GH1011Embedded", strategy="set") */
    public $embeds;

    public function __construct()
    {
        $this->embeds = new ArrayCollection();
    }
}

/** @ODM\EmbeddedDocument */
class GH1011Embedded
{
    /** @ODM\String */
    public $name;
    
    public function __construct($name)
    {
        $this->name = $name;
    }
}
