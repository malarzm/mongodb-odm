<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Tests\BaseTest;

class ManualComputeChangeSetTest extends BaseTest
{
    public function testAutoRecompute()
    {
        $d = new Document();
        $d->value1 = "Value 1";
        $this->dm->persist($d);
        $this->dm->flush();
        $this->dm->clear();
        $d = $this->dm->getRepository(get_class($d))->findOneByValue1("Value 1");
        $d->value1 = "Changed";
        $this->uow->computeChangeSet($this->dm->getClassMetadata(get_class($d)), $d);
        $changeSet = $this->uow->getDocumentChangeSet($d);
        if (isset($changeSet['value1'])) {
            $d->value2 = "v1 has changed";
        }
        // with next line uncommented test passes
        // $this->uow->recomputeSingleDocumentChangeSet($this->dm->getClassMetadata(get_class($d)), $d);
        $this->dm->flush();
        $this->dm->clear();
        $d = $this->dm->getRepository(get_class($d))->findOneByValue1("Changed");
        $this->assertNotNull($d);
    }
}

/** @ODM\Document */
class Document
{
    /** @ODM\Id */
    public $id;
    
    /** @ODM\String */
    public $value1;
    
    /** @ODM\String */
    public $value2;
}
