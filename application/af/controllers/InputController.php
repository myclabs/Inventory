<?php
/**
 * @author  matthieu.napoli
 * @package AF
 */

use Core\Annotation\Secure;

/**
 * Saisie des AF
 * @package AF
 */
class AF_InputController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * Soumission d'un AF
     * AJAX
     * - id ID d'AF
     * - actionStack array() Liste d'actions ZF à appeler
     * @Secure("editInputAF")
     */
    public function submitAction()
    {
        /** @var AF_Service_InputService $inputService */
        $inputService = $this->get('AF_Service_InputService');

        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        $this->_setParam('af', $af);

        // InputSet
        if ($this->hasParam('idInputSet')) {
            /** @var $inputSet AF_Model_InputSet_Primary */
            $inputSet = AF_Model_InputSet_Primary::load($this->getParam('idInputSet'));
        } else {
            $inputSet = new AF_Model_InputSet_Primary($af);
        }
        $this->_setParam('inputSet', $inputSet);

        // Form data
        $formData = json_decode($this->getParam($af->getRef()), true);

        // MAJ l'InputSet
        $newValues = new AF_Model_InputSet_Primary($af);
        $errorMessages = $this->parseAfSubmit($formData, $newValues, $af);
        $inputService->editInputSet($inputSet, $newValues);

        // Réponse
        $response = [];
        $response['errorMessages'] = $errorMessages;

        if ($inputSet->isInputComplete()) {
            $response['type'] = UI_Message::TYPE_SUCCESS;
            $response['message'] = __('AF', 'inputInput', 'completeInputSaved');
        } else {
            $response['type'] = UI_Message::TYPE_SUCCESS;
            $response['message'] = __('AF', 'inputInput', 'incompleteInputSaved');
        }

        // Fait suivre aux actions de processing
        $actions = json_decode($this->getParam('actionStack'), true);
        // Fait suivre à la fin à l'action qui renvoie la réponse
        $actions[] = [
            'action'     => 'submit-send-response',
            'controller' => 'input',
            'module'     => 'af',
            'params'     => ['response' => $response],
        ];
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
        /** @var $inputSet AF_Model_InputSet_Primary */
        $inputSet = $this->getParam('inputSet');
        $response = $this->getParam('response');

        if (isset($response['errorMessages'])) {
            $this->addFormErrors($response['errorMessages']);
        }

        if (isset($response['message']) && isset($response['type'])) {
            $this->setFormMessage($response['message'], $response['type']);
        } elseif (isset($response['message'])) {
            $this->setFormMessage($response['message']);
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
        /** @var $sessionStorage AF_Service_InputSetSessionStorage */
        $sessionStorage = AF_Service_InputSetSessionStorage::getInstance();
        $sessionStorage->saveInputSet($this->getParam('af'), $this->getParam('inputSet'));
        $this->_helper->viewRenderer->setNoRender(true);
    }

    /**
     * Aperçu des résultats
     * AJAX
     * @Secure("editInputAF")
     */
    public function resultsPreviewAction()
    {
        /** @var AF_Service_InputService $inputService */
        $inputService = $this->get('AF_Service_InputService');

        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));

        // Crée une nouvelle saisie temporaire
        $inputSet = new AF_Model_InputSet_Primary($af);

        // Form data
        $formContent = json_decode($this->getParam($af->getRef()), true);

        // Remplit l'InputSet
        $newValues = new AF_Model_InputSet_Primary($af);
        $errorMessages = $this->parseAfSubmit($formContent, $newValues, $af);
        $inputService->editInputSet($inputSet, $newValues);

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
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        } else {
            /** @var $sessionStorage AF_Service_InputSetSessionStorage */
            $sessionStorage = AF_Service_InputSetSessionStorage::getInstance();
            // Récupère la saisie en session
            $inputSet = $sessionStorage->getInputSet($af, false);
            if ($inputSet === null) {
                throw new Core_Exception_User("AF", "message", "inputSetDoesntExist");
            }
            $inputSet->markAsFinished($this->getParam('value'));
            $sessionStorage->saveInputSet($af, $inputSet);
        }
        $this->sendJsonResponse(__("AF", "inputInput", "progressStatusUpdated"));

        $this->sendJsonResponse([
                                'message'    => __("AF", "inputInput", "progressStatusUpdated"),
                                'status'     => $inputSet->getStatus(),
                                'completion' => $inputSet->getCompletion(),
                                ]);
    }

    /**
     * Retourne un sous-af (pour ajouter un nouveau sous-af répété)
     * AJAX
     * @Secure("editInputAF")
     */
    public function getSubAfAction()
    {
        /** @var $af AF_Model_AF */
        $af = AF_Model_AF::load($this->getParam('id'));
        /** @var $component AF_Model_Component_SubAF_Repeated */
        $component = AF_Model_Component_SubAF_Repeated::loadByRef($this->getParam('refComponent'), $af);
        $generationHelper = new AF_GenerationHelper();
        $uiElement = $component->getSingleSubAFUIElement($generationHelper, $this->getParam('number'), null);
        $html = $uiElement->render() . "<script>" . $uiElement->getScript() . "</script>";
        $this->sendJsonResponse($html);
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
            $calcValue = new Calc_UnitValue();
            if ($inputContent['value'] !== '') {
                $value = str_replace(',', '.', $inputContent['value']);
                $relativeUncertainty = 0;
                if ($component->getWithUncertainty()) {
                    $childInputContent = current($inputContent['children']);
                    $relativeUncertainty = $childInputContent['value'];
                    if ($relativeUncertainty == '') {
                        $relativeUncertainty = 0;
                    }
                }
                if (!is_numeric($value)) {
                    $errorMessages[$fullRef] = __("UI", "formValidation", "invalidNumber");
                } elseif (!is_numeric($relativeUncertainty) || ($relativeUncertainty < 0)) {
                    $errorMessages[$fullRef] = __("UI", "formValidation", "invalidUncertainty");
                } else {
                    $calcValue->value->digitalValue = $value;
                    if ($component->getWithUncertainty()) {
                        $calcValue->value->relativeUncertainty = $relativeUncertainty;
                    }
                }
            } elseif ($component->getRequired()) {
                $errorMessages[$fullRef] = __("UI", "formValidation", "emptyRequiredField");
            }
            $calcValue->unit = $component->getUnit();
            $input->setValue($calcValue);
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
                        foreach ($elements['elements'] as $subRef => $subInputContent) {
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
                        foreach ($elements['elements'] as $subRef => $subInputContent) {
                            $refComponents = explode(UI_Generic::REF_SEPARATOR, $subRef);
                            if ($refComponents[count($refComponents) - 1] == 'freeLabel') {
                                $subInputSet->setFreeLabel($subInputContent['value']);
                                break;
                            }
                        }
                        $errorMessages += $this->parseAfSubmit($elements['elements'],
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
