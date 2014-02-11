<?php
/**
 * Classe Classif_ContextController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Context.
 * @package    Classif
 * @subpackage Controller
 */
class Classif_ContextController extends Core_Controller
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