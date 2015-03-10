<?php

namespace Doctrine\ODM\MongoDB\Tests;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class ClearAndPersistTest extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testClearAndPersist()
    {
        $d1 = new CAPDocument();
        $d1->text = "Test";
        $sd1 = new CAPEmbedded();
        $sd1->text = "Embedded no 1";
        $sd2 = new CAPEmbedded();
        $sd2->text = "Embedded no 2";
        $sd3 = new CAPEmbedded();
        $sd3->text = "Just Embedded";
        $d1->embedOne = $sd3;
        $d1->data = array($sd1, $sd2);
        $this->dm->persist($d1);
        $this->dm->flush();
        $this->dm->clear();
        $d1->text = "Changed";
        $d1->embedOne->text = "Changed";
        $d1->data->first()->text = "Changed";
        $this->dm->persist($d1);
        $this->dm->flush();
        $this->dm->clear();
        $r = $this->dm->getRepository('Doctrine\ODM\MongoDB\Tests\CAPDocument');
        $o = $r->findOneByText('Changed');
        $this->assertSame($o->text, 'Changed');
        $this->assertSame($o->embedOne->text, 'Changed');
        $this->assertSame($o->data->first()->text, 'Changed'); // this one fails
    }
}

/**
 * @ODM\Document
 */
class CAPDocument
{
    /** @ODM\Id(strategy="auto") */
    private $id;

    /** @ODM\String */
    public $text;

    /** @ODM\EmbedOne */
    public $embedOne;

    /** @ODM\EmbedMany */
    public $data;
}

/**
 * @ODM\EmbeddedDocument
 */
class CAPEmbedded
{
    /** @ODM\String */
    public $text;
}
