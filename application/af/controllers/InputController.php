<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;
use Unit\UnitAPI;

/**
 * Saisie des AF
 * @package AF
 */
class AF_InputController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var AF_Service_InputService
     */
    private $inputService;

    /**
     * @Inject
     * @var AF_Service_InputSetSessionStorage
     */
    private $inputSetSessionStorage;

    /**
     * @Inject
     * @var AF_Service_InputHistoryService
     */
    private $inputHistoryService;

    /**
     * @Inject
     * @var AF_Service_InputFormParser
     */
    private $inputFormParser;

    /**
     * Soumission d'un AF
     * AJAX
     * - id ID d'AF
     * - actionStack array() Liste d'actions ZF à appeler
     * @Secure("editInputAF")
     */
    public function submitAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $this->setParam('af', $af);

        // Form data
        $formData = json_decode($this->getParam($af->getRef()), true);
        $errorMessages = [];

        $inputSet = $this->inputFormParser->parseForm($formData, $af, $errorMessages);

        // Fait suivre aux actions de processing
        $actions = json_decode($this->getParam('actionStack'), true);
        // Fait suivre à la fin à l'action qui renvoie la réponse
        $actions[] = [
            'action'     => 'submit-send-response',
            'controller' => 'input',
            'module'     => 'af',
            'params'     => ['errorMessages' => $errorMessages],
        ];

        // On est obligé de construire un "container" pour que les sous-actions puissent remplacer l'inputset
        $inputSetContainer = new \stdClass();
        $inputSetContainer->inputSet = $inputSet;

        // Reverse car l'action stack est une pile (last in first out)
        $actions = array_reverse($actions);
        foreach ($actions as $action) {
            $request = clone $this->getRequest();
            $request->setModuleName($action['module']);
            $request->setControllerName($action['controller']);
            $request->setActionName($action['action']);
            if (isset($action['params'])) {
                $request->setParams($action['params']);
            }
            $request->setParam('inputSetContainer', $inputSetContainer);
            $this->_helper->actionStack($request);
        }

        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Retourne la réponse de la soumission d'un AF
     * - inputSet
     * @Secure("editInputAF")
     */
    public function submitSendResponseAction()
    {
        $inputSetContainer = $this->getParam('inputSetContainer');
        /** @var $inputSet AF_Model_InputSet_Primary */
        $inputSet = $inputSetContainer->inputSet;

        $this->addFormErrors($this->getParam('errorMessages', []));

        if ($inputSet->isInputComplete()) {
            $this->setFormMessage(__('AF', 'inputInput', 'completeInputSaved'), UI_Message::TYPE_SUCCESS);
        } else {
            $this->setFormMessage(__('AF', 'inputInput', 'incompleteInputSaved'), UI_Message::TYPE_SUCCESS);
        }

        $data = [
            'status'     => $inputSet->getStatus(),
            'completion' => $inputSet->getCompletion(),
        ];

        if ($inputSet->getId() > 0) {
            $data['idInputSet'] = $inputSet->getId();
        }

        $this->sendFormResponse($data);
    }

    /**
     * Sauvegarde l'inputSet après sa MAJ
     * - af
     * - inputSet
     * @Secure("editAF")
     */
    public function submitTestAction()
    {
        $inputSetContainer = $this->getParam('inputSetContainer');
        /** @var $inputSet AF_Model_InputSet_Primary */
        $inputSet = $inputSetContainer->inputSet;

        // Met à jour les résultats
        $this->inputService->updateResults($inputSet);

        // Sauvegarde en session
        $this->inputSetSessionStorage->saveInputSet($this->getParam('af'), $inputSet);

        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Aperçu des résultats
     * AJAX
     * @Secure("editInputAF")
     */
    public function resultsPreviewAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));

        // Form data
        $formContent = json_decode($this->getParam($af->getRef()), true);
        $errorMessages = [];

        // Remplit l'InputSet
        $inputSet = $this->inputFormParser->parseForm($formContent, $af, $errorMessages);
        $this->inputService->updateResults($inputSet);

        $this->addFormErrors($errorMessages);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;

        $data = $this->view->render('af/display-results.phtml');

        // Force le statut en success (sinon les handlers JS ne sont pas exécutés)
        $this->setFormMessage(null, UI_Message::TYPE_SUCCESS);
        $this->sendFormResponse($data);
    }

    /**
     * Marque une saisie comme terminée
     * AJAX
     * @Secure("editInputAF")
     */
    public function markInputAsFinishedAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        if ($this->hasParam('idInputSet')) {
            // Charge la saisie depuis la BDD
            /** @var $inputSet AF_Model_InputSet_Primary */
            $inputSet = AF_Model_InputSet_Primary::load($this->getParam('idInputSet'));
            $inputSet->markAsFinished($this->getParam('value'));
            $inputSet->save();
            $this->entityManager->flush();
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
            if ($inputSet === null) {
                throw new Core_Exception_User("AF", "message", "inputSetDoesntExist");
            }
            $inputSet->markAsFinished($this->getParam('value'));
            $this->inputSetSessionStorage->saveInputSet($af, $inputSet);
        }

        $this->sendJsonResponse([
                                'message'    => __("AF", "inputInput", "progressStatusUpdated"),
                                'status'     => $inputSet->getStatus(),
                                'completion' => $inputSet->getCompletion(),
                                ]);
    }

    /**
     * Retourne l'historique des valeurs d'une saisie
     * AJAX
     * @Secure("viewCell")
     */
    public function inputHistoryAction()
    {
        $idInput = $this->getParam('idInput', null);

        // Pour gérer le cas où on demande l'historique dans l'interface de test des AF
        if ($idInput !== null) {
            /** @var $input AF_Model_Input */
            $input = AF_Model_Input::load($this->getParam('idInput'));

            $entries = $this->inputHistoryService->getInputHistory($input);

            $this->view->assign('component', $input->getComponent());
        } else {
            $entries = [];
        }

        $this->view->assign('entries', $entries);
        $this->_helper->layout->disableLayout();
    }

}
