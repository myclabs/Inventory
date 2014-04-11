<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @author  guillaume.querat
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\AFConfigurationValidator;
use AF\Domain\Component\NumericField;
use AF\Domain\Component\Field;
use AF\Domain\Condition\Condition;
use AF\Domain\Component\Component;
use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Parameter\Domain\ParameterLibrary;
use TEC\Exception\InvalidExpressionException;

/**
 * CompleteEdition Controller
 * @package AF
 */
class AF_EditController extends Core_Controller
{

    use UI_Controller_Helper_Form;

    /**
     * @Inject
     * @var AFConfigurationValidator
     */
    private $afConfigurationValidator;

    /**
     * Permet l'affichage du menu dans un tabView avec différents onglets
     * @Secure("editAF")
     */
    public function menuAction()
    {
        $af = AF::load($this->getParam('id'));
        $this->view->assign('af', $af);
        $this->view->onglet = $this->getParam('onglet');
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
        $this->setActiveMenuItemAFLibrary($af->getLibrary()->getId());
    }

    /**
     * Onglet "Général": Formulaire d'édition des infos générales
     * AJAX
     * @Secure("editAF")
     */
    public function generalTabAction()
    {
        $this->view->af = AF::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Soumission du formulaire des infos générales
     * @Secure("editAF")
     */
    public function generalSubmitAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        if ($this->getRequest()->isPost()) {
            $ref = $this->getParam('ref');
            if (empty($ref)) {
                $this->addFormError('ref', __('UI', 'formValidation', 'emptyRequiredField'));
                $this->sendFormResponse();
                return;
            } else {
                try {
                    $af->setRef($ref);
                } catch (Core_Exception_User $e) {
                    $this->addFormError('ref', __('Core', 'exception', 'unauthorizedRef'));
                    $this->sendFormResponse();
                    return;
                }
            }
            $label = $this->getParam('label');
            if (empty($label)) {
                $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
                $this->sendFormResponse();
                return;
            }
            $af->setLabel($label);
            $af->setDocumentation($this->getParam('documentation'));
            $af->save();
            try {
                $this->entityManager->flush();
                $this->setFormMessage(__('UI', 'message', 'updated'));
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->addFormError('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
            }
        }

        $this->sendFormResponse();
    }

    /**
     * Onglet "Structure" d'un AF
     * AJAX
     * @Secure("editAF")
     */
    public function structureAction()
    {
        $this->view->af = AF::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Onglet "Composants" d'un AF
     * AJAX
     * @Secure("editAF")
     */
    public function componentsAction()
    {
        $this->view->af = AF::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Onglet "Interactions" d'un AF
     * AJAX
     * @Secure("editAF")
     */
    public function interactionsAction()
    {
        $this->view->af = AF::load($this->getParam('id'));
        // Composants
        $query = new Core_Model_Query();
        $query->filter->addCondition(Component::QUERY_AF, $this->view->af);
        $this->view->componentList = Component::loadList($query);
        // Conditions
        $query = new Core_Model_Query();
        $query->filter->addCondition(Condition::QUERY_AF, $this->view->af);
        $this->view->conditionList = Condition::loadList($query);
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Onglet "Traitement" d'un AF
     * AJAX
     * @Secure("editAF")
     */
    public function traitementAction()
    {
        $af = AF::load($this->getParam('id'));

        // Récupère les familles que l'ont peut utiliser
        $libraries = ParameterLibrary::loadUsableInAccount($af->getLibrary()->getAccount());

        $this->view->assign('parameterLibraries', $libraries);
        $this->view->assign('af', $af);

        // Composants
        $query = new Core_Model_Query();
        $query->filter->addCondition(Field::QUERY_AF, $this->view->af);
        $this->view->fieldList = Field::loadList($query);

        // Composants numériques
        $query = new Core_Model_Query();
        $query->filter->addCondition(NumericField::QUERY_AF, $this->view->af);
        $this->view->numericInputList = NumericField::loadList($query);
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Modification de l'algorithme principal d'un AF
     * @Secure("editAF")
     */
    public function algoMainSubmitAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));

        if ($this->getRequest()->isPost()) {
            try {
                $af->getMainAlgo()->setExpression(trim($this->getParam('expression')));
            } catch (InvalidExpressionException $e) {
                $message = __('AF', 'configTreatmentMessage', 'invalidExpression')
                    . "<br>" . implode("<br>", $e->getErrors());
                $this->addFormError('expression', $message);
            }
            if (!$this->hasFormError()) {
                $af->save();
                $this->entityManager->flush();
                $this->setFormMessage(__('UI', 'message', 'updated'));
            }
        }
        $this->sendFormResponse();
    }

    /**
     * Onglet controle
     * @Secure("editAF")
     */
    public function controlAction()
    {
        $this->view->af = AF::load($this->getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Retourne la partie "résultats" du contrôle d'un AF
     * @Secure("editAF")
     */
    public function controlResultsAction()
    {
        $this->view->af = AF::load($this->getParam('id'));

        $this->view->errors = $this->afConfigurationValidator->validateAF($this->view->af);
        $this->_helper->layout()->disableLayout();
    }

}
