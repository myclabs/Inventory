<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Techno\Domain\ValidationService;

/**
 * @author matthieu.napoli
 */
class Techno_ValidationController extends Core_Controller
{
    /**
     * @Inject
     * @var ValidationService
     */
    private $validationService;

    /**
     * @Secure("editTechno")
     */
    public function resultsAction()
    {
        $this->view->keywordMeaningsErrors = $this->validationService->validateMeaningsKeywords();
        $this->view->keywordFamilyTagsErrors = $this->validationService->validateFamilyTagsKeywords();
        $this->view->keywordFamilyMembersErrors = $this->validationService->validateFamilyMembersKeywords();
    }
}
