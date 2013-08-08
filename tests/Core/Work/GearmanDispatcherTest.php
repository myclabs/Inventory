<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

/**
 * @package    Core
 * @subpackage Test
 */
class Core_Test_Work_GearmanDispatcherTest extends PHPUnit_Framework_TestCase
{

    public function testRunServiceCall()
    {
        if (!extension_loaded('gearman')) {
            $this->markTestSkipped('Extension Gearman non installée');
        }
        $dispatcher = new Core_Work_GearmanDispatcher();
        $dispatcher->registerWorker(new Core_Work_ServiceCall_Worker());

        $task = new Core_Work_ServiceCall_Task('Inventory_Service_Test', 'doSomething', ['foo']);

        $this->assertEquals('foo', $dispatcher->run($task));
    }

}
