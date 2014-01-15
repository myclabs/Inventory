<?php

use Core\Work\EventListener\RabbitMQEventListener;
use Core\Work\EventListener\SimpleEventListener;
use Core\Work\Notification\EmailTaskNotifier;
use Core\Work\Notification\TaskNotifier;
use Core\Work\ServiceCall\ServiceCallTask;
use DI\Container;
use MyCLabs\Work\Dispatcher\RabbitMQWorkDispatcher;
use MyCLabs\Work\Dispatcher\SimpleWorkDispatcher;
use MyCLabs\Work\Dispatcher\WorkDispatcher;
use MyCLabs\Work\TaskExecutor\ServiceCallExecutor;
use MyCLabs\Work\Worker\RabbitMQWorker;
use MyCLabs\Work\Worker\SimpleWorker;
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
    'rabbitmq.queue'    => DI\factory(function (Container $c) {
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
    AMQPChannel::class => DI\factory(function (Container $c) {
        /** @var AMQPConnection $connection */
        $connection = $c->get(AMQPConnection::class);
        $channel = $connection->channel();

        // Queue durable (= sauvegardée sur disque)
        $channel->queue_declare($c->get('rabbitmq.queue'), false, true, false, false);

        return $channel;
    }),

    // Work dispatcher
    WorkDispatcher::class => DI\factory(function (Container $c) {
        if ($c->get('rabbitmq.enabled')) {
            $channel = $c->get(AMQPChannel::class);
            $workDispatcher = new RabbitMQWorkDispatcher($channel, $c->get('rabbitmq.queue'));
            $workDispatcher->addEventListener($c->get(RabbitMQEventListener::class));
        } else {
            $workDispatcher = new SimpleWorkDispatcher($c->get(Worker::class));
            $workDispatcher->addEventListener($c->get(SimpleEventListener::class));
        }

        return $workDispatcher;
    }),

    // Worker
    Worker::class => DI\factory(function (Container $c) {
        if ($c->get('rabbitmq.enabled')) {
            $channel = $c->get(AMQPChannel::class);
            $worker = new RabbitMQWorker($channel, $c->get('rabbitmq.queue'));
            $worker->addEventListener($c->get(RabbitMQEventListener::class));
        } else {
            $worker = $c->get(SimpleWorker::class);
            $worker->addEventListener($c->get(SimpleEventListener::class));
        }

        $worker->registerTaskExecutor(ServiceCallTask::class, new ServiceCallExecutor($c));

        return $worker;
    }),

    // Notifications
    TaskNotifier::class => DI\object(EmailTaskNotifier::class)
            ->methodParameter('__construct', 'applicationName', DI\link('email.noreply.name')),

];
