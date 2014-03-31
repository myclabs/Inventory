<?php

use Core\Annotation\Secure;

class Classification_ConsistencyController extends Core_Controller
{
    /**
     * Vérifie la cohérence de Classification
     * @Secure("viewClassification")
     */
    public function checkAction()
    {
    }
}
