<?php

require_once '../vendor/autoload.php';

use Doctrine\MongoDB\Connection;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

AnnotationDriver::registerAnnotationClasses();

class Test
{
    const COUNT = 2;
    const ITERATIONS = 2;

    /**
     * @var DocumentManager
     */
    protected $dm;
    protected $inc = 1;

    public function test1()
    {
        $docs = [];
        for ($i = 0; $i < self::COUNT; $i++) {
            $doc = $this->createDoc();
            $this->dm->persist($doc);
            $docs[] = $doc;
        }
        $this->dm->flush();
        foreach ($docs as $doc) {
            $this->dm->detach($doc);
        }
    }

    public function test2()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $doc = $this->createDoc();
            $this->dm->persist($doc);
        }
        $this->dm->flush();
        $this->dm->clear('Doc');
    }

    public function test3()
    {
        for ($i = 0; $i < self::COUNT; $i++) {
            $doc = $this->createDoc();
            $this->dm->persist($doc);
        }
        $this->dm->flush();
        $this->dm->clear();
    }

    protected function clear()
    {
        $config = new Configuration();
        $config->setProxyDir('/tmp/test');
        $config->setProxyNamespace('Proxies');
        $config->setHydratorDir('/tmp/test');
        $config->setHydratorNamespace('Hydrators');
        $config->setMetadataDriverImpl(AnnotationDriver::create(__DIR__));

        if ($this->dm) {
            $this->dm->close();
        }
        $this->dm = DocumentManager::create(new Connection(), $config);
        $this->dm->getDocumentCollection('Doc')->remove([]);
        $this->inc = 1;
    }

    public function run()
    {
        echo "Test1\n";
        $this->clear();
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $this->test1();
            $this->mem();
        }

        echo "\n\nTest2\n";
        $this->clear();
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $this->test2();
            $this->mem();
        }

        echo "\n\nTest3\n";
        $this->clear();
        for ($i = 0; $i < self::ITERATIONS; $i++) {
            $this->test3();
            $this->mem();
        }
    }

    private function createDoc()
    {
        $doc = new Doc();
        //$doc->id = $this->inc++;
        $doc->name = str_repeat(md5($this->inc), 40);
        return $doc;
    }

    private function mem()
    {
        echo sprintf("%.2fM\n", memory_get_usage() / 1024 / 1024);
    }
}

/**
 * @ODM\Document
 */
class Doc
{
     /** @ODM\Id */
    public $id;
    
    /** @ODM\String */
    public $name;
}

$test = new Test();
$test->run();
