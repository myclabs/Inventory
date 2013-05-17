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
class Social_ActionController extends Core_Controller_Ajax
{

    use UI_Controller_Helper_Form;

    /**
     * Thèmes d'action
     * @Secure("adminThemes")
     */
    public function themesAction()
    {
    }

    /**
     * Modèles d'action
     * @Secure("viewGenericActions")
     */
    public function genericActionsAction()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Theme::QUERY_LABEL);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->themes = Social_Model_Theme::loadList($query);
    }

    /**
     * Détails d'un modèle d'action
     * - id ID de l'action
     * - title (optionnel) Titre de la page
     * - returnUrl (optionnel) URL du bouton retour
     * @Secure("viewGenericAction")
     */
    public function genericActionDetailsAction()
    {
        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->_getParam('id'));
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->genericAction = $genericAction;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->comments = $genericAction->getComments();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->title = $this->_getParam('title');
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->returnUrl = $this->_getParam('returnUrl');
    }

    /**
     * Soumission du formulaire de modification d'un modèle d'action
     * - Ajax
     * @Secure("editGenericAction")
     */
    public function genericActionUpdateAction()
    {
        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->_getParam('id'));
        $formData = $this->getFormData('editGenericAction');

        $idTheme = $formData->getValue('theme');
        if (empty($idTheme)) {
            $this->addFormError('theme', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        /** @var $theme Social_Model_Theme */
        $theme = Social_Model_Theme::load($idTheme);
        $label = $formData->getValue('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $description = $formData->getValue('description');

        if (!$this->hasFormError()) {
            $genericAction->setTheme($theme);
            $genericAction->setLabel($label);
            $genericAction->setDescription($description);
            $this->setFormMessage(__('UI', 'message', 'updated'));
        }
        $this->sendFormResponse();
    }

    /**
     * Ajout d'un commentaire sur un modèle action
     * - Ajax
     * @Secure("commentGenericAction")
     */
    public function genericActionAddCommentAction()
    {
        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($this->_getParam('id'));
        $author = $this->_helper->auth();
        $formData = $this->getFormData('addComment');

        $content = $formData->getValue('content');
        if (empty($content)) {
            $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = new Social_Model_Comment($author);
            $comment->setText($content);
            $comment->save();
            $genericAction->addComment($comment);
            $genericAction->save();

            // Retourne la vue du commentaire
            $this->_forward('comment-added', 'comment', null, ['comment' => $comment]);
            return;
        }
        $this->sendFormResponse();
    }

    /**
     * Popup qui affiche la description d'une action
     * @Secure("viewContextAction")
     */
    public function popupActionDescriptionAction()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->action = Social_Model_Action::load($this->_getParam('id'));
        $this->_helper->layout()->disableLayout();
    }

    /**
     * Liste des actions contextualisées
     * @Secure("viewContextActions")
     */
    public function contextActionsAction()
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_Theme::QUERY_LABEL);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->themes = Social_Model_Theme::loadList($query);
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->genericActions = Social_Model_GenericAction::loadList();
    }

    /**
     * Détails d'une action
     * - id ID de l'action
     * - title (optionnel) Titre de la page
     * - returnUrl (optionnel) URL du bouton retour
     * @Secure("viewContextAction")
     */
    public function contextActionDetailsAction()
    {
        /** @var $action Social_Model_ContextAction */
        $action = Social_Model_ContextAction::load($this->_getParam('id'));
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->contextAction = $action;
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->comments = $action->getComments();
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->title = $this->_getParam('title');
        /** @noinspection PhpUndefinedFieldInspection */
        $this->view->returnUrl = $this->_getParam('returnUrl');
    }

    /**
     * Soumission du formulaire de modification d'une action
     * - Ajax
     * @Secure("editContextActions")
     */
    public function contextActionUpdateAction()
    {
        $locale = Core_Locale::loadDefault();
        /** @var $contextAction Social_Model_ContextAction */
        $contextAction = Social_Model_ContextAction::load($this->_getParam('id'));
        $formData = $this->getFormData('editContextAction');

        // Validation
        $idGenericAction = $formData->getValue('genericAction');
        if (empty($idGenericAction)) {
            $this->addFormError('genericAction', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $targetDate = $formData->getValue('targetDate');
        if (empty($targetDate)) {
            $targetDate = null;
        } else {
            try {
                $targetDate = $locale->parseDate($targetDate);
            } catch (Exception $e) {
                $this->addFormError('targetDate', __('UI', 'formValidation', 'invalidDate'));
            }
        }
        $launchDate = $formData->getValue('launchDate');
        if (empty($launchDate)) {
            $launchDate = null;
        } else {
            try {
                $launchDate = $locale->parseDate($launchDate);
            } catch (Core_Exception_InvalidArgument $e) {
                $this->addFormError('launchDate', __('UI', 'formValidation', 'invalidDate'));
            }
        }

        /** @var $genericAction Social_Model_GenericAction */
        $genericAction = Social_Model_GenericAction::load($idGenericAction);
        $label = $formData->getValue('label');
        if (empty($label)) {
            $this->addFormError('label', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        $description = $formData->getValue('description');

        if (!$this->hasFormError()) {
            $contextAction->setGenericAction($genericAction);
            $contextAction->setLabel($label);
            $contextAction->setPersonInCharge($formData->getValue('personInCharge'));
            $contextAction->setTargetDate($targetDate);
            $contextAction->setLaunchDate($launchDate);
            $contextAction->setProgress($formData->getValue('progress'));
            $contextAction->setDescription($description);

            // Key figures
            foreach ($contextAction->getKeyFigures() as $contextActionKeyFigure) {
                $keyFigure = $contextActionKeyFigure->getActionKeyFigure();
                $value = $formData->getValue($keyFigure->getId());
                if ($value === '') {
                    $contextActionKeyFigure->setValue(null);
                } else {
                    $contextActionKeyFigure->setValue($value);
                }
                $contextActionKeyFigure->save();
            }

            $this->setFormMessage(__('UI', 'message', 'updated'));
        }
        $this->sendFormResponse();
    }

    /**
     * Ajout d'un commentaire sur une action
     * - Ajax
     * @Secure("commentContextAction")
     */
    public function contextActionAddCommentAction()
    {
        /** @var $contextAction Social_Model_ContextAction */
        $contextAction = Social_Model_ContextAction::load($this->_getParam('id'));
        $author = $this->_helper->auth();
        $formData = $this->getFormData('addComment');

        $content = $formData->getValue('content');
        if (empty($content)) {
            $this->addFormError('content', __('UI', 'formValidation', 'emptyRequiredField'));
        }
        if (!$this->hasFormError()) {

            // Ajoute le commentaire
            $comment = new Social_Model_Comment($author);
            $comment->setText($content);
            $comment->save();
            $contextAction->addComment($comment);
            $contextAction->save();

            // Retourne la vue du commentaire
            $this->_forward('comment-added', 'comment', null, ['comment' => $comment]);
            return;
        }
        $this->sendFormResponse();
    }

}
