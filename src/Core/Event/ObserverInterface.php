<?php
/**
 * Fichier de la classe de trait Observer.
 *
 * @author     valentin.claras
 *
 * @package    Core
 * @subpackage EventDispatcher
 */

/**
 * Description of ObserverTrait.
 *
 * Interface utilisée par les Core_Model_Entity souhaitant surveiller des entité observables.
 *
 * @package    Core
 * @subpackage EventDispatcher
 */
interface Core_Event_ObserverInterface
{
    /**
     * Utilisé quand un événement est lancé.
     *
     * @param string			$event
     * @param Core_Model_Entity $subject
     * @param array				$arguments
     */
    public static function applyEvent($event, $subject, $arguments=array());

}