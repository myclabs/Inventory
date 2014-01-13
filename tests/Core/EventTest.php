<?php

namespace Tests\Core;

use Core\Test\TestCase;
use Core_Event_ObservableTrait;
use Core_Event_ObserverInterface;
use Core_EventDispatcher;

class EventTest extends TestCase
{
    protected $subject1;
    protected $subject2;
    protected $observer1;
    protected $observer2;

    public function setUp()
    {
        parent::setUp();

        /** @var Core_EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get(Core_EventDispatcher::class);

        $this->subject1 = new testSubject1();
        $this->subject2 = new testSubject2();
        $this->observer1 = new testObserver1();
        $this->observer2 = new testObserver2();
        if (!$eventDispatcher->hasListener(testSubject1::class, testObserver1::class)) {
            $eventDispatcher->addListener(testObserver1::class, testSubject1::class);
        }
        if (!$eventDispatcher->hasListener(testSubject1::class, testObserver2::class)) {
            $eventDispatcher->addListener(testObserver2::class, testSubject1::class);
        }
        if (!$eventDispatcher->hasListener(testSubject2::class, testObserver2::class)) {
            $eventDispatcher->addListener(testObserver2::class, testSubject2::class);
        }
    }

    /**
     * Test le lancement d'un évent.
     */
    public function testEvent()
    {
        testObserver1::$proof = null;
        $this->assertEquals(null, testObserver1::$proof);
        $this->subject1->start();
        $this->assertEquals('event by Tests\Core\testSubject1 : ', testObserver1::$proof);
    }

    /**
     * Test le lancement de plusieurs events.
     */
    public function testEvents()
    {
        testObserver2::$proof = null;
        $this->assertEquals(null, testObserver2::$proof);
        $this->subject2->launchEvent1();
        $this->assertEquals('event 1 by Tests\Core\testSubject2 : a - b', testObserver2::$proof);
        $this->subject2->launchEvent2();
        $this->assertEquals('event 2 by Tests\Core\testSubject2 : c - d', testObserver2::$proof);
    }
}

/**
 * Class de test Event.
 */
class testSubject1
{
    use Core_Event_ObservableTrait;

    const EVENT = 'event';

    /**
     * AfterEvent
     */
    public function start()
    {
        $this->launchEvent(self::EVENT);
    }
}

/**
 * Class de test Event.
 */
class testSubject2
{
    use Core_Event_ObservableTrait;

    const EVENT_1 = 'event 1';
    const EVENT_2 = 'event 2';

    /**
     * BeforeEvent
     */
    public function launchEvent1()
    {
        $this->launchEvent(self::EVENT_1, array('a', 'b'));
    }

    /**
     * AfterEvent
     */
    public function launchEvent2()
    {
        $this->launchEvent(self::EVENT_2, array('c', 'd'));
    }
}

/**
 * Class de test Observer.
 */
class testObserver1 implements Core_Event_ObserverInterface
{
    public static $proof;

    public static function applyEvent($event, $subject, $arguments = array())
    {
        self::$proof = $event . ' by ' . get_class($subject) . ' : ' . implode(' - ', $arguments);
    }
}

/**
 * Class de test Observer.
 */
class testObserver2 implements Core_Event_ObserverInterface
{
    public static $proof;

    public static function applyEvent($event, $subject, $arguments = array())
    {
        self::$proof = $event . ' by ' . get_class($subject) . ' : ' . implode(' - ', $arguments);
    }
}
