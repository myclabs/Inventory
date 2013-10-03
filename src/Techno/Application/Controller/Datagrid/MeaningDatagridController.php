<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use Doctrine\DBAL\DBALException;
use Keyword\Application\Service\KeywordService;
use Techno\Domain\Meaning;

/**
 * @package Techno
 */
class Techno_Datagrid_MeaningDatagridController extends UI_Controller_Datagrid
{
    /**
     * @Inject
     * @var KeywordService
     */
    protected $keywordService;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        $query = ($this->request ? : new Core_Model_Query());
        $query->order->addOrder('position');
        $meanings = Meaning::loadList($this->request);

        foreach ($meanings as $meaning) {
            /** @var $meaning Meaning */
            $data = [];
            $data['index'] = $meaning->getId();
            $data['label'] = $meaning->getLabel();
            $data['ref'] = $meaning->getRef();
            // Seule les valeurs en erreur sont éditables
            $this->editableCell($data['ref'], false);
            if ($this->keywordService->exists($meaning->getKeyword())) {
                $this->editableCell($data['ref'], true);
            }
            // Position
            $canMoveUp = ($meaning->getPosition() > 1);
            $canMoveDown = ($meaning->getPosition() < $meaning->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($meaning->getPosition(), $canMoveUp, $canMoveDown);
            $this->addLine($data);
        }

        $this->totalElements = Meaning::countTotal($this->request);
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     * @Secure("editTechno")
     */
    public function addelementAction()
    {
        // Validation du formulaire
        $refKeyword = $this->getAddElementValue('ref');
        if (empty($refKeyword)) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            $keyword = $this->keywordService->get($refKeyword);
        } catch(Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('ref', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        try {
            Meaning::loadByRef($refKeyword);
            $this->setAddElementErrorMessage('ref', __('Techno', 'meaning', 'cantInsertMeaning'));
        } catch(Core_Exception_NotFound $e) {
            // Le meanig n'existe pas déjà.
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $meaning = new Meaning();
            /** @noinspection PhpUndefinedVariableInspection */
            $meaning->setKeyword($keyword);
            try {
                $meaning->save();
                $this->entityManager->flush();
            } catch (DBALException $e) {
                throw new Core_Exception_User('Techno', 'meaning', 'cantInsertMeaning');
            }
            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     * @Secure("editTechno")
     */
    public function updateelementAction()
    {
        /** @var $meaning Meaning */
        $meaning = Meaning::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                try {
                    $keyword = $this->keywordService->get($newValue);
                    $meaning->setKeyword($keyword);
                    $this->data = $keyword->getRef();
                } catch (Core_Exception_NotFound $e) {
                    throw new Core_Exception_User('Techno', 'formValidation', 'unknownKeywordRef');
                }
                break;
            case 'position':
                $oldPosition = $meaning->getPosition();
                switch ($newValue) {
                    case 'goFirst':
                        $newPosition = 1;
                        break;
                    case 'goUp':
                        $newPosition = $oldPosition - 1;
                        break;
                    case 'goDown':
                        $newPosition = $oldPosition + 1;
                        break;
                    case 'goLast':
                        $newPosition = $meaning->getLastEligiblePosition();
                        break;
                    default:
                        $newPosition = $newValue;
                        break;
                }
                $meaning->setPosition($newPosition);
                break;
        }
        $meaning->save();
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     * @Secure("editTechno")
     */
    public function deleteelementAction()
    {
        /** @var $meaning Meaning */
        $meaning = Meaning::load($this->getParam('index'));
        try {
            $meaning->delete();
            $this->entityManager->flush();
        } catch (DBALException $e) {
            throw new Core_Exception_User('Techno', 'meaning', 'cantDeleteMeaning');
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
