<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use Keyword\Domain\Keyword;

/**
 * Controleur des significations
 * @package Techno
 */
class Techno_MeaningController extends Core_Controller
{

    /**
     * Liste des significations
     * @Secure("editTechno")
     */
    public function listAction()
    {
        $this->view->keywords = Keyword::loadList();
    }

}
