<?php
use Core\Test\TestCase;

/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage Event
 */

/**
 * Test des fonctionnalités de l'objet métier Core_Model_List.
 *
 * @package Core
 * @subpackage Event
 */
class Core_Test_EventTest extends TestCase
{
    // Attributs des Tests.
    protected $subject1;
    protected $subject2;
    protected $observer1;
    protected $observer2;


    /**
     * Méthode appelée avant l'exécution des tests.
     */
    public function setUp()
    {
        parent::setUp();

        /** @var Core_EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->get('Core_EventDispatcher');

        $this->subject1 = new testSubject1();
        $this->subject2 = new testSubject2();
        $this->observer1 = new testObserver1();
        $this->observer2 = new testObserver2();
        if (!$eventDispatcher->hasListener('testSubject1', 'testObserver1')) {
            $eventDispatcher->addListener('testObserver1', 'testSubject1');
        }
        if (!$eventDispatcher->hasListener('testSubject1', 'testObserver2')) {
            $eventDispatcher->addListener('testObserver2', 'testSubject1');
        }
        if (!$eventDispatcher->hasListener('testSubject2', 'testObserver2')) {
            $eventDispatcher->addListener('testObserver2', 'testSubject2');
        }
    }

    /**
     * Test le lancement d'un évent.
     */
    function testEvent()
    {
        testObserver1::$proof = null;
        $this->assertEquals(null, testObserver1::$proof);
        $this->subject1->start();
        $this->assertEquals('event by testSubject1 : ', testObserver1::$proof);
    }

    /**
     * Test le lancement de plusieurs events.
     */
    function testEvents()
    {
        testObserver2::$proof = null;
        $this->assertEquals(null, testObserver2::$proof);
        $this->subject2->launchEvent1();
        $this->assertEquals('event 1 by testSubject2 : a - b', testObserver2::$proof);
        $this->subject2->launchEvent2();
        $this->assertEquals('event 2 by testSubject2 : c - d', testObserver2::$proof);
    }
}

/**
 * Class de test Event.
 *
 * @package Core
 * @subpackage Event
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
 *
 * @package Core
 * @subpackage Event
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
 *
 * @package Core
 * @subpackage Event
 */
class testObserver1 implements Core_Event_ObserverInterface
{
    public static $proof;

    /**
     * Test the effect of an Event.
     * @param string			$event
     * @param Core_Model_Entity $subject
     * @param array				$arguments
     * @return array Array of messages (string)
     */
    public static function applyEvent($event, $subject, $arguments=array())
    {
        self::$proof = $event . ' by ' . get_class($subject) . ' : ' . implode(' - ', $arguments);
    }
}

/**
 * Class de test Observer.
 *
 * @package Core
 * @subpackage Event
 */
class testObserver2 implements Core_Event_ObserverInterface
{
    public static $proof;

    /**
     * Test the effect of an Event.
     * @param string			$event
     * @param Core_Model_Entity $subject
     * @param array				$arguments
     * @return array Array of messages (string)
     */
    public static function applyEvent($event, $subject, $arguments=array())
    {
        self::$proof = $event . ' by ' . get_class($subject) . ' : ' . implode(' - ', $arguments);
    }
}