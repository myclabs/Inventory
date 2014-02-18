<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use AF\Domain\Algorithm\Numeric\NumericExpressionAlgo;
use Classification\Domain\ContextIndicator;
use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Parameter\Application\Service\ParameterService;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_NumericParameterController extends UI_Controller_Datagrid
{

    /**
     * @Inject
     * @var ParameterService
     */
    private $parameterService;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af \AF\Domain\AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof NumericParameterAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $algo->getLabel();
                try {
                    $data['family'] = $algo->getFamily()->getRef();
                } catch (Core_Exception_NotFound $e) {
                    // Si la famille n'existe plus
                    $data['family'] = $this->cellText(null, __('AF', 'configTreatmentInvalidRef', 'family'));
                }
                $data['coordinates'] = $this->cellPopup($this->_helper->url('popup-parameter-coordinates',
                                                                            'edit_algos',
                                                                            'af',
                                                                            ['idAF' => $af->getId(),
                                                                            'idAlgo' => $algo->getId()]),
                                                        __('Parameter', 'name', 'coordinates'),
                                                        'search');
                $contextIndicator = $algo->getContextIndicator();
                if ($contextIndicator) {
                    $ref = $contextIndicator->getContext()->getRef()
                        . "#" . $contextIndicator->getIndicator()->getRef();
                    $data['contextIndicator'] = $this->cellList($ref);
                }
                $data['resultIndex'] = $this->cellPopup($this->_helper->url('popup-indexation',
                                                                            'edit_algos',
                                                                            'af',
                                                                            ['id' => $algo->getId()]),
                                                        __('Algo', 'name', 'indexation'),
                                                        'search');
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af \AF\Domain\AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $familyRef = $this->getAddElementValue('family');
        if (empty($familyRef)) {
            $this->setAddElementErrorMessage('family', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $family = $this->parameterService->getFamily($familyRef);
        } catch (Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('family', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $algo = new NumericParameterAlgo();
            try {
                $algo->setRef($ref);
            } catch (Core_Exception_User $e) {
                $this->setAddElementErrorMessage('ref', $e->getMessage());
                $this->send();
                return;
            }
            $algo->setLabel($this->getAddElementValue('label'));
            /** @noinspection PhpUndefinedVariableInspection */
            $algo->setFamily($family);
            $algo->save();
            $af->addAlgo($algo);
            $af->save();
            try {
                $this->entityManager->flush();
            } catch (Core_ORM_DuplicateEntryException $e) {
                $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'alreadyUsedIdentifier'));
                $this->send();
                return;
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                $algo->setRef($newValue);
                $this->data = $algo->getRef();
                break;
            case 'label':
                $algo->setLabel($newValue);
                $this->data = $algo->getLabel();
                break;
            case 'family':
                try {
                    $family = $this->parameterService->getFamily($newValue);
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                $algo->setFamily($family);
                $this->data = $algo->getFamily()->getRef();
                break;
            case 'contextIndicator':
                if ($newValue) {
                    $contextIndicator = $this->getContextIndicatorByRef($newValue);
                    $algo->setContextIndicator($contextIndicator);
                } else {
                    $algo->setContextIndicator(null);
                }
                break;
        }
        $algo->save();
        try {
            $this->entityManager->flush();
        } catch (Core_ORM_DuplicateEntryException $e) {
            throw new Core_Exception_User('UI', 'formValidation', 'alreadyUsedIdentifier');
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        /** @var $algo NumericParameterAlgo */
        $algo = NumericParameterAlgo::load($this->getParam('index'));
        $algo->delete();
        $algo->getSet()->removeAlgo($algo);
        $algo->getSet()->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * Fonction permettant de récupérer la forme brute de l'expression
     * @Secure("editAF")
     */
    public function getExpressionAction()
    {
        /** @var $algo NumericExpressionAlgo */
        $algo = NumericExpressionAlgo::load($this->getParam('id'));
        $this->data = $algo->getExpression()->getExpression();
        $this->send();
    }

    /**
     * Renvoie la liste des contextIndicator
     * @Secure("editAF")
     */
    public function getContextIndicatorListAction()
    {
        $this->addElementList(null, '');
        /** @var $contextIndicators ContextIndicator[] */
        $contextIndicators = ContextIndicator::loadList();
        foreach ($contextIndicators as $contextIndicator) {
            $this->addElementList($this->getContextIndicatorRef($contextIndicator),
                                  $this->getContextIndicatorLabel($contextIndicator));
        }
        $this->send();
    }

    /**
     * @param ContextIndicator $contextIndicator
     * @return string
     */
    private function getContextIndicatorRef(ContextIndicator $contextIndicator)
    {
        return $contextIndicator->getContext()->getRef()
            . '#' . $contextIndicator->getIndicator()->getRef();
    }

    /**
     * @param string $ref
     * @return ContextIndicator
     */
    private function getContextIndicatorByRef($ref)
    {
        if (empty($ref)) {
            return null;
        }
        list($refContext, $refIndicator) = explode('#', $ref);
        return ContextIndicator::loadByRef($refContext, $refIndicator);
    }

    /**
     * @param ContextIndicator $contextIndicator
     * @return string
     */
    private function getContextIndicatorLabel(ContextIndicator $contextIndicator)
    {
        return $contextIndicator->getIndicator()->getLabel() . ' - ' . $contextIndicator->getContext()->getLabel();
    }

}
