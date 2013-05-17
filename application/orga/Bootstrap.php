<?php
/**
 * @author valentin.claras
 * @package Orga
 */

error_reporting(E_ALL);

/**
 * @author valentin.claras
 * @package Orga
 */
class Orga_Bootstrap extends Core_Package_Bootstrap
{

    /**
     * Enregistre les Workers
     */
    protected function _initOrgaWorker()
    {
        /**@var Core_Work_Dispatcher $dispatcher */
        $dispatcher = Zend_Registry::get('workDispatcher');
        $dispatcher->registerWorker(new Orga_Work_WorkerGranularity());
        $dispatcher->registerWorker(new Orga_Work_WorkerMember());
    }

}
