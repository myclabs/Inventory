<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Countroleur des AF
 * @package AF
 */
class AF_AfController extends Core_Controller
{

    /**
     * @Inject
     * @var AF_Service_InputSetSessionStorage
     */
    private $inputSetSessionStorage;

    /**
     * Liste des AF
     * @Secure("editAF")
     */
    public function listAction()
    {
    }

    /**
     * Arbre des AF
     * @Secure("editAF")
     */
    public function treeAction()
    {
    }

    /**
     * Affichage d'un AF en mode test
     * @Secure("editAF")
     */
    public function testAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));

        $viewConfiguration = new AF_ViewConfiguration();
        $viewConfiguration->setMode(AF_ViewConfiguration::MODE_TEST);
        $viewConfiguration->addToActionStack('submit-test', 'input', 'af');
        $viewConfiguration->setDisplayConfigurationLink(true);
        $viewConfiguration->addBaseTabs();
        $viewConfiguration->setPageTitle($af->getLabel());
        if ($this->hasParam('fromTree')) {
            $viewConfiguration->setExitUrl($this->_helper->url('tree', 'af', 'af'));
        } else {
            $viewConfiguration->setExitUrl($this->_helper->url('list', 'af', 'af'));
        }

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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));

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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('idAF'));
        $actionStack = json_decode($this->getParam('actionStack'));
        $exitURL = urldecode($this->getParam('exitURL'));
        $mode = $this->getParam('mode');
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = AF_Model_InputSet_Primary::load($idInputSet);
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
        }
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->mode = $mode;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->exitURL = $exitURL;
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
            $urlParams['mode'],
            $urlParams['idInputSet']
        );
        $this->view->urlParams = $urlParams;
        $params += $urlParams;
        if ($idInputSet) {
            $params['idInputSet'] = $idInputSet;
        }
        if ($mode != AF_ViewConfiguration::MODE_READ) {
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('idAF'));
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = AF_Model_InputSet_Primary::load($idInputSet);
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
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
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('idAF'));
        $idInputSet = $this->getParam('idInputSet');
        if ($idInputSet) {
            // Charge la saisie depuis la BDD
            $inputSet = AF_Model_InputSet_Primary::load($idInputSet);
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
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
        $this->view->af = AF_Model_AF::load($this->getParam('idAF'));
        $this->_helper->layout->disableLayout();
    }

    /**
     * Popup affichant l'expression d'un algo numérique sous forme de graphe
     * AJAX
     * @Secure("viewInputAF")
     */
    public function popupExpressionGraphAction()
    {
        /** @var Algo_Model_Numeric_Expression $algo */
        $algo = Algo_Model_Numeric_Expression::load($this->getParam('id'));

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->algo = $algo;
        $this->_helper->layout->disableLayout();
    }

}
