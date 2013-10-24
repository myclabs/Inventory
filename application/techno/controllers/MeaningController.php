<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Keyword\Application\Service\KeywordService;

/**
 * Controleur des significations
 * @package Techno
 */
class Techno_MeaningController extends Core_Controller
{
    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * Liste des significations
     * @Secure("editTechno")
     */
    public function listAction()
    {
        $this->view->keywords = $this->keywordService->getAll();
    }
}
