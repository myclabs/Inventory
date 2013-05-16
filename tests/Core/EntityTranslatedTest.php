<?php
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
class Core_Test_EntityTranslatedTest extends Core_Test_TestCase
{

    /**
     * Méthode appelée avant l'exécution des tests
     */
    public static function setUpBeforeClass()
    {
        // Vérification qu'il ne reste aucun Default_Model_Entity en base, sinon suppression !
        if (Default_Model_Translated::countTotal() > 0) {
            foreach (Default_Model_Translated::loadList() as $o) {
                $o->delete();
            }
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        }
    }


    public function testSimpleTranslation()
    {
        $french = Core_Locale::load('fr');
        $english = Core_Locale::load('en');

        $o = new Default_Model_Translated();
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

        $o = new Default_Model_Translated();
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

        $o = new Default_Model_Translated();
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

        $o = new Default_Model_Translated();

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

        $o1 = new Default_Model_Translated();
        $translationRepository->translate($o1, 'name', $french->getId(), 'A');
        $translationRepository->translate($o1, 'name', $english->getId(), 'B');
        $o1->save();

        $o2 = new Default_Model_Translated();
        $translationRepository->translate($o2, 'name', $french->getId(), 'B');
        $translationRepository->translate($o2, 'name', $english->getId(), 'A');
        $o2->save();

        $this->entityManager->flush();

        // Test default
        $query = new Core_Model_Query();
        $query->order->addOrder(Default_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Default_Model_Translated::loadList($query);

        $this->assertSame($o1, $list[0]);
        $this->assertSame($o2, $list[1]);

        // Test fr
        $query = new Core_Model_Query();
        $query->enableTranslations(true, $french);
        $query->order->addOrder(Default_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Default_Model_Translated::loadList($query);

        $this->assertSame($o1, $list[0]);
        $this->assertSame($o2, $list[1]);

        // Test en (liste inversée)
        $query = new Core_Model_Query();
        $query->enableTranslations(true, $english);
        $query->order->addOrder(Default_Model_Translated::QUERY_NAME, Core_Model_Order::ORDER_ASC);
        $list = Default_Model_Translated::loadList($query);

        $this->assertSame($o2, $list[0]);
        $this->assertSame($o1, $list[1]);

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

        $o1 = new Default_Model_Translated();
        $translationRepository->translate($o1, 'name', $french->getId(), 'A');
        $translationRepository->translate($o1, 'name', $english->getId(), 'B');
        $o1->save();

        $o2 = new Default_Model_Translated();
        $translationRepository->translate($o2, 'name', $french->getId(), 'B');
        $translationRepository->translate($o2, 'name', $english->getId(), 'A');
        $o2->save();

        $this->entityManager->flush();

        // Test default
        $query = new Core_Model_Query();
        $query->filter->addCondition(Default_Model_Translated::QUERY_NAME, 'A');
        $list = Default_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o1, $list[0]);

        // Test fr
        $query = new Core_Model_Query();
        $query->enableTranslations(true, $french);
        $query->filter->addCondition(Default_Model_Translated::QUERY_NAME, 'A');
        $list = Default_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o1, $list[0]);

        // Test en (liste inversée)
        $query = new Core_Model_Query();
        $query->enableTranslations(true, $english);
        $query->filter->addCondition(Default_Model_Translated::QUERY_NAME, 'A');
        $list = Default_Model_Translated::loadList($query);

        $this->assertCount(1, $list);
        $this->assertSame($o2, $list[0]);

        // Fixtures deletion
        $o1->delete();
        $o2->delete();
        $this->entityManager->flush();
    }

}
