<?php
/**
 * @author     matthieu.napoli
 * @package    Core
 * @subpackage Test
 */

use DI\Container;

/**
 * @package    Core
 * @subpackage Test
 */
class Core_Test_Work_SimpleDispatcherTest extends PHPUnit_Framework_TestCase
{

    public function testRegisterWorker()
    {
        /** @var $dispatcher Core_Work_SimpleDispatcher */
        $dispatcher = $this->getMockForAbstractClass('Core_Work_SimpleDispatcher');

        $task = $this->getMockForAbstractClass('Core_Work_Task');

        $worker = $this->getMockForAbstractClass('Core_Work_Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));

        /** @var $worker Core_Work_Worker */
        $dispatcher->registerWorker($worker);

        // Call Core_Work_SimpleDispatcher::getWorker()
        $class = new ReflectionClass($dispatcher);
        $method = $class->getMethod('getWorker');
        $method->setAccessible(true);
        $fetchedWorker = $method->invoke($dispatcher, $task);

        $this->assertSame($worker, $fetchedWorker);
    }

    public function testRun()
    {
        $dispatcher = new Core_Work_SimpleDispatcher();

        /** @var $task Core_Work_Task */
        $task = $this->getMockForAbstractClass('Core_Work_Task');

        /** @var $worker PHPUnit_Framework_MockObject_MockObject */
        $worker = $this->getMockForAbstractClass('Core_Work_Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));
        $worker->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($task))
            ->will($this->returnValue('foo'));

        /** @var $worker Core_Work_Worker */
        $dispatcher->registerWorker($worker);

        $this->assertEquals('foo', $dispatcher->run($task));
    }

    public function testRunBackground()
    {
        $dispatcher = new Core_Work_SimpleDispatcher();

        /** @var $task Core_Work_Task */
        $task = $this->getMockForAbstractClass('Core_Work_Task');

        /** @var $worker PHPUnit_Framework_MockObject_MockObject */
        $worker = $this->getMockForAbstractClass('Core_Work_Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));
        $worker->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($task))
            ->will($this->returnValue('foo'));

        /** @var $worker Core_Work_Worker */
        $dispatcher->registerWorker($worker);

        $dispatcher->runBackground($task);
    }

    public function testRunServiceCall()
    {
        $dispatcher = new Core_Work_SimpleDispatcher();
        $dispatcher->registerWorker(new Core_Work_ServiceCall_Worker(new Container()));

        $task = new Core_Work_ServiceCall_Task('Inventory_Service_Test', 'doSomething', ['foo']);

        $this->assertEquals('foo', $dispatcher->run($task));
    }

}
