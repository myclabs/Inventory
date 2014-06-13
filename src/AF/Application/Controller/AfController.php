<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use AF\Application\AFViewConfiguration;
use AF\Architecture\Service\AFSerializer;
use AF\Architecture\Service\InputSerializer;
use AF\Architecture\Service\InputSetSessionStorage;
use AF\Domain\AF;
use AF\Domain\AFCopyService;
use AF\Domain\AFLibrary;
use AF\Domain\InputService;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use Core\Annotation\Secure;
use Core\Translation\TranslatedString;
use DI\Annotation\Inject;

/**
 * Countroleur des AF
 */
class AF_AfController extends Core_Controller
{
    /**
     * @Inject
     * @var InputSetSessionStorage
     */
    private $inputSetSessionStorage;

    /**
     * @Inject
     * @var AFCopyService
     */
    private $afCopyService;

    /**
     * @Inject
     * @var InputService
     */
    private $inputService;

    /**
     * @Inject
     * @var AFSerializer
     */
    private $afSerializer;

    /**
     * @Inject
     * @var InputSerializer
     */
    private $inputSerializer;


    /**
     * Affichage d'un AF en mode test
     * @Secure("editAF")
     */
    public function testAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));

        $viewConfiguration = new AFViewConfiguration();
        $viewConfiguration->setMode(AFViewConfiguration::MODE_TEST);
        $viewConfiguration->addToActionStack('submit-test', 'input', 'af');
        $viewConfiguration->setDisplayConfigurationLink(true);
        $viewConfiguration->addBaseTabs();
        $viewConfiguration->setPageTitle($this->translator->get($af->getLabel()));
        $viewConfiguration->setExitUrl('af/library/view/id/' . $af->getLibrary()->getId());
        $viewConfiguration->setResultsPreviewUrl('af/input/results-preview?id=' . $af->getId());

        // Charge la saisie depuis la session
        $inputSet = $this->inputSetSessionStorage->getInputSet($af);
        if ($inputSet === null) {
            $inputSet = $this->inputService->createDefaultInputSet($af);
            $this->inputSetSessionStorage->saveInputSet($af, $inputSet);
        }
        $viewConfiguration->setInputSet($inputSet);

        $this->setActiveMenuItemAFLibrary($af->getLibrary()->getId());
        $this->forward('display', 'af', 'af', ['viewConfiguration' => $viewConfiguration]);
    }

    /**
     * Affiche un AF
     * - id : ID d'AF
     * - viewConfiguration
     * @Secure("viewInputAF")
     */
    public function displayAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));

        if (!$this->hasParam('viewConfiguration')) {
            throw new Core_Exception_InvalidHTTPQuery("ViewConfiguration must be provided");
        }
        /** @var AFViewConfiguration $viewConfiguration */
        $viewConfiguration = $this->getParam('viewConfiguration');
        if (!$viewConfiguration->isUsable()) {
            throw new Core_Exception_InvalidHTTPQuery('The viewConfiguration is not properly configured');
        }

        $inputSet = $viewConfiguration->getInputSet();

        // Crée une nouvelle saisie initialisée avec les valeurs par défaut
        if ($inputSet === null) {
            $inputSet = $this->inputService->createDefaultInputSet($af);
        }

        $previousInputSet = $viewConfiguration->getPreviousInputSet();

        $urlParams = [
            'actionStack' => $viewConfiguration->getActionStack(),
        ];

        $this->view->assign('af', $af);
        $this->view->assign('viewConfiguration', $viewConfiguration);
        $this->view->assign('serializedAF', $this->afSerializer->serialize($af));
        $this->view->assign('inputSet', $inputSet);
        $this->view->assign('serializedInputSet', $this->inputSerializer->serialize($inputSet, $previousInputSet));
        $this->view->assign('urlParams', $urlParams);
    }

    /**
     * Onglet des résultats
     * AJAX
     * @Secure("viewInputAF")
     */
    public function displayResultsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('idAF'));
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = PrimaryInputSet::load($idInputSet);
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af);
            // Recalcule les résultats parce que sinon problème de serialisation de proxies en session
            if ($inputSet) {
                $this->inputService->updateResults($inputSet);
            }
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        $this->_helper->layout->disableLayout();
    }

    /**
     * Onglet détail des calculs
     * AJAX
     * @Secure("viewInputAF")
     */
    public function displayCalculationDetailsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('idAF'));
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = PrimaryInputSet::load($idInputSet);
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af);
            // Recalcule les résultats parce que sinon problème de serialisation de proxies en session
            if ($inputSet) {
                $this->inputService->updateResults($inputSet);
            }
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        $this->_helper->layout->disableLayout();
    }

    /**
     * Onglet de documentation d'un AF
     * AJAX
     * @Secure("viewInputAF")
     */
    public function displayDocumentationAction()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = AF::load($this->getParam('idAF'));
        $this->_helper->layout->disableLayout();
    }

    /**
     * Popup affichant l'expression d'un algo numérique sous forme de graphe
     * AJAX
     * @Secure("public")
     */
    public function popupExpressionGraphAction()
    {
        /** @var NumericExpressionAlgo $algo */
        $algo = NumericExpressionAlgo::load($this->getParam('id'));

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->algo = $algo;
        $this->_helper->layout->disableLayout();
    }

    /**
     * Popup de copie d'un AF
     * @Secure("editAFLibrary")
     */
    public function duplicatePopupAction()
    {
        /** @var $library AFLibrary */
        $library = AFLibrary::load($this->getParam('library'));

        $this->view->assign('id', $this->getParam('idAF'));
        $this->view->assign('library', $library);
        $this->_helper->layout->disableLayout();
    }

    /**
     * Duplique un AF
     * @Secure("editAFLibrary")
     */
    public function duplicateAction()
    {
        /** @var $library AFLibrary */
        $library = AFLibrary::load($this->getParam('library'));
        /** @var $af AF */
        $af = AF::load($this->getParam('idAF'));

        $newLabel = $this->getParam('label');
        if ($newLabel == '') {
            UI_Message::addMessageStatic(__('UI', 'formValidation', 'emptyRequiredField'), UI_Message::TYPE_ERROR);
            $this->redirect('af/library/view/id/' . $library->getId());
            return;
        }

        $newLabel = $this->translator->set(new TranslatedString(), $newLabel);
        $newAF = $this->afCopyService->copyAF($af, $newLabel);

        $newAF->save();
        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'added'), UI_Message::TYPE_SUCCESS);
        $this->redirect('af/library/view/id/' . $library->getId());
    }
}
