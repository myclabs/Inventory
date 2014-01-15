<?php

namespace Tests\Techno;

use Core\Test\TestCase;
use Doctrine\ORM\UnitOfWork;
use Techno\Domain\Category;

class CategoryTest extends TestCase
{
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun objet en base, sinon suppression
        foreach (Category::loadList() as $o) {
            $o->delete();
        }
        self::getEntityManager()->flush();
    }

    public function testConstruct()
    {
        $o = new Category("Test");
        $o->save();
        $this->entityManager->flush();

        $this->assertEquals("Test", $o->getLabel());
        return $o;
    }

    /**
     * @depends testConstruct
     * @param Category $o
     * @return Category
     */
    public function testLoad($o)
    {
        $this->entityManager->clear('Techno\Domain\Category');
        /** @var $oLoaded Category */
        $oLoaded = Category::load($o->getId());

        $this->assertInstanceOf('Techno\Domain\Category', $oLoaded);
        $this->assertNotSame($o, $oLoaded);
        $this->assertEquals($o->getKey(), $oLoaded->getKey());
        $this->assertEquals($o->getLabel(), $oLoaded->getLabel());
        return $oLoaded;
    }

    /**
     * @depends testLoad
     * @param Category $o
     */
    public function testDelete($o)
    {
        $o->delete();
        $this->assertEquals(UnitOfWork::STATE_REMOVED, $this->entityManager->getUnitOfWork()->getEntityState($o));
        $this->entityManager->flush();
        $this->assertEquals(UnitOfWork::STATE_NEW, $this->entityManager->getUnitOfWork()->getEntityState($o));
    }
}
