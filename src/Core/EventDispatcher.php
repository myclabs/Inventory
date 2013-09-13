<?php
/**
 * @author     valentin.claras
 * @package    Core
 * @subpackage EventDispatcher
 */

/**
 * Observateur des classes
 *
 * @package    Core
 * @subpackage EventDispatcher
 */
class Core_EventDispatcher
{
    /**
     * List of all observable enteties referencing their observers
     *
     * @var array() observable => observer[]
     */
    protected $subjects = array();

    /**
     * Add a link between an observer and an observable.
     *
     * @param string $observerName
     * @param string $observableName
     */
    public function addListener($observerName, $observableName)
    {
        if (!($this->hasListener($observerName, $observableName))) {
            if (isset($this->subjects[$observableName])) {
                $this->subjects[$observableName][] = $observerName;
            } else {
                $this->subjects[$observableName] = array($observerName);
            }
        }
    }

    /**
     * Check if a link between an observer and an observable already exist.
     *
     * @param srring $observerName
     * @param string $observableName
     *
     * @return bool
     */
    public function hasListener($observerName, $observableName)
    {
        if ((isset($this->subjects[$observableName])) && (in_array($observerName, $this->subjects[$observableName]))) {
            return true;
        }
        return false;
    }

    /**
     * Add a link between an observer and an observable.
     *
     * @param string $observerName
     * @param string $observableName
     */
    public function removeListener($observerName, $observableName)
    {
        if ($this->hasListener($observerName, $observableName)) {
            unset($this->subjects[$observableName][array_search($observerName, $this->subjects[$observableName])]);
        }
    }

    /**
     * Triggered when an event is fired. It will alert all observers of the observable entity.
     *
     * @param Core_Model_Entity $observable
     * @param string $event
     * @param array  $arguments
     */
    public function launch($observable, $event, $arguments=array())
    {
        if (isset($this->subjects[get_class($observable)])) {
            foreach ($this->subjects[get_class($observable)] as $observerClassName) {
                $observerClassName::applyEvent($event, $observable, $arguments);
            }
        }
    }

}
