<?php

use Core\Work\ServiceCall\ServiceCallTask;
use Core\Work\Worker;
use Core\Work\Task;
use Core\Work\Dispatcher\SimpleWorkDispatcher;
use Core\Work\ServiceCall\ServiceCallWorker;
use DI\Container;

class Core_Test_Work_SimpleDispatcherTest extends PHPUnit_Framework_TestCase
{

    public function testRegisterWorker()
    {
        /** @var $dispatcher SimpleWorkDispatcher */
        $dispatcher = $this->getMockForAbstractClass('Core\Work\Dispatcher\SimpleWorkDispatcher');

        $task = $this->getMockForAbstractClass('Core\Work\Task');

        $worker = $this->getMockForAbstractClass('Core\Work\Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));

        /** @var $worker Worker */
        $dispatcher->registerWorker($worker);

        // Call SimpleWorkDispatcher::getWorker()
        $class = new ReflectionClass($dispatcher);
        $method = $class->getMethod('getWorker');
        $method->setAccessible(true);
        $fetchedWorker = $method->invoke($dispatcher, $task);

        $this->assertSame($worker, $fetchedWorker);
    }

    public function testRun()
    {
        $dispatcher = new SimpleWorkDispatcher();

        /** @var $task Task */
        $task = $this->getMockForAbstractClass('Core\Work\Task');

        /** @var $worker PHPUnit_Framework_MockObject_MockObject */
        $worker = $this->getMockForAbstractClass('Core\Work\Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));
        $worker->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($task))
            ->will($this->returnValue('foo'));

        /** @var $worker Worker */
        $dispatcher->registerWorker($worker);

        $this->assertEquals('foo', $dispatcher->run($task));
    }

    public function testRunBackground()
    {
        $dispatcher = new SimpleWorkDispatcher();

        /** @var $task Task */
        $task = $this->getMockForAbstractClass('Core\Work\Task');

        /** @var $worker PHPUnit_Framework_MockObject_MockObject */
        $worker = $this->getMockForAbstractClass('Core\Work\Worker');
        $worker->expects($this->once())
            ->method('getTaskType')
            ->will($this->returnValue(get_class($task)));
        $worker->expects($this->once())
            ->method('execute')
            ->with($this->equalTo($task))
            ->will($this->returnValue('foo'));

        /** @var $worker Worker */
        $dispatcher->registerWorker($worker);

        $dispatcher->runBackground($task);
    }

    public function testRunServiceCall()
    {
        /** @var \Psr\Log\LoggerInterface $logger */
        $logger = $this->getMockForAbstractClass('Psr\Log\LoggerInterface');

        $dispatcher = new SimpleWorkDispatcher();
        $dispatcher->registerWorker(new ServiceCallWorker(new Container(), $logger));

        $task = new ServiceCallTask('Inventory_Service_Test', 'doSomething', ['foo']);

        $result = $dispatcher->run($task);

        $this->assertInternalType('array', $result);
        $this->assertEquals('foo', $result['value']);
        $this->assertEquals(Core_Locale::loadDefault()->getId(), $result['locale']);
    }

}
