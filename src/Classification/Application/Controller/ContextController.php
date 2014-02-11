<?php
/**
 * Classe Classification_ContextController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Context.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_ContextController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Context.
     *
     * @Secure("viewClassif")
     */
    public function listAction()
    {
    }

    /**
     * Action appelé à la gestion des Context.
     *
     * @Secure("editClassif")
     */
    public function manageAction()
    {
    }

}
