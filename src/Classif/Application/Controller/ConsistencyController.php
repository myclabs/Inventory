<?php
/**
 * Classe Classif_ConsistencyController
 * @author valentin.claras
 * @package    Classif
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Index Controller
 * @package Classif
 */
class Classif_ConsistencyController extends Core_Controller
{
    /**
     * Vérifie la cohérence de Classif
     *
     * @Secure("viewClassif")
     */
    public function checkAction()
    {
    }

}