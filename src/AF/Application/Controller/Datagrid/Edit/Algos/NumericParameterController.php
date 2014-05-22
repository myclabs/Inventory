<?php

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericParameterAlgo;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Parameter\Application\Service\ParameterService;
use Parameter\Domain\Family\FamilyReference;

/**
 * @author matthieu.napoli
 * @author hugo.charbonnier
 * @author thibaud.rolland
 */
class AF_Datagrid_Edit_Algos_NumericParameterController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var ParameterService
     */
    private $parameterService;

    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof NumericParameterAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $this->cellTranslatedText($algo->getLabel());

                try {
                    $data['family'] = (string) $algo->getFamilyReference();
                } catch (Core_Exception_NotFound $e) {
                    // Si la famille n'existe plus
                    $data['family'] = $this->cellText(null, __('AF', 'configTreatmentInvalidRef', 'family'));
                }

                $data['coordinates'] = $this->cellPopup($this->_helper->url(
                    'popup-parameter-coordinates',
                    'edit_algos',
                    'af',
                    ['idAF'   => $af->getId(), 'idAlgo' => $algo->getId()]
                ), __('Parameter', 'name', 'coordinates'), 'search');

                $contextIndicator = $algo->getContextIndicator();
                if ($contextIndicator) {
                    $data['contextIndicator'] = $this->cellList($contextIndicator->getId());
                }
                $data['resultIndex'] = $this->cellPopup(
                    $this->_helper->url('popup-indexation', 'edit_algos', 'af', [
                        'idAF' => $af->getId(),
                        'algo' => $algo->getId(),
                    ]),
                    '<i class="fa fa-search-plus"></i> ' . __('Algo', 'name', 'indexation')
                );
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function addelementAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $ref = $this->getAddElementValue('ref');
        if (empty($ref)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (empty($this->getAddElementValue('family'))) {
            $this->setAddElementErrorMessage('family', __('UI', 'formValidation', 'emptyRequiredField'));
        } else {
            try {
                $familyReference = FamilyReference::fromString($this->getAddElementValue('family'));
                $family = $this->parameterService->getFamily($familyReference);
            } catch (Core_Exception_NotFound $e) {
                $this->setAddElementErrorMessage('family', __('UI', 'formValidation', 'emptyRequiredField'));
            }
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
            $this->translator->set($algo->getLabel(), $this->getAddElementValue('label'));
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
                $this->translator->set($algo->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($algo->getLabel());
                break;
            case 'family':
                try {
                    $familyReference = FamilyReference::fromString($newValue);
                    $family = $this->parameterService->getFamily($familyReference);
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('UI', 'formValidation', 'emptyRequiredField');
                }
                $algo->setFamily($family);
                $this->data = $algo->getFamily()->getRef();
                break;
            case 'contextIndicator':
                if ($newValue) {
                    $contextIndicator = ContextIndicator::load($newValue);
                    $algo->setContextIndicator($contextIndicator);
                    $this->data = $this->cellList($contextIndicator->getId());
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
     * Renvoie la liste des contextIndicator
     * @Secure("editAF")
     */
    public function getContextIndicatorListAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $classificationLibraries = ClassificationLibrary::loadUsableInAccount($af->getLibrary()->getAccount());

        $this->addElementList(null, '');

        foreach ($classificationLibraries as $library) {
            foreach ($library->getContextIndicators() as $contextIndicator) {
                $this->addElementList(
                    $contextIndicator->getId(),
                    $this->translator->get($library->getLabel()) . ' > '
                    . $this->translator->get($contextIndicator->getLabel())
                );
            }
        }

        $this->send();
    }
}
