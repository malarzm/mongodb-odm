<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class GH1050Test extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function tearDown()
    {
        parent::tearDown();
    }
    
    public function testRepositoryClassIsCorrect()
    {
        $r = $this->dm->getRepository(__NAMESPACE__ . '\\GH1050AbstractDocument');
        $this->assertInstanceOf(__NAMESPACE__ . '\\GH1050Repository', $r);
    }
    
    public function testRepositoryFindAll()
    {
        $d1 = new GH1050Document1();
        $d1->title = "a";
        $this->dm->persist($d1);
        $d2 = new GH1050Document2();
        $d2->title = "z";
        $this->dm->persist($d2);
        $this->dm->flush();
        $this->dm->clear();
        $r = $this->dm->getRepository(__NAMESPACE__ . '\\GH1050AbstractDocument');
        $results = $r->findAll();
        $this->assertCount(2, $results);
    }
}

/**
 * @ODM\MappedSuperclass(repositoryClass="GH1050Repository")
 * @ODM\InheritanceType("SINGLE_COLLECTION")
 * @ODM\DiscriminatorField(fieldName="type")
 * @ODM\DiscriminatorMap({
 *      "d1" = "GH1050Document1",
 *      "d2" = "GH1050Document2"
 * })
 */
abstract class GH1050AbstractDocument
{
    /** @ODM\Id */
    public $id;
}

/** @ODM\Document(collection="d1") */
class GH1050Document1 extends GH1050AbstractDocument
{
    /** @ODM\String */
    public $title;
}

/** @ODM\Document(collection="d1") */
class GH1050Document2 extends GH1050AbstractDocument
{
    /** @ODM\String */
    public $title;
}

class GH1050Repository extends \Doctrine\ODM\MongoDB\DocumentRepository
{
    
}
