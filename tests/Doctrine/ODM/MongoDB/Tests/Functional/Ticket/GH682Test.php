<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class GH682Test extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testIndex2D()
    {
        $class = $this->dm->getClassMetadata(__NAMESPACE__.'\GH682Document');
        $sm = $this->dm->getSchemaManager();
        $indexes = $sm->getDocumentIndexes($class->name);
        $this->assertSame(array('coord' => '2d'), $indexes[0]['keys']);
    }
}

/** 
 * @ODM\Document 
 * @ODM\Index(keys={"coord"="2d"})
 */
class GH682Document
{
    /** @ODM\Id */
    public $id;

    /**
     * @ODM\EmbedOne(targetDocument="GH682Coord") 
     */
    public $coord;

    /**
     * @ODM\ReferenceOne(targetDocument="GH682Document")
     * @var Document
     */
    public $parent;
}

/** @ODM\EmbeddedDocument */
class GH682Coord
{
    /** @ODM\Float */
    public $lat;
    
    /** @ODM\Float */
    public $lng;
}
