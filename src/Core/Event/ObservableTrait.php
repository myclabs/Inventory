<?php
/**
 * Fichier de la classe de trait Observable.
 *
 * @author     valentin.claras
 *
 * @package    Core
 * @subpackage EventDispatcher
 */

/**
 * Description of ObservableTrait.
 *
 * Trait utilisé par les Core_Model_Entity souhaitant être observable par les autres.
 *
 * @package    Core
 * @subpackage EventDispatcher
 */
trait Core_Event_ObservableTrait
{
    /**
     * Lance en évent lié à la l'objet.
     *
     * @param string $event Identifier de l'événement.
     * @param array  $arguments optionnel
     */
    protected function launchEvent($event, $arguments=array())
    {
        Core_EventDispatcher::getInstance()->launch($this, $event, $arguments);
    }

}