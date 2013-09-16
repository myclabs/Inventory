<?php

use Core\Work\ServiceCall\ServiceCallTask;
use Core\Work\ServiceCall\ServiceCallWorker;
use DI\Container;

class Core_Test_Work_GearmanDispatcherTest extends Core_Test_TestCase
{
    public function testRunServiceCall()
    {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Extension Gearman non installée');
        }

        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $dispatcher = $this->get('Core\Work\Dispatcher\GearmanWorkDispatcher');
        $dispatcher->registerWorker(new ServiceCallWorker(new Container(), $logger));

        $oldDefaultLocale = Core_Locale::loadDefault();
        $locale = Core_Locale::load('en');
        Core_Locale::setDefault($locale);

        $task = new ServiceCallTask('Inventory_Service_Test', 'doSomething', ['foo']);

        $result = $dispatcher->run($task);

        $this->assertInternalType('array', $result);
        $this->assertEquals('foo', $result['value']);
        $this->assertEquals($locale->getId(), $result['locale']);

        // Restaure la locale par défaut
        Core_Locale::setDefault($oldDefaultLocale);
    }
}
