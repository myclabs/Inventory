<?php

use AF\Application\InputFormParser;
use AF\Architecture\Service\InputSetSessionStorage;
use AF\Domain\AF;
use AF\Domain\InputHistoryService;
use AF\Domain\InputService;
use AF\Domain\Input\Input;
use AF\Domain\InputSet\PrimaryInputSet;
use Core\Annotation\Secure;

/**
 * Saisie des AF.
 *
 * @author matthieu.napoli
 */
class AF_InputController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var InputService
     */
    private $inputService;

    /**
     * @Inject
     * @var InputSetSessionStorage
     */
    private $inputSetSessionStorage;

    /**
     * @Inject
     * @var InputHistoryService
     */
    private $inputHistoryService;

    /**
     * @Inject
     * @var InputFormParser
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
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
        /** @var $inputSet PrimaryInputSet */
        $inputSet = $inputSetContainer->inputSet;

        $this->addFormErrors($this->getParam('errorMessages', []));

        if ($inputSet->isInputComplete() && $inputSet->isCalculationComplete()) {
            $this->setFormMessage(__('AF', 'inputInput', 'completeInputSaved'), UI_Message::TYPE_SUCCESS);
        } elseif ($inputSet->isInputComplete()) {
            $message = $inputSet->getErrorMessage() ?: __('AF', 'inputInput', 'completeInputSaved');
            $this->setFormMessage($message, UI_Message::TYPE_SUCCESS);
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
        /** @var $inputSet PrimaryInputSet */
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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));

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
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        if ($this->hasParam('idInputSet')) {
            // Charge la saisie depuis la BDD
            /** @var $inputSet PrimaryInputSet */
            $inputSet = PrimaryInputSet::load($this->getParam('idInputSet'));
            $inputSet->markAsFinished(true);
            $inputSet->save();
            $this->entityManager->flush();
        } else {
            // Récupère la saisie en session
            $inputSet = $this->inputSetSessionStorage->getInputSet($af, false);
            if ($inputSet === null) {
                throw new Core_Exception_User("AF", "message", "inputSetDoesntExist");
            }
            $inputSet->markAsFinished(true);
            $this->inputSetSessionStorage->saveInputSet($af, $inputSet);
        }

        $this->sendJsonResponse([
            'message'    => __("AF", "inputInput", "inputFinished"),
            'status'     => $inputSet->getStatus(),
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
            /** @var $input Input */
            $input = Input::load($this->getParam('idInput'));

            $entries = $this->inputHistoryService->getInputHistory($input);

            $this->view->assign('component', $input->getComponent());
        } else {
            $entries = [];
        }

        $this->view->assign('entries', $entries);
        $this->_helper->layout->disableLayout();
    }
}
