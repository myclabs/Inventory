<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Gedmo\Loggable\Entity\Repository\LogEntryRepository;

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

        // InputSet
        $inputSet = new AF_Model_InputSet_Primary($af);

        // Form data
        $formData = json_decode($this->getParam($af->getRef()), true);

        $errorMessages = $this->parseAfSubmit($formData, $inputSet, $af);

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

        // Crée une nouvelle saisie temporaire
        $inputSet = new AF_Model_InputSet_Primary($af);

        // Form data
        $formContent = json_decode($this->getParam($af->getRef()), true);

        // Remplit l'InputSet
        $newValues = new AF_Model_InputSet_Primary($af);
        $errorMessages = $this->parseAfSubmit($formContent, $newValues, $af);
        $this->inputService->editInputSet($inputSet, $newValues);

        $this->addFormErrors($errorMessages);

        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->inputSet = $inputSet;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->isInputComplete = $inputSet->isInputComplete();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->af = $af;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->updateInputProgress = true;

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
        $this->sendJsonResponse(__("AF", "inputInput", "progressStatusUpdated"));

        $this->sendJsonResponse([
                                'message'    => __("AF", "inputInput", "progressStatusUpdated"),
                                'status'     => $inputSet->getStatus(),
                                'completion' => $inputSet->getCompletion(),
                                ]);
    }

    /**
     * Retourne l'historique des valeurs d'une saisie
     * AJAX
     * @Secure("editInputAF")
     */
    public function inputHistoryAction()
    {
        /** @var $input AF_Model_Input */
        $input = AF_Model_Input::load($this->getParam('idInput'));

        $entries = $this->inputHistoryService->getInputHistory($input);

        $this->view->assign('component', $input->getComponent());
        $this->view->assign('entries', $entries);
        $this->_helper->layout->disableLayout();
    }


    /**
     * Parse la soumission d'un AF pour remplir un InputSet
     * @param array             $formContent
     * @param AF_Model_InputSet $inputSet
     * @param AF_Model_AF       $af
     * @return array Error messages indexed by the field name
     */
    private function parseAfSubmit(array $formContent, AF_Model_InputSet $inputSet, AF_Model_AF $af)
    {
        $errorMessages = [];
        foreach ($formContent as $fullRef => $inputContent) {
            $refComponents = explode(UI_Generic::REF_SEPARATOR, $fullRef);
            $ref = $refComponents[count($refComponents) - 1];
            $number = null;
            // Si le ref est un numéro de sous-af répété
            if (is_numeric($ref)) {
                $ref = $refComponents[count($refComponents) - 2];
            }
            // Free label
            if ($ref == 'freeLabel') {
                continue;
            }
            try {
                $afComponent = AF_Model_Component::loadByRef($ref, $af);
            } catch (Core_Exception_NotFound $e) {
                // Ça n'est pas un composant de l'AF
                continue;
            }
            // Récupère l'Input
            $errorMessages += $this->createInputFromComponent($fullRef, $afComponent, $inputSet, $inputContent);
            // Groupe
            if ($afComponent instanceof AF_Model_Component_Group) {
                $errorMessages += $this->parseAfSubmit($inputContent['elements'], $inputSet, $af);
            }
        }
        return $errorMessages;
    }

    /**
     * @param string             $fullRef Le ref du champ du formulaire (avec les préfixes)
     * @param AF_Model_Component $component
     * @param AF_Model_InputSet  $inputSet
     * @param array              $inputContent
     * @throws InvalidArgumentException
     * @return array Error messages indexed by the field name
     */
    private function createInputFromComponent($fullRef, AF_Model_Component $component, AF_Model_InputSet $inputSet,
                                              array $inputContent
    ) {
        $errorMessages = [];
        if ($component instanceof AF_Model_Component_Group) {
            // Groupe
            $input = new AF_Model_Input_Group($inputSet, $component);
        } elseif ($component instanceof AF_Model_Component_Numeric) {
            // Champ numérique
            $input = new AF_Model_Input_Numeric($inputSet, $component);
            $locale = Core_Locale::loadDefault();
            $value = null;
            try {
                $value = $locale->readNumber($inputContent['value']);
                if ($component->getRequired() && $value === null) {
                    $errorMessages[$fullRef] = __('UI', 'formValidation', 'emptyRequiredField');
                }
            } catch (Core_Exception_InvalidArgument $e) {
                $errorMessages[$fullRef] = __('UI', 'formValidation', 'invalidNumber');
            }
            $relativeUncertainty = null;
            if ($component->getWithUncertainty()) {
                try {
                    $childInputContent = current($inputContent['children']);
                    $relativeUncertainty = $locale->readInteger($childInputContent['value']);
                    if ($relativeUncertainty < 0) {
                        $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                    }
                } catch (Core_Exception_InvalidArgument $e) {
                    $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                }
            }
            $input->setValue(
                new Calc_UnitValue($component->getUnit(), $value, $relativeUncertainty)
            );
        } elseif ($component instanceof AF_Model_Component_Text) {
            // Champ texte
            $input = new AF_Model_Input_Text($inputSet, $component);
            if (isset($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            }
        } elseif ($component instanceof AF_Model_Component_Checkbox) {
            // Champ checkbox
            $input = new AF_Model_Input_Checkbox($inputSet, $component);
            if (isset($inputContent['value'])) {
                $input->setValue($inputContent['value']);
            }
        } elseif ($component instanceof AF_Model_Component_Select_Single) {
            // Champ de sélection simple
            $input = new AF_Model_Input_Select_Single($inputSet, $component);
            if (!empty($inputContent['value'])) {
                $option = $component->getOptionByRef($inputContent['value']);
                $input->setValue($option);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }
        } elseif ($component instanceof AF_Model_Component_Select_Multi) {
            // Champ de sélection multiple
            $input = new AF_Model_Input_Select_Multi($inputSet, $component);
            if (!empty($inputContent['value']) && is_array($inputContent['value'])) {
                $options = [];
                foreach ($inputContent['value'] as $optionRef) {
                    $options[] = $component->getOptionByRef($optionRef);
                }
                $input->setValue($options);
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }
        } elseif ($component instanceof AF_Model_Component_SubAF_NotRepeated) {
            // Sous-AF non répété
            $input = new AF_Model_Input_SubAF_NotRepeated($inputSet, $component);
            $errorMessages += $this->parseAfSubmit($inputContent['elements'],
                                                   $input->getValue(),
                                                   $component->getCalledAF());
        } elseif ($component instanceof AF_Model_Component_SubAF_Repeated) {
            // Sous-AF répété
            $input = new AF_Model_Input_SubAF_Repeated($inputSet, $component);
            foreach ($inputContent['elements'] as $ref => $elements) {
                list($number) = explode(UI_Generic::REF_SEPARATOR, strrev($ref), 2);
                $number = strrev($number);
                if (is_numeric($number)) {
                    $subInputSets = $input->getValue();
                    if (isset($subInputSets[$number])) {
                        // La répétition existe déjà
                        $subInputSet = $subInputSets[$number];
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->parseAfSubmit($elements['elements'],
                                                               $subInputSet,
                                                               $component->getCalledAF());
                    } else {
                        // On crée une nouvelle répétition
                        $subInputSet = new AF_Model_InputSet_Sub($component->getCalledAF());
                        // Free label
                        foreach ($elements as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->parseAfSubmit($elements,
                                                               $subInputSet,
                                                               $component->getCalledAF());
                        $input->addSubSet($subInputSet);
                    }
                }
            }
        } else {
            throw new InvalidArgumentException("Unknown component " . get_class($component));
        }
        // On enregistre les éléments communs à tout type d'élément
        if (isset($inputContent['hidden'])) {
            $input->setHidden($inputContent['hidden']);
        }
        if (isset($inputContent['disabled'])) {
            $input->setDisabled($inputContent['disabled']);
        }
        return $errorMessages;
    }

}
