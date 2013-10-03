<?php
/**
 * @author matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Techno\Domain\ValidationService;

/**
 * @package Techno
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
