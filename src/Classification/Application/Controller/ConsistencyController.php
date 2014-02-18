<?php
/**
 * Classe Classification_ConsistencyController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * Index Controller
 * @package Classification
 */
class Classification_ConsistencyController extends Core_Controller
{
    /**
     * Vérifie la cohérence de Classification
     *
     * @Secure("viewClassification")
     */
    public function checkAction()
    {
    }

}
