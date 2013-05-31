<?php
/**
 * @author  matthieu.napoli
 * @package Techno
 */

use Core\Annotation\Secure;
use Doctrine\DBAL\DBALException;

/**
 * @package Techno
 */
class Techno_Datagrid_MeaningDatagridController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editTechno")
     */
    public function getelementsAction()
    {
        $query = ($this->request ? : new Core_Model_Query());
        $query->order->addOrder('position');
        $meanings = Techno_Model_Meaning::loadList($this->request);

        foreach ($meanings as $meaning) {
            /** @var $meaning Techno_Model_Meaning */
            $data = [];
            $data['index'] = $meaning->getId();
            $data['label'] = $meaning->getLabel();
            $data['ref'] = $meaning->getRef();
            // Seule les valeurs en erreur sont éditables
            $this->editableCell($data['ref'], false);
            try {
                $meaning->getKeyword();
            } catch (Core_Exception_NotFound $e) {
                $this->editableCell($data['ref'], true);
            }
            // Position
            $canMoveUp = ($meaning->getPosition() > 1);
            $canMoveDown = ($meaning->getPosition() < $meaning->getLastEligiblePosition());
            $data['position'] = $this->cellPosition($meaning->getPosition(), $canMoveUp, $canMoveDown);
            $this->addLine($data);
        }

        $this->totalElements = Techno_Model_Meaning::countTotal($this->request);
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
            $keyword = Keyword_Model_Keyword::loadByRef($refKeyword);
        } catch(Core_Exception_NotFound $e) {
            $this->setAddElementErrorMessage('ref', __('Techno', 'formValidation', 'unknownKeywordRef'));
        }
        try {
            Techno_Model_Meaning::loadByRef($refKeyword);
            $this->setAddElementErrorMessage('ref', __('Techno', 'meaning', 'cantInsertMeaning'));
        } catch(Core_Exception_NotFound $e) {
            // Le meanig n'existe pas déjà.
        }
        // Pas d'erreurs
        if (empty($this->_addErrorMessages)) {
            $meaning = new Techno_Model_Meaning();
            /** @noinspection PhpUndefinedVariableInspection */
            $meaning->setKeyword($keyword);
            try {
                $meaning->save();
                $entityManagers = Zend_Registry::get('EntityManagers');
                $entityManagers['default']->flush();
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
        /** @var $meaning Techno_Model_Meaning */
        $meaning = Techno_Model_Meaning::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'ref':
                try {
                    $keyword = Keyword_Model_Keyword::loadByRef($newValue);
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
        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
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
        /** @var $meaning Techno_Model_Meaning */
        $meaning = Techno_Model_Meaning::load($this->getParam('index'));
        try {
            $meaning->delete();
            $entityManagers = Zend_Registry::get('EntityManagers');
            $entityManagers['default']->flush();
        } catch (DBALException $e) {
            throw new Core_Exception_User('Techno', 'meaning', 'cantDeleteMeaning');
        }
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
