<?php
/**
* @package Inventory
* @subpackage ModelProvider
*/
/**
* Classe faisant le lien entre les cellules de la structure Orga, les AF et le DW.
* @author valentin.claras
* @package Inventory
* @subpackage ModelProvider
*/
class Inventory_ReportResourceProvider implements Core_Event_ObserverInterface
{
    /**
     * Utilisé quand un événement est lancé.
     *
     * @param string            $event
     * @param Core_Model_Entity $subject
     * @param array             $arguments
     */
    public static function applyEvent($event, $subject, $arguments=array())
    {
        switch ($event) {
            case DW_Model_Report::EVENT_SAVED:
                $resource = new User_Model_Resource_Entity();
                $resource->setEntity($resource);
                $resource->save();
                break;
            case DW_Model_Report::EVENT_DELETED:
                $resource = User_Model_Resource_Entity::loadByEntity($subject);
                $resource->delete();
                break;
        }
    }
}
