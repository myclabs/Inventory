<?php

use Core\Work\EventListener\RabbitMQDispatcherEventListener;
use Core\Work\EventListener\InMemoryWorkerEventListener;
use Core\Work\EventListener\RabbitMQWorkerEventListener;
use Core\Work\Notification\EmailTaskNotifier;
use Core\Work\Notification\TaskNotifier;
use Core\Work\ServiceCall\ServiceCallTask;
use Interop\Container\ContainerInterface;
use MyCLabs\Work\Adapter\InMemory\InMemoryWorkDispatcher;
use MyCLabs\Work\Adapter\InMemory\InMemoryWorker;
use MyCLabs\Work\Adapter\RabbitMQ\RabbitMQWorkDispatcher;
use MyCLabs\Work\Adapter\RabbitMQ\RabbitMQWorker;
use MyCLabs\Work\Dispatcher\SynchronousWorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\TaskExecutor\ServiceCallExecutor;
use MyCLabs\Work\Worker\Worker;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPConnection;

return [

    // RabbitMQ
    'rabbitmq.enabled'  => true,
    'rabbitmq.host'     => 'localhost',
    'rabbitmq.port'     => 5672,
    'rabbitmq.user'     => 'myc-sense',
    'rabbitmq.password' => '',
    'rabbitmq.queue'    => DI\factory(function (ContainerInterface $c) {
        return $c->get('application.name') . '-work';
    }),

    'work.waitDelay' => 5,

    AMQPConnection::class => DI\object()
            ->constructor(
                DI\link('rabbitmq.host'),
                DI\link('rabbitmq.port'),
                DI\link('rabbitmq.user'),
                DI\link('rabbitmq.password')
            ),
    AMQPChannel::class => DI\factory(function (ContainerInterface $c) {
        /** @var AMQPConnection $connection */
        $connection = $c->get(AMQPConnection::class);
        $channel = $connection->channel();

        // Queue durable (= sauvegardée sur disque)
        $channel->queue_declare($c->get('rabbitmq.queue'), false, true, false, false);

        return $channel;
    }),

    // Work dispatcher
    SynchronousWorkDispatcher::class => DI\factory(function (ContainerInterface $c) {
        if ($c->get('rabbitmq.enabled')) {
            $channel = $c->get(AMQPChannel::class);
            $workDispatcher = new RabbitMQWorkDispatcher($channel, $c->get('rabbitmq.queue'));
            $workDispatcher->registerEventListener($c->get(RabbitMQDispatcherEventListener::class));
        } else {
            $workDispatcher = new InMemoryWorkDispatcher($c->get(Worker::class));
        }

        return $workDispatcher;
    }),
    WorkDispatcher::class => DI\link(SynchronousWorkDispatcher::class),

    // Worker
    Worker::class => DI\factory(function (ContainerInterface $c) {
        if ($c->get('rabbitmq.enabled')) {
            $channel = $c->get(AMQPChannel::class);
            $worker = new RabbitMQWorker($channel, $c->get('rabbitmq.queue'));
            $worker->registerEventListener($c->get(RabbitMQWorkerEventListener::class));
        } else {
            /** @var InMemoryWorker $worker */
            $worker = $c->get(InMemoryWorker::class);
            $worker->registerEventListener($c->get(InMemoryWorkerEventListener::class));
        }

        $worker->registerTaskExecutor(ServiceCallTask::class, new ServiceCallExecutor($c));

        return $worker;
    }),

    // Notifications
    TaskNotifier::class => DI\object(EmailTaskNotifier::class)
            ->constructorParameter('applicationName', DI\link('emails.noreply.name')),

];
