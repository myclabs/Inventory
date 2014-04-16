<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use AF\Application\AFViewConfiguration;
use AF\Architecture\Service\InputSetSessionStorage;
use AF\Domain\AF;
use AF\Domain\AFCopyService;
use AF\Domain\AFLibrary;
use AF\Domain\InputService;
use AF\Domain\InputSet\PrimaryInputSet;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use Core\Annotation\Secure;
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
        $viewConfiguration->setPageTitle($af->getLabel());
        $viewConfiguration->setUseSession(true);
        $viewConfiguration->setExitUrl('af/library/view/id/' . $af->getLibrary()->getId());

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
        $viewConfiguration = $this->getParam('viewConfiguration');
        if (!$viewConfiguration->isUsable()) {
            throw new Core_Exception_InvalidHTTPQuery('The viewConfiguration is not properly configured');
        }

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->viewConfiguration = $viewConfiguration;
    }

    /**
     * Affiche le formulaire pour y rentrer des saisies
     * AJAX
     * @Secure("modeInputAF")
     */
    public function displayInputAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('idAF'));
        $actionStack = json_decode($this->getParam('actionStack'));
        $exitURL = urldecode($this->getParam('exitURL'));
        $resultsPreviewUrl = json_decode(urldecode($this->getParam('resultsPreviewUrl')));
        $mode = $this->getParam('mode');
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = PrimaryInputSet::load($idInputSet);
        } elseif ($this->getParam('useSession')) {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
        } else {
            $inputSet = new PrimaryInputSet($af);
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->mode = $mode;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->exitURL = $exitURL;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->withResultsPreview = $this->getParam('resultsPreview');
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->resultsPreviewUrl = $resultsPreviewUrl;
        // Génère le formulaire
        $form = $af->generateForm($inputSet, $mode);
        $form->setAjax(true, 'inputSavedHandler');
        // URL de submit
        $params = ['id' => $af->getId(), 'actionStack' => json_encode($actionStack)];
        // Ajoute les paramètres personnalisés qu'il peut y'avoir dans l'URL
        $urlParams = $this->getAllParams();
        unset(
            $urlParams['module'],
            $urlParams['controller'],
            $urlParams['action'],
            $urlParams['idAF'],
            $urlParams['actionStack'],
            $urlParams['exitURL'],
            $urlParams['resultsPreviewUrl'],
            $urlParams['mode'],
            $urlParams['idInputSet']
        );
        $this->view->urlParams = $urlParams;
        $params += $urlParams;
        if ($idInputSet) {
            $params['idInputSet'] = $idInputSet;
        }
        if ($mode != AFViewConfiguration::MODE_READ) {
            $form->setAction($this->_helper->url('submit', 'input', 'af', $params));
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->form = $form;
        $this->_helper->layout->disableLayout();
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
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
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
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
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
     * @Secure("viewInputAF")
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

        $newAF = $this->afCopyService->copyAF($af, $newLabel);

        $newAF->save();
        $this->entityManager->flush();

        UI_Message::addMessageStatic(__('UI', 'message', 'added'), UI_Message::TYPE_SUCCESS);
        $this->redirect('af/library/view/id/' . $library->getId());
    }
}
