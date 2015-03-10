<?php

namespace Doctrine\ODM\MongoDB\Tests\Functional\Ticket;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Events;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;

class GH1039Test extends \Doctrine\ODM\MongoDB\Tests\BaseTest
{
    /** @var Article */
    private $article;
    
    public function setUp()
    {
        parent::setUp();
        $this->article = new Article('Test Article');
        $this->article->addTag(new Tag('test 1'));
        $this->article->addTag(new Tag('test 2'));
        $this->dm->persist($this->article);
        $this->dm->flush();
    }
    
    public function testonlyRemoveTagByName()
    {
        // add onFlush listener
        $em = $this->dm->getEventManager();
        $listener = $this->getMockBuilder('EventListener')
                ->setMethods(array('onFlush'))
                ->getMock();
        $listener->expects($this->once())->method('onFlush');
        $em->addEventListener(Events::onFlush, $listener);
        
        $this->assertEquals(2, $this->article->getTags()->count());
        $this->article->removeTagByName('test 2');
        $this->dm->flush();
        $this->dm->clear();
        $article = $this->dm->getRepository(get_class($this->article))
                ->findOneByTitle('Test Article');
        $this->assertEquals(1, $article->getTags()->count());
    }
}

/**
 * @ODM\Document(collection="articles")
 */
class Article
{
    /**
     * @ODM\Id
     */
    private $id;
    
    /**
     * @ODM\String
     */
    private $title;

    /**
     * @ODM\EmbedMany(targetDocument="Tag")
     */
    private $tags;

    public function __construct($title)
    {
        $this->title = $title;
        $this->tags = new ArrayCollection();
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        return $this->title;
    }
    
    public function getTags()
    {
        return $this->tags;
    }

    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
    }

    public function removeTagByName($tagName)
    {
        foreach ($this->tags as $idx => $tag) {
            if ($tagName === $tag->getName()) {
                unset($this->tags[$idx]);

                break;
            }
        }
    }
}

/**
 * @ODM\EmbeddedDocument
 */
class Tag
{
    /**
     * @ODM\String
     */
    private $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->name;
    }
}
