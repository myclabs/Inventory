<?php

use Core\Annotation\Secure;
use Keyword\Application\Service\KeywordService;

/**
 * Controleur des significations
 * @author matthieu.napoli
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
