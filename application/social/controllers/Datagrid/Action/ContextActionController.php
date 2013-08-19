<?php
/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;

/**
 * @package Social
 */
class Social_Datagrid_Action_ContextActionController extends UI_Controller_Datagrid
{

    /**
     * {@inheritdoc}
     * @Secure("viewContextActions")
     */
    public function getelementsAction()
    {
        $this->request->order->addOrder(Social_Model_ContextAction::QUERY_GENERIC_ACTION);
        $this->request->order->addOrder(Social_Model_ContextAction::QUERY_LABEL);
        /** @var $contextActions Social_Model_ContextAction[] */
        $contextActions = Social_Model_ContextAction::loadList($this->request);
        $this->totalElements = Social_Model_ContextAction::countTotal($this->request);

        foreach ($contextActions as $contextAction) {
            $data = [];

            $genericAction = $contextAction->getGenericAction();
            $theme = $genericAction->getTheme();

            $data['index'] = $contextAction->getId();
            $data['theme'] = $this->cellList($theme->getId());
            $data['genericAction'] = $this->cellList($genericAction->getId());
            $data['label'] = $this->cellText($contextAction->getLabel());
            $data['personInCharge'] = $this->cellText($contextAction->getPersonInCharge());
            if ($contextAction->getLaunchDate() != null) {
                $data['launchDate'] = $this->cellDate($contextAction->getLaunchDate());
            }
            if ($contextAction->getTargetDate() != null) {
                $data['targetDate'] = $this->cellDate($contextAction->getTargetDate());
            }
            $data['progress'] = $this->cellList($contextAction->getProgress());
            $data['description'] = $this->cellLongText('social/action/popup-action-description/id/'
                                                           . $contextAction->getId(),
                                                           null,
                                                           __('UI', 'name', 'description'),
                                                           'zoom-in');
            $data['details'] = $this->cellLink('social/action/context-action-details/id/' . $contextAction->getId(),
                                               __('UI', 'name', 'details'), 'share-alt');

            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("addContextAction")
     */
    public function addelementAction()
    {
        $locale = Core_Locale::loadDefault();

        $idGenericAction = $this->getAddElementValue('genericAction');
        if (empty($idGenericAction)) {
            $this->setAddElementErrorMessage('genericAction', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        $label = $this->getAddElementValue('label');
        if (empty($label)) {
            $this->setAddElementErrorMessage('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        $targetDate = $this->getAddElementValue('targetDate');
        if (empty($targetDate)) {
            $targetDate = null;
        } else {
            try {
                $targetDate = $locale->parseDate($targetDate);
            } catch (Exception $e) {
                $this->setAddElementErrorMessage('targetDate', __('UI', 'formValidation', 'invalidDate'));
            }
        }

        $launchDate = $this->getAddElementValue('launchDate');
        if (empty($launchDate)) {
            $launchDate = null;
        } else {
            try {
                $launchDate = $locale->parseDate($launchDate);
            } catch (Core_Exception_InvalidArgument $e) {
                $this->setAddElementErrorMessage('launchDate', __('UI', 'formValidation', 'invalidDate'));
            }
        }

        $progress = $this->getAddElementValue('progress');
        if (empty($progress)) {
            $this->setAddElementErrorMessage('progress', __('UI', 'formValidation', 'emptyRequiredField'));
        }

        if (count($this->_addErrorMessages) === 0) {
            /** @var $genericAction Social_Model_GenericAction */
            $genericAction = Social_Model_GenericAction::load($idGenericAction);

            $contextAction = new Social_Model_ContextAction($genericAction);
            $contextAction->setLabel($label);
            $contextAction->setDescription($this->getAddElementValue('description'));
            $contextAction->setPersonInCharge($this->getAddElementValue('personInCharge'));
            $contextAction->setTargetDate($targetDate);
            $contextAction->setLaunchDate($launchDate);
            $contextAction->setProgress($progress);
            $contextAction->save();

            $this->entityManager->flush();

            /** @var $actionKeyFigures Social_Model_ActionKeyFigure[] */
            $actionKeyFigures = Social_Model_ActionKeyFigure::loadList();
            foreach ($actionKeyFigures as $actionKeyFigure) {
                $assoc = new Social_Model_ContextActionKeyFigure($actionKeyFigure, $contextAction);
                $assoc->save();
                $contextAction->addKeyFigure($assoc);
            }
            $contextAction->save();

            $this->entityManager->flush();

            $this->message = __('UI', 'message', 'added');
        }
        $this->send();
    }

    /**
     * {@inheritdoc}
     * @Secure("editContextAction")
     */
    public function updateelementAction()
    {
        /** @var $action Social_Model_ContextAction */
        $action = Social_Model_ContextAction::load($this->update['index']);
        $newValue = $this->update['value'];
        switch ($this->update['column']) {
            case 'label':
                $action->setLabel($newValue);
                $this->data = $action->getLabel();
                break;
        }
        $this->message = __('UI', 'message', 'updated');
        $this->send();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteelementAction()
    {
        /** @var $contextAction Social_Model_ContextAction */
        $contextAction = Social_Model_ContextAction::load($this->delete);

        $contextAction->delete();

        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

}
