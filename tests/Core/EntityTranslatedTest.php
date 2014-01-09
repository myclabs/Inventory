<?php
use Core\Test\TestCase;

/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * Test des traductions de champs d'entités
 *
 * @package Core
 * @subpackage Event
 */
class Core_Test_EntityTranslatedTest extends TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Inventory_Model_Entity en base, sinon suppression !
        if (Inventory_Model_Translated::countTotal() > 0) {
            foreach (Inventory_Model_Translated::loadList() as $o) {
                $o->delete();
            }
            \Core\ContainerSingleton::getEntityManager()->flush();
        }
    }


    public function testSimpleTranslation()
    {
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        $o = new Inventory_Model_Translated();
        $o->setTranslationLocale($french);
        $o->setName('Bonjour');
        $o->save();
        $this->entityManager->flush($o);

        $o->setTranslationLocale($english);
        $o->setName('Hello');
        $this->entityManager->flush($o);

        $o->setTranslationLocale($french);
        $this->entityManager->refresh($o);
        $this->assertEquals('Bonjour', $o->getName());

        $o->setTranslationLocale($english);
        $this->entityManager->refresh($o);
        $this->assertEquals('Hello', $o->getName());

        $o->delete();
        $this->entityManager->flush();
    }

    public function testReloadWithLocale()
    {
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        $o = new Inventory_Model_Translated();
        $o->setTranslationLocale($french);
        $o->setName('Bonjour');
        $o->save();
        $this->entityManager->flush($o);

        $o->setTranslationLocale($english);
        $o->setName('Hello');
        $this->entityManager->flush($o);

        $o->reloadWithLocale($french);
        $this->assertEquals('Bonjour', $o->getName());

        $o->reloadWithLocale($english);
        $this->assertEquals('Hello', $o->getName());

        $o->delete();
        $this->entityManager->flush();
    }

    public function testDefaultLocale()
    {
        $english = Core_Locale::load('en');

        $o = new Inventory_Model_Translated();
        $o->setName('Bonjour');
        $o->save();
        $this->entityManager->flush($o);

        $o->setTranslationLocale($english);
        $o->setName('Hello');
        $this->entityManager->flush($o);

        $o->reloadWithLocale();
        $this->assertEquals('Bonjour', $o->getName());

        $o->reloadWithLocale($english);
        $this->assertEquals('Hello', $o->getName());

        $o->delete();
        $this->entityManager->flush();
    }

    public function testRepositoryTranslate()
    {
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $o = new Inventory_Model_Translated();

        $translationRepository->translate($o, 'name', $french->getId(), 'Bonjour');
        $translationRepository->translate($o, 'name', $english->getId(), 'Hello');

        $o->save();
        $this->entityManager->flush($o);

        $o->reloadWithLocale($french);
        $this->assertEquals('Bonjour', $o->getName());

        $o->reloadWithLocale($english);
        $this->assertEquals('Hello', $o->getName());

        $o->delete();
        $this->entityManager->flush();
    }

    public function testQueryOrder()
    {
        // Fixtures
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $o1 = new Inventory_Model_Translated();
        $translationRepository->translate($o1, 'name', $french->getId(), 'A');
        $translationRepository->translate($o1, 'name', $english->getId(), 'B');
        $o1->save();

        $o2 = new Inventory_Model_Translated();
        $translationRepository->translate($o2, 'name', $french->getId(), 'B');
        $translationRepository->translate($o2, 'name', $english->getId(), 'A');
        $o2->save();

        $this->entityManager->flush();

        // Test default
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertSame($o1, $list[0]);
        $this->assertSame($o2, $list[1]);

        // Test en (liste inversée)
        Core_Locale::setDefault($english);
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertSame($o2, $list[0]);
        $this->assertSame($o1, $list[1]);

        // Test fr
        Core_Locale::setDefault($french);
        $query = new Core_Model_Query();
        $query->order->addOrder(Inventory_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertSame($o1, $list[0]);
        $this->assertSame($o2, $list[1]);

        // Fixtures deletion
        $o1->delete();
        $o2->delete();
        $this->entityManager->flush();
    }

    public function testQueryFilter()
    {
        // Fixtures
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        /** @var $translationRepository \Gedmo\Translatable\Entity\Repository\TranslationRepository */
        $translationRepository = $this->entityManager->getRepository('Gedmo\Translatable\Entity\Translation');

        $o1 = new Inventory_Model_Translated();
        $translationRepository->translate($o1, 'name', $french->getId(), 'A');
        $translationRepository->translate($o1, 'name', $english->getId(), 'B');
        $o1->save();

        $o2 = new Inventory_Model_Translated();
        $translationRepository->translate($o2, 'name', $french->getId(), 'B');
        $translationRepository->translate($o2, 'name', $english->getId(), 'A');
        $o2->save();

        $this->entityManager->flush();

        // Test default
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Translated::QUERY_NAME, 'A');
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o1, $list[0]);

        // Test en (liste inversée)
        Core_Locale::setDefault($english);
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Translated::QUERY_NAME, 'A');
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o2, $list[0]);

        // Test fr
        Core_Locale::setDefault($french);
        $query = new Core_Model_Query();
        $query->filter->addCondition(Inventory_Model_Translated::QUERY_NAME, 'A');
        $list = Inventory_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o1, $list[0]);

        // Fixtures deletion
        $o1->delete();
        $o2->delete();
        $this->entityManager->flush();
    }

}
