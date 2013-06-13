<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

use Gedmo\Loggable\Entity\LogEntry;

/**
 * Test des versions de champs d'entités
 *
 * @package Core
 * @subpackage Event
 */
class Core_Test_EntityVersionedTest extends Core_Test_TestCase
{

    public function setUp()
    {
        parent::setUp();

        /** @var Gedmo\Loggable\LoggableListener $loggableListener */
        $loggableListener = $this->get('Gedmo\Loggable\LoggableListener');
        $loggableListener->setUsername('foo');
    }


    public function testRepositoryTranslate()
    {
        /** @var $repository \Gedmo\Loggable\Entity\Repository\LogEntryRepository */
        $repository = $this->entityManager->getRepository('Gedmo\Loggable\Entity\LogEntry');

        $o = new Inventory_Model_Versioned();
        $o->setName('foo');
        $o->save();
        $this->entityManager->flush();

        $o->setName('bar');
        $o->save();
        $this->entityManager->flush();

        /** @var LogEntry[] $versions */
        $versions = $repository->getLogEntries($o);

        $this->assertCount(2, $versions);

        $names = [
            1 => 'foo',
            2 => 'bar',
        ];

        foreach ($versions as $version) {
            $this->assertEquals('foo', $version->getUsername());
            $repository->revert($o, $version->getVersion());
            $this->assertEquals($names[$version->getVersion()], $o->getName());
        }

        $o->delete();
        $this->entityManager->flush();
    }

}
