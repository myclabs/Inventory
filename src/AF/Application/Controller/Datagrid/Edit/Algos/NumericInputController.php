<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Algorithm\Numeric\NumericInputAlgo;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\ContextIndicator;
use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_NumericInputController extends UI_Controller_Datagrid
{

    /**
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof NumericInputAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                $data['label'] = $this->cellTranslatedText($algo->getLabel());
                $data['input'] = $algo->getInputRef();
                $data['unit'] = $this->cellText($algo->getUnit()->getRef(), $algo->getUnit()->getSymbol());
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
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

    /**
     * @Secure("editAF")
     */
    public function updateelementAction()
    {
        /** @var $algo NumericInputAlgo */
        $algo = NumericInputAlgo::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $this->translationHelper->set($algo->getLabel(), $newValue);
                $this->data = $this->cellTranslatedText($algo->getLabel());
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
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * @Secure("editAF")
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
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
                    $this->translationHelper->toString($library->getLabel()) . ' > '
                    . $this->translationHelper->toString($contextIndicator->getLabel())
                );
            }
        }

        $this->send();
    }
}
