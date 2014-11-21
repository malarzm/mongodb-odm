<?php

namespace Doctrine\ODM\MongoDB\Tests;

use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class GH809Test extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    public function testUpdateSingle()
    {
        $q = $this->dm->createQueryBuilder('Doctrine\ODM\MongoDB\Tests\GH809Document')
                ->update()
                ->field('data.title')->set('Test')
                ->field('id')->equals('foo')
                ->getQuery()
                ->debug();
        $this->assertSame($q, array(
            'type' => \Doctrine\MongoDB\Query\Query::TYPE_UPDATE,
            'query' => array('_id' => 'foo'),
            'newObj' => array(
                '$set' => array('data.title' => 'Test')
            )
        ));
    }
    
    public function testUpdateMultiple()
    {
        $q = $this->dm->createQueryBuilder('Doctrine\ODM\MongoDB\Tests\GH809Document')
                ->update()
                ->multiple(true)
                ->field('data.title')->set('Test')
                ->field('id')->equals('foo')
                ->getQuery()
                ->debug();
        $this->assertSame($q, array(
            'type' => \Doctrine\MongoDB\Query\Query::TYPE_UPDATE,
            'multiple' => true,
            'query' => array('_id' => 'foo'),
            'newObj' => array(
                '$set' => array('data.title' => 'Test')
            )
        ));
    }
}

/**
 * @ODM\Document
 */
class GH809Document
{
    /** @ODM\Id(strategy="none") */
    public $id;
    
    /** @ODM\Hash */
    public $data;
}