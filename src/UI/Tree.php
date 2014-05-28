<?php
use AF\Application\Form\Element\Option;
use AF\Application\Form\Element\Select;
use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use MyCLabs\MUIH\Icon;
use MyCLabs\MUIH\Modal;

/**
 * Fichier de la classe Tree.
 *
 * @package    UI
 * @subpackage Tree
 */

/**
 * Description of Tree.
 *
 * Une classe permettant de génèrer un arbre très simplement.
 *
 * @author     valentin.claras
 */
class UI_Tree extends UI_Generic
{
    /**
     * Définition du titre affiché dans le popup d'édition d'un node.
     *
     * @var   string
     */
    public $editPanelTitle = null;

    /**
     * Définition de l'icône affichée dans le bouton de validation du popup d'édition.
     *
     * @var   string
     */
    public $editPanelConfirmIcon = null;

    /**
     * Définition de l'icône affichée dans le bouton d'annulation du popup d'édition.
     *
     * @var   string
     */
    public $editPanelCancelIcon = null;

    /**
     * Définition du label affiché dans le bouton de validation du popup d'édition.
     *
     * @var   string
     */
    public $editPanelConfirmLabel = null;

    /**
     * Définition du label affiché dans le bouton d'annulation du popup d'édition.
     *
     * @var   string
     */
    public $editPanelCancelLabel = null;

    /**
     * Définition du texte affiché dans le select de modification du parent.
     *
     * @var   string
     */
    public $changeParentLabel = null;

    /**
     * Définition du message affiché dans la liste des options de parents lors de son chargement.
     *
     * @var   string
     */
    public $changeParentLoadingOption = null;

    /**
     * Définition du label affiché dans l'option de sélection du changement de position.
     *
     * @var   string
     */
    public $changeOrderLabel = null;

    /**
     * Définition du texte affiché dans l'option de positionnement en premier.
     *
     * @var   string
     */
    public $changeOrderFirstLabel = null;

    /**
     * Définition du texte affiché dans l'option de positionnement en fin.
     *
     * @var   string
     */
    public $changeOrderLastLabel = null;

    /**
     * Définition du texte affiché dans l'option de positionnement après un élément.
     *
     * @var   string
     */
    public $changeOrderAfterLabel = null;

    /**
     * Définition du message affiché dans la liste des options de position lors de son chargement.
     *
     * @var   string
     */
    public $changeOrderLoadingOption = null;

    /**
     * Définition de l'icône affichée dans le bouton faisant apparaître le popup de suppression.
     *
     * @var   string
     */
    public $deleteButtonIcon = null;

    /**
     * Définition du label affiché dans le bouton faisant apparaître le popup de suppression.
     *
     * @var   string
     */
    public $deleteButtonLabel = null;

    /**
     * Définition du titre affiché dans le popup de suppression.
     *
     * @var   string
     */
    public $deletePanelTitle = null;

    /**
     * Définition du message affiché dans le popup de suppression.
     *
     * @var   string
     */
    public $deletePanelText = null;

    /**
     * Définition de l'icône affichée dans le bouton de validation du popup de suppression.
     *
     * @var   string
     */
    public $deletePanelConfirmIcon = null;

    /**
     * Définition de l'icône affichée dans le bouton d'annulation du popup de suppression.
     *
     * @var   string
     */
    public $deletePanelCancelIcon = null;

    /**
     * Définition du label affiché dans le bouton de validation du popup de suppression.
     *
     * @var   string
     */
    public $deletePanelConfirmLabel = null;

    /**
     * Définition du label affiché dans le bouton d'annulation du popup de suppression.
     *
     * @var   string
     */
    public $deletePanelCancelLabel = null;

    /**
     * Définition de l'icône affichée dans le bouton faisant apparaître le popup d'ajout.
     *
     * @var   string
     */
    public $addButtonIcon = null;

    /**
     * Définition du label affiché dans le bouton faisant apparaître le popup d'ajout.
     *
     * @var   string
     */
    public $addButtonLabel = null;

    /**
     * Définition du titre affiché dans le popup d'ajout.
     *
     * @var   string
     */
    public $addPanelTitle = null;

    /**
     * Définition du formulaire affiché dans le popup d'ajout. Null = Error.
     *
     * @var   GenericTag
     */
    public $addPanelForm = null;

    /**
     * Définition de l'icône affichée dans le bouton de validation du popup d'ajout.
     *
     * @var   string
     */
    public $addPanelConfirmIcon = null;

    /**
     * Définition de l'icône affichée dans le bouton d'annulation du popup d'ajout.
     *
     * @var   string
     */
    public $addPanelCancelIcon = null;

    /**
     * Définition du label affiché dans le bouton de validation du popup d'ajout.
     *
     * @var   string
     */
    public $addPanelConfirmLabel = null;

    /**
     * Définition du label affiché dans le bouton d'annulation du popup d'ajout.
     *
     * @var   string
     */
    public $addPanelCancelLabel = null;

    /**
     * Définition du label affiché pour déplier l'ensemble de l'arbre.
     *
     * @var   string
     */
    public $labelExpandAll = null;

    /**
     * Définition du label affiché pour plier l'ensemble de l'arbre.
     *
     * @var   string
     */
    public $labelCollapseAll = null;


    /**
     * Identifiant unique du Tree.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Nom du contrôleur ou le tree récuperera les données.
     *
     * @var   string
     */
    protected $_controller = null;

    /**
     * Nom (optionnel) du module ou le tree récuperera les données.
     *
     * @var   string
     */
    protected $_module = null;

    /**
     * Tableau d'attributs optionnels qui seront envoyés au controleur.
     *
     * @var   array
     */
    protected $_parameters = array();

    /**
     * Permet de savoir si l'ajout d'éléments est présent dans le tree.
     *
     * Par défaut non actif.
     *
     * @var   bool
     *
     * @see setAddNode
     */
    public $addNode = false;

    /**
     * Tableau de paramètres définissant l'édition des nodes dans le tree.
     *
     * Par défaut non actif.
     *
     * @var   array
     *
     * @see setEditNode
     */
    protected $_editNode = array();

    /**
     * Tableau de paramètres définissant la suppression des nodes dans le tree.
     *
     * Par défaut non actif.
     *
     * @var   bool
     *
     * @see setDeleteNode
     */
    public $deleteNode = false;

    /**
     * Booléen qui définit si il est possible de déplier l'arbre d'un seul coup.
     *
     * Par défaut true.
     *
     * @var   bool
     */
    public $expandAll = true;

    /**
     * Booléen qui définit si il est possible de replier l'arbre d'un seul coup.
     *
     * Par défaut true.
     *
     * @var   bool
     */
    public $collapseAll = true;


    /**
     * Constructeur de la classe Tree
     *
     * @param string $id         Identifiant unique du tree.
     * @param string $controleur Nom du contrôleur du tree.
     * @param string $module     Module ou se trouve le controleur du tree.
     */
    public function  __construct($id, $controleur, $module=null)
    {
        $this->id = $id;
        $this->_controller = $controleur;
        $this->_module = $module;

        $this->_editNode['state'] = false;
        $this->_editNode['parent'] = false;
        $this->_editNode['order'] = false;
        $this->_editNode['custom'] = false;

        // Popup d'édition
        $this->editPanelTitle = __('UI', 'name', 'edition');
        $this->editPanelConfirmIcon = 'check';
        $this->editPanelConfirmLabel = __('UI', 'verb', 'confirm');
        $this->editPanelCancelIcon = 'times';
        $this->editPanelCancelLabel = __('UI', 'verb', 'cancel');
        $this->changeParentLoadingOption = __('UI', 'loading', 'loading');
        $this->changeParentLabel = __('UI', 'name', 'parent');
        $this->changeOrderLabel = __('UI', 'name', 'position');
        $this->changeOrderFirstLabel = __('UI', 'other', 'first');
        $this->changeOrderLastLabel = __('UI', 'other', 'last');
        $this->changeOrderAfterLabel = __('UI', 'other', 'after');
        $this->changeOrderLoadingOption = __('UI', 'loading', 'loading');

        // Bouton et popup de confirmation de suppression
        $this->deleteButtonLabel = __('UI', 'verb', 'delete');
        $this->deleteButtonIcon = 'trash-o';
        $this->deletePanelTitle = __('UI', 'deletionConfirmationPopup', 'title');
        $this->deletePanelText = __('UI', 'deletionConfirmationPopup', 'text');
        $this->deletePanelConfirmIcon = 'check';
        $this->deletePanelConfirmLabel = __('UI', 'verb', 'confirm');
        $this->deletePanelCancelIcon = 'remove';
        $this->deletePanelCancelLabel = __('UI', 'verb', 'cancel');

        // Bouton et popup d'ajout
        $this->addButtonLabel = __('UI', 'verb', 'add');
        $this->addButtonIcon = 'plus-circle';
        $this->addPanelTitle = __('UI', 'name', 'addition');
        $this->addPanelConfirmLabel = __('UI', 'verb', 'validate');
        $this->addPanelConfirmIcon = 'check';
        $this->addPanelCancelLabel = __('UI', 'verb', 'cancel');
        $this->addPanelCancelIcon = 'times';

        // Tout déplier / Tout replier
        $this->labelExpandAll = __('UI', 'verb', 'expandAll');
        $this->labelCollapseAll = __('UI', 'verb', 'collapseAll');
    }

    /**
     * Fonction que permet d'ajouter des paramètres au tree.
     *
     * Ces paramètres seront envoyés par l'url à chaque requête.
     *
     * @param string $parameterName
     * @param mixed  $parameterValue
     *
     * @return void
     */
    public function addParam($parameterName, $parameterValue)
    {
        $this->_parameters[$parameterName] = $parameterValue;
    }

    /**
     * Fonction qui active la possibilité d'éditer les nodes du Tree.
     *
     * @param bool                $editParent True par défaut
     * @param bool                $editOrder  True par défaut
     * @param Zend_Form_Element[] $customEdit
     *
     * @return void
     */
    public function setEditNode($editParent=true, $editOrder=true, $customEdit=array())
    {
        $this->_editNode['state'] = true;
        $this->_editNode['parent'] = $editParent;
        $this->_editNode['order'] = $editOrder;
        $this->_editNode['custom'] = $customEdit;
    }

    /**
     * Fonction qui renvoie l'url du controleur avec le module.
     *
     * @return string
     */
    protected function encodeUrl()
    {
        $url = '';
        // Ajout du nom du module.
        if ($this->_module !== null) {
            $url .= $this->_module.'/';
        }
        // Ajout du contrôleur.
        $url .= $this->_controller.'/';

        return $url;
    }

    /**
     * Fonction qui renvoie les paramètres optionnels pour la requête.
     *
     * @return string
     */
    public function encodeParameters()
    {
        $url = '';

        $url .= 'idTree=' . $this->id . '&';
        foreach ($this->_parameters as $option => $valeur) {
            $url .= $option . '=' . addslashes($valeur) . '&';
        }

        return $url;
    }

    /**
     * Fonction qui renvoie une url complète pour une action donnée.
     *
     * @param string $action
     *
     * @return string
     */
    public function getActionUrl($action)
    {
        return $this->encodeUrl() . $action . '?' . $this->encodeParameters();
    }


    /**
     * Fonction qui génère le code HTLM permettant de plier / déplier l'ensemble de l'arbre.
     *
     * @return string Chaîne HTML du label de pliage / dépliage.
     */
    protected function generateFold()
    {
        $htmlFold = '';

        if (($this->expandAll === true) || ($this->collapseAll === true)) {
            $htmlFold .= '<div>';
            if ($this->expandAll === true) {
                $htmlFold .= '<span class="tree_fold_link" onclick="'.$this->id.'.expandAll();">';
                $htmlFold .= $this->labelExpandAll;
                $htmlFold .= '</span>';
            }
            if (($this->expandAll === true) && ($this->collapseAll === true)) {
                $htmlFold .= ' / ';
            }
            if ($this->collapseAll === true) {
                $htmlFold .= '<span class="tree_fold_link" onclick="'.$this->id.'.collapseAll();">';
                $htmlFold .= $this->labelCollapseAll;
                $htmlFold .= '</span>';
            }
            $htmlFold .= '</div>';
        }

        return $htmlFold;
    }

    /**
     * Fonction qui génère le code Javascript permettant de plier / déplier l'ensemble de l'arbre.
     *
     * @return string Chaîne Javascript de la fonction permettant le pliage / dépliage.
     */
    protected function getFoldScript()
    {
        $scriptFold = '';

        $scriptFold .= $this->id.'.expandAllState == false;';
        if ($this->expandAll === true) {
            $scriptFold .= $this->id.'.expandAll = function() {';
            $scriptFold .= $this->id.'.expandAllState = true;';
            $scriptFold .= $this->id.'.Tree.removeChildren('.$this->id.'.Tree.getRoot());';
            $scriptFold .= $this->id.'.init();';
            $scriptFold .= '};';
        }
        if ($this->collapseAll === true) {
            $scriptFold .= $this->id.'.collapseAll = function() {';
            $scriptFold .= $this->id.'.expandAllState = false;';
            $scriptFold .= $this->id.'.treeState = new Array();';
            $scriptFold .= $this->id.'.Tree.removeChildren('.$this->id.'.Tree.getRoot());';
            $scriptFold .= $this->id.'.init();';
            $scriptFold .= '};';
        }

        return $scriptFold;
    }

    /**
     * Initialise le formulaire d'édition.
     *
     * @return GenericTag
     */
    protected function initEditForm()
    {
        $editForm = new GenericTag('form');
        $editForm->setAttribute('action', $this->getActionUrl('editnode'));
        $editForm->setAttribute('method', 'POST');
        $editForm->setAttribute('id', $this->id.'_editForm');
        $editForm->addClass('form-horizontal');

        $hiddenValue = new GenericVoidTag('input');
        $hiddenValue->setAttribute('type', 'hidden');
        $hiddenValue->setAttribute('name', $this->id.'_element');
        $hiddenValue->setAttribute('id', $this->id.'_element');
        $editForm->appendContent($hiddenValue);

        if ($this->_editNode['parent'] === true) {
            $changeParentLoadingOption = new GenericTag('option', $this->changeParentLoadingOption);
            $changeParentInput = new GenericTag('select', $changeParentLoadingOption);
            $changeParentInput->setAttribute('name', $this->id.'_changeParent');
            $changeParentInput->setAttribute('id', $this->id.'_changeParent');
            $changeParentInput->addClass('form-control');
            $changeParentWrapper = new GenericTag('div', $changeParentInput);
            $changeParentWrapper->addClass('col-xs-10');
            $changeParentLabel = new GenericTag('label', $this->changeParentLabel);
            $changeParentLabel->setAttribute('for', $this->id.'_changeParent');
            $changeParentLabel->addClass('control-label');
            $changeParentLabel->addClass('col-xs-2');
            $changeParentGroup = new GenericTag('div');
            $changeParentGroup->addClass('form-group');
            $changeParentGroup->appendContent($changeParentLabel);
            $changeParentGroup->appendContent($changeParentWrapper);
            $editForm->appendContent($changeParentGroup);
        }

        if ($this->_editNode['order'] === true) {
            $changeOrderFirstInput = new GenericVoidTag('input');
            $changeOrderFirstInput->setAttribute('name', $this->id.'_changeOrder');
            $changeOrderFirstInput->setAttribute('value', 'first');
            $changeOrderFirstInput->setAttribute('type', 'radio');
            $changeOrderFirstLabel = new GenericTag('label', $this->changeOrderFirstLabel);
            $changeOrderFirstLabel->appendContent($changeOrderFirstInput);
            $changeOrderFirstWrapper = new GenericTag('div', $changeOrderFirstLabel);
            $changeOrderFirstWrapper->addClass('radio');

            $changeOrderLastInput = new GenericVoidTag('input');
            $changeOrderLastInput->setAttribute('name', $this->id.'_changeOrder');
            $changeOrderLastInput->setAttribute('value', 'last');
            $changeOrderLastInput->setAttribute('type', 'radio');
            $changeOrderLastLabel = new GenericTag('label', $this->changeOrderLastLabel);
            $changeOrderLastLabel->appendContent($changeOrderLastInput);
            $changeOrderLastWrapper = new GenericTag('div', $changeOrderLastLabel);
            $changeOrderLastWrapper->addClass('radio');

            $changeOrderAfterInput = new GenericVoidTag('input');
            $changeOrderAfterInput->setAttribute('name', $this->id.'_changeOrder');
            $changeOrderAfterInput->setAttribute('value', 'after');
            $changeOrderAfterInput->setAttribute('type', 'radio');
            $changeOrderAfterLabel = new GenericTag('label', $this->changeOrderAfterLabel);
            $changeOrderAfterLabel->appendContent($changeOrderAfterInput);
            $changeOrderAfterWrapper = new GenericTag('div', $changeOrderAfterLabel);
            $changeOrderAfterWrapper->addClass('radio');

            $changeOrderWrapper = new GenericTag('div');
            $changeOrderWrapper->addClass('col-xs-10');
            $changeOrderWrapper->appendContent($changeOrderFirstWrapper);
            $changeOrderWrapper->appendContent($changeOrderLastWrapper);
            $changeOrderWrapper->appendContent($changeOrderAfterWrapper);

            $changeOrderLabel = new GenericTag('span', $this->changeOrderLabel);
            $changeOrderLabel->addClass('control-label');
            $changeOrderLabel->addClass('form-control-static');
            $changeOrderLabel->addClass('col-xs-2');

            $changeOrderGroup = new GenericTag('div');
            $changeOrderGroup->addClass('form-group');
            $changeOrderGroup->appendContent($changeOrderLabel);
            $changeOrderGroup->appendContent($changeOrderWrapper);
            $editForm->appendContent($changeOrderGroup);

            $selectAfter = new Select($this->id.'_selectAfter');

            $optionLoadingAfter = new Option($this->id.'_load');
            $optionLoadingAfter->label = $this->changeOrderLoadingOption;
            $selectAfter->addOption($optionLoadingAfter);
            $selectAfter->getElement()->hidden = true;
            
            $changeOrderLoadingOption = new GenericTag('option', $this->changeOrderLoadingOption);
            $changeOrderInput = new GenericTag('select', $changeOrderLoadingOption);
            $changeOrderInput->setAttribute('name', $this->id.'_selectAfter');
            $changeOrderInput->setAttribute('id', $this->id.'_selectAfter');
            $changeOrderInput->addClass('form-control');
            $changeOrderWrapper = new GenericTag('div', $changeOrderInput);
            $changeOrderWrapper->addClass('col-xs-10 col-xs-offset-2');
            $changeOrderGroup = new GenericTag('div', $changeOrderWrapper);
            $changeOrderGroup->addClass('form-group');
            $changeOrderGroup->addClass('hide');
            $editForm->appendContent($changeOrderGroup);
        }

        if (is_array($this->_editNode['custom'])) {
            foreach ($this->_editNode['custom'] as $customFormElement) {
                $editForm->appendContent($customFormElement);
            }
        }

        return $editForm;
    }

    /**
     * Fonction qui génère le code javascript lié au popup d'édition.
     *
     * @return string
     */
    protected function getEditScript()
    {
        $editScript = '';

        $editScript .= $this->initEditForm()->getScript();

        // Action d'affichage du bloc de sélection de déplacement
        if ($this->_editNode['order'] === true) {
            $editScript .= '$(\'input[name="'.$this->id.'_changeOrder"]\').on(\'change\', function(e) {';
            $editScript .= 'if ($(\'input[name="'.$this->id.'_changeOrder"]:checked\').val() === \'after\') {';
            $editScript .= '$(\'#'.$this->id.'_selectAfter\').closest(\'.form-group\').removeClass(\'hide\');';
            $editScript .= '} else {';
            $editScript .= '$(\'#'.$this->id.'_selectAfter\').closest(\'.form-group\').addClass(\'hide\');';
            $editScript .= '}';
            $editScript .= '});';
        }

        // Fonction de préparation du popup d'édition.
        $editScript .= '$(\'body\').on(\'click\', \'[data-target="#'.$this->id.'_editPanel"]\', function(e) {';
        $editScript .= 'var idNode = $(this).attr(\'id\').split(\''.$this->id.'_\').pop();';
        $editScript .= '$(\'#' . $this->id . '_element\').val(idNode).trigger(\'change\');';
        $editScript .= '});';
        // Initialisation du popup d'édition.
        $editScript .= '$(\'#'.$this->id.'_editPanel\').on(\'shown.bs.modal\', function(e) {';
        $editScript .= 'var idNode = $(\'#' . $this->id . '_element\').val();';
        // Chargement de la liste des parents.
        if ($this->_editNode['parent'] === true) {
            $editScript .= '$.get(';
            $editScript .= '\''.$this->getActionUrl('getlistparents').'idNode=\' + idNode, ';
            $editScript .= 'function(o){';
            $editScript .= '$(\'#' . $this->id . '_changeParent\').formActionSetOptions(o);';
            $editScript .= '}';
            $editScript .= ').error(function(o) {';
            $editScript .= 'errorHandler(o);';
            $editScript .= '});';
        }
        // Chargement de la liste des frères.
        if ($this->_editNode['order'] === true) {
            $editScript .= '$.get(';
            $editScript .= '\''.$this->getActionUrl('getlistsiblings').'idNode=\' + idNode + \'&idParent=\', ';
            $editScript .= 'function(o){';
            $editScript .= '$(\'#' . $this->id . '_selectAfter\').formActionSetOptions(o);';
            $editScript .= '}';
            $editScript .= ').error(function(o) {';
            $editScript .= 'errorHandler(o);';
            $editScript .= '});';
        }
        $editScript .= '});';

        if (($this->_editNode['parent'] === true) && ($this->_editNode['order'] === true)) {
            // Lors d'un changement de parent, on change les options de changement d'ordre "après"
            $editScript .= '$(\'#' . $this->id . '_changeParent\').on(\'change\', function(e) {';
            $editScript .= '$(\'#' . $this->id . '_selectAfter\').formActionSetOptions(';
            $editScript .= 'new Array(\''.$this->changeOrderLoadingOption.'\')';
            $editScript .= ');';
            $editScript .= '$.get(';
            $editScript .= '\''.$this->getActionUrl('getlistsiblings').'idNode=\' + $(\'#' . $this->id . '_element\').val() + ';
            $editScript .= '\'&idParent=\' + $(this).val(), ';
            $editScript .= 'function(o){';
            $editScript .= '$(\'#' . $this->id . '_selectAfter\').formActionSetOptions(o);';
            $editScript .= '}';
            $editScript .= ').error(function(o) {';
            $editScript .= 'errorHandler(o);';
            $editScript .= '});';
            $editScript .= '});';
            // L'évent de setOption dynamic est attaché à chaque ouverture du panel d'édition,
            //  il faut éviter l'appel multiple. L'évent est donc détaché à la fermeture.
        }

        if ($this->deleteNode === true) {
            // Ajout d'une fonction de suppression des éléments au Tree.
            $editScript .= $this->id.'.deleteNode = function(idNode) {';
            $editScript .= 'setMask(true);';
            $editScript .= '$.post(';
            $editScript .= '\''.$this->getActionUrl('deletenode').'idNode=\' + idNode, ';
            $editScript .= 'function(o){';
            $editScript .= '$(\'#'.$this->id.'_deletePanel\').modal(\'hide\');';
            $editScript .= '$(\'#'.$this->id.'_editPanel\').modal(\'hide\');';
            $editScript .= 'var currentNode = $(\'#'.$this->id.'_\' + idNode);';
            $editScript .= 'var parentNode = '.$this->id.'.Tree.getNodeByElement(currentNode[0]).parent;';
            $editScript .= 'if (parentNode == \'RootNode\') {';
            $editScript .= $this->id.'.Tree.removeChildren('.$this->id.'.Tree.getRoot());';
            $editScript .= $this->id.'.init();';
            $editScript .= '} else {';
            $editScript .= $this->id.'.Tree.removeChildren(parentNode);';
            $editScript .= 'parentNode.expand();';
            $editScript .= '}';
            $editScript .= 'if (o.message != \'\') {';
            $editScript .= 'addMessage(o.message, \'success\')';
            $editScript .= '}';
            $editScript .= 'setMask(false);';
            $editScript .= '}).error(function(o) {';
            $editScript .= '$(\'#'.$this->id.'_deletePanel\').modal(\'hide\');';
            $editScript .= '$(\'#'.$this->id.'_editPanel\').modal(\'hide\');';
            $editScript .= 'setMask(false);';
            $editScript .= 'errorHandler(o);';
            $editScript .= '});';
            $editScript .= '};';
        }

        // Ajout d'une fonction d'encapsulation de l'édition.
        $editScript .= 'new AjaxForm(\'#'.$this->id.'_editForm\');';
        $editScript .= '$(\'#'.$this->id.'_editForm\').on(\'successSubmit\', function(e, response) {';
        $editScript .= 'this.reset();';
        $editScript .= '$(\'#'.$this->id.'_selectAfter\').closest(\'.form-group\').addClass(\'hide\');';
        $editScript .= $this->id.'.Tree.removeChildren('.$this->id.'.Tree.getRoot());';
        $editScript .= $this->id.'.init();';
        $editScript .= '$(\'#'.$this->id.'_editPanel\').modal(\'hide\');';
        $editScript .= '});';
        // Ajout du fonction de reset du formulaire lors de la fermeture électrique.
        $editScript .= '$(\'#'.$this->id.'_editPanel\').on(\'hide.bs.modal\', function() {';
        $editScript .= '$(\'#'.$this->id.'_editForm\').get(0).reset();';
        $editScript .= '$(\'#'.$this->id.'_editForm\').eraseFormErrors();';
        $editScript .= '});';

        return $editScript;
    }

    /**
     * Fonction qui génère le code html lié au popup d'édition.
     *
     * @return string
     */
    protected function generateEdit()
    {
        $edit = '';

        $editPanel = new Modal();
        $editPanel->setAttribute('id', $this->id.'_editPanel');
        $editPanel->addTitle($this->editPanelTitle);
        $editPanel->addDefaultDismissButton();

        $editPanel->setContent($this->initEditForm()->getHTML());

        if ($this->deleteNode === true) {
            $buttonShowDeletePanel = new Button($this->deleteButtonLabel, Button::TYPE_WARNING);
            $buttonShowDeletePanel->prependContent(' ');
            $buttonShowDeletePanel->prependContent(new Icon($this->deleteButtonIcon));
            $buttonShowDeletePanel->setAttribute('href', '#');
            $buttonShowDeletePanel->setAttribute('data-toggle', 'modal');
            $buttonShowDeletePanel->setAttribute('data-target', '#'.$this->id.'_deletePanel');

            // Placement du bouton dans le corps du popup si l'édition n'est pas possible.
            if ($this->_editNode['state'] === true) {
                $buttonShowDeletePanel->setAttribute('style', 'float: left;');
                $editPanel->setFooterContent($buttonShowDeletePanel->getHTML());
            } else {
                $editPanel->appendContent($buttonShowDeletePanel->getHTML());
            }

            $buttonConfirmDelete= new Button($this->deletePanelConfirmLabel, Button::TYPE_PRIMARY);
            $buttonConfirmDelete->prependContent(' ');
            $buttonConfirmDelete->prependContent(new Icon($this->deletePanelConfirmIcon));
            $deleteAction = $this->id.'.deleteNode($(\'#' . $this->id . '_element\').val());';
            $buttonConfirmDelete->setAttribute('onclick', $deleteAction);

            $buttonCancelDelete= new Button($this->deletePanelCancelLabel);
            $buttonCancelDelete->prependContent(' ');
            $buttonCancelDelete->prependContent(new Icon($this->deletePanelCancelIcon));
            $buttonCancelDelete->setAttribute('href', '#');
            $buttonCancelDelete->setAttribute('data-dismiss', 'modal');
            $buttonCancelDelete->setAttribute('data-target', '#'.$this->id.'_deletePanel');

            $deletePanel = new Modal();
            $deletePanel->setAttribute('id', $this->id.'_deletePanel');
            $deletePanel->setBackdropStatic();
            $deletePanel->addTitle($this->deletePanelTitle);
            $deletePanel->addDefaultDismissButton();
            $deletePanel->setFooterContent($buttonConfirmDelete->getHTML().$buttonCancelDelete->getHTML());
            $deletePanel->setContent($this->deletePanelText);

        }

        if ($this->_editNode['state'] === true) {

            // Ajout des boutons de confirmation / infirmation.
            $buttonConfirmEditPanel = new Button($this->editPanelConfirmLabel, Button::TYPE_PRIMARY);
            $buttonConfirmEditPanel->prependContent(' ');
            $buttonConfirmEditPanel->prependContent(new Icon($this->editPanelConfirmIcon));
            $buttonConfirmEditPanel->setAttribute('onclick', '$(\'#'.$this->id.'_editForm\').submit();');

            $buttonCancelEditPanel = new Button($this->editPanelCancelLabel);
            $buttonCancelEditPanel->prependContent(' ');
            $buttonCancelEditPanel->prependContent(new Icon($this->editPanelCancelIcon));
            $buttonCancelEditPanel->closeModal($this->id.'_addPanel');

            $editPanel->getFooter()->appendContent($buttonConfirmEditPanel->getHTML().$buttonCancelEditPanel->getHTML());
        }

        $edit .= $editPanel->getHTML();

        if ($this->deleteNode === true) {
            $edit .= $deletePanel->getHTML();
        }

        return $edit;
    }

    /**
     * Initialise le formulaire d'ajout.
     */
    protected function initAddForm()
    {
        if (!($this->addPanelForm instanceof GenericTag)) {
            throw new Core_Exception_UndefinedAttribute('You must specify an addPanelForm to enable the addition.');
        }
        $this->addPanelForm->setAttribute('id', $this->id.'_addForm');
        $this->addPanelForm->setAttribute('action', $this->getActionUrl('addnode'));
        $this->addPanelForm->setAttribute('method', 'POST');
        $this->addPanelForm->addClass('form-horizontal');
    }

    /**
     * Fonction qui génère le code javascript du panneau d'ajout d'éléments.
     *
     * @return string Chaîne HTML et Javascript du panneau d'ajout.
     */
    protected function getAddScript()
    {
        $this->initAddForm();
        $addScript = '';

        // Ajout des scripts du formulaire.
        $addScript .= 'new AjaxForm(\'#'.$this->addPanelForm->getAttribute('id').'\');';

        // Ajout d'une fonction d'encapsulation de l'ajout.
        $addScript .= '$(\'#'.$this->addPanelForm->getAttribute('id').'\').on(\'successSubmit\', function(response) {';
        $addScript .= 'this.reset();';
        $addScript .= $this->id.'.Tree.removeChildren('.$this->id.'.Tree.getRoot());';
        $addScript .= $this->id.'.init();';
        $addScript .= '$(\'#'.$this->id.'_addPanel\').modal(\'hide\');';
        $addScript .= '});';

        return $addScript;
    }

    /**
     * Fonction qui génère le code du panneau d'ajout d'éléments.
     *
     * @return string Chaîne HTML et Javascript du panneau d'ajout.
     */
    protected function generateAdd()
    {
        $this->initAddForm();

        $add = '<div>';

        $addButton = new Button($this->addButtonLabel);
        $addButton->prependContent(' ');
        $addButton->prependContent(new Icon($this->addButtonIcon));
        $addButton->showModal($this->id.'_addPanel');
        $add .= $addButton->getHTML();

        $add .= '</div>';

        // Ajout du popup d'ajout.
        $buttonConfirmAddPanel = new Button($this->addPanelConfirmLabel, Button::TYPE_PRIMARY);
        $buttonConfirmAddPanel->prependContent(' ');
        $buttonConfirmAddPanel->prependContent(new Icon($this->addPanelConfirmIcon));
        $buttonConfirmAddPanel->setAttribute('onclick', '$(\'#'.$this->id.'_addForm\').submit();');

        $buttonCancelAddPanel = new Button($this->addPanelCancelLabel);
        $buttonCancelAddPanel->prependContent(' ');
        $buttonCancelAddPanel->prependContent(new Icon($this->addPanelCancelIcon));
        $buttonCancelAddPanel->closeModal($this->id.'_addPanel');
        $resetAction = '$(\'#'.$this->id.'_addForm\').get(0).reset();$(\'#'.$this->id.'_addForm\').eraseFormErrors();';
        $buttonCancelAddPanel->setAttribute('onclick', $resetAction);

        $addPanel = new Modal($this->addPanelForm);
        $addPanel->setAttribute('id', $this->id.'_addPanel');
        $addPanel->addTitle($this->addPanelTitle);
        $addPanel->addDefaultDismissButton();
        $addPanel->setFooterContent($buttonConfirmAddPanel->getHTML().$buttonCancelAddPanel->getHTML());
        $addPanel->setBackdropStatic();

        $add .= $addPanel->getHTML();

        return $add;
    }

    /**
     * Fonction qui génère le code javascript du tree.
     *
     * @return string
     */
    protected function getTreeScript()
    {
        $treeScript = '';

        // Définition d'un objet javascript qui sera une surcouche du TreeView de yui.
        $treeScript .= 'var tree'.$this->id.' = function() {';

        // Création et stockage de l'objet yui TreeView
        $treeScript .= 'this.Tree = new YAHOO.widget.TreeView("'.$this->id.'_container");';

        // Définition de la fonction permettant de charger dynamiquement le contenu des noeuds.
        $treeScript .= 'this.loadNodes = function (parentNode, fnLoadComplete) {';
        $treeScript .= 'setMask(true);';
        $treeScript .= 'var oCallback = {';
        $treeScript .= 'success: function(o) {';
        $treeScript .= 'var nodesData = YAHOO.lang.JSON.parse(o.responseText).data;';
        $treeScript .= 'for (var i in nodesData) {';
        $treeScript .= $this->id.'.parseNodeData(nodesData[i], o.argument.parentNode);';
        $treeScript .= '}';
        $treeScript .= 'setMask(false);';
        $treeScript .= 'o.argument.fnLoadComplete();';
        $treeScript .= '},';
        $treeScript .= 'failure: function(o) {';
        $treeScript .= 'errorHandler(o);';
        $treeScript .= 'setMask(false);';
        $treeScript .= '},';
        $treeScript .= 'argument: {';
        $treeScript .= '\'parentNode\': parentNode,';
        $treeScript .= '\'fnLoadComplete\': fnLoadComplete';
        $treeScript .= '}';
        $treeScript .= '};';
        $treeScript .= 'var sUrl = \''.$this->getActionUrl('getnodes').'idNode=\'';
        $treeScript .= ' + parentNode.labelElId.split(\''.$this->id.'_\').pop();';
        $treeScript .= 'YAHOO.util.Connect.asyncRequest("get", sUrl, oCallback);';
        $treeScript .= '};';

        // Définition de la fonction permettant de convertir un tableau javascript en noeud.
        $treeScript .= 'this.parseNodeData = function (data, parentNode) {';
        $treeScript .= 'var node = new YAHOO.widget.TextNode(data[\'label\'], parentNode, false);';
        $treeScript .= 'node.labelElId = \''.$this->id.'_\' + data[\'id\'];';
        $treeScript .= 'if (data[\'isLeaf\'] != false) {';
        $treeScript .= 'node.isLeaf = true;';
        $treeScript .= 'node.contentStyle = \'tree_leaf\';';
        $treeScript .= '} else {';
        $treeScript .= 'if ((typeof('.$this->id.'.treeState[node.labelElId]) != \'undefined\') ';
        $treeScript .= '|| (data[\'isExpanded\'] == true) || ('.$this->id.'.expandAllState == true)) {';
        $treeScript .= 'node.expand();';
        $treeScript .= '}';
        $treeScript .= 'node.contentStyle = \'tree_parent\';';
        $treeScript .= '}';
        if (($this->_editNode['state'] === false) && ($this->deleteNode === false)) {
            $treeScript .= 'if (typeof(data[\'url\']) == \'string\') {';
            $treeScript .= 'if (data[\'directLink\'] == true) {';
            $treeScript .= 'node.href = data[\'url\'];';
            $treeScript .= '}';
            $treeScript .= '} else {';
            $treeScript .= 'node.contentStyle = \'tree_node_text\';';
            $treeScript .= '}';
        } else {
            $treeScript .= 'if (data[\'isEditable\'] == true) {';
            $treeScript .= 'node.editable = true;';
            $treeScript .= 'node.href = \''.$this->id.'_editPanel\';';
            $treeScript .= 'node.getContentHtml = function() {;';
            $treeScript .= 'var d = [];';
            $treeScript .= 'd[d.length] = this.href ? "<a" : "<span";';
            $treeScript .= 'd[d.length] = " id=\"" + YAHOO.lang.escapeHTML(this.labelElId) + "\"";';
            $treeScript .= 'd[d.length] = " class=\"" + YAHOO.lang.escapeHTML(this.labelStyle) + "\"";';
            $treeScript .= 'if (this.href) {';
            $treeScript .= 'if (this.editable) {';
            $treeScript .= 'd[d.length] = " href=\"#\"";';
            $treeScript .= 'd[d.length] = " data-toggle=\"modal\"";';
            $treeScript .= 'd[d.length] = " data-remote=\"false\"";';
            $treeScript .= 'd[d.length] = " data-target=\"#" + YAHOO.lang.escapeHTML(this.href) + "\"";';
            $treeScript .= '} else {';
            $treeScript .= 'd[d.length] = " href=\"" + YAHOO.lang.escapeHTML(this.href) + "\"";';
            $treeScript .= 'd[d.length] = " target=\"" + YAHOO.lang.escapeHTML(this.target) + "\"";';
            $treeScript .= '}';
            $treeScript .= '}';
            $treeScript .= 'if (this.title) {';
            $treeScript .= 'd[d.length] = " title=\"" + YAHOO.lang.escapeHTML(this.title) + "\"";';
            $treeScript .= '}';
            $treeScript .= 'd[d.length] = " >";';
            $treeScript .= 'd[d.length] = this.label;';
            $treeScript .= 'd[d.length] = this.href ? "</a>" : "</span>";';
            $treeScript .= 'return d.join("");';
            $treeScript .= '};';
            $treeScript .= '} else {';
            $treeScript .= 'node.contentStyle = \'tree_node_text\';';
            $treeScript .= '}';
        }
        $treeScript .= '};';

        // Spécification du chargement dynamic des noeuds.
        $treeScript .= 'this.Tree.setDynamicLoad(this.loadNodes);';

        // Définition de la fonction de sauvegarde de l'état de l'arbre.
        $treeScript .= 'this.treeState = new Array();';
        $treeScript .= 'this.Tree.saveTreeState = function (node) {';
        $treeScript .= 'if (node.expanded) {';
        $treeScript .= 'if(typeof('.$this->id.'.treeState[node.labelElId]) == \'undefined\') {';
        $treeScript .= $this->id.'.treeState[node.labelElId] = true;';
        $treeScript .= '}';
        $treeScript .= '} else {';
        $treeScript .= 'if(typeof('.$this->id.'.treeState[node.labelElId]) != \'undefined\') {';
        $treeScript .= 'delete '.$this->id.'.treeState[node.labelElId];';
        $treeScript .= '}';
        $treeScript .= '}';
        $treeScript .= '};';
        $treeScript .= 'this.Tree.subscribe(\'expandComplete\', this.Tree.saveTreeState);';
        $treeScript .= 'this.Tree.subscribe(\'collapseComplete\', this.Tree.saveTreeState);';
        // Séléction des noeuds au click.
        $treeScript .= 'this.Tree.subscribe(\'clickEvent\', this.Tree.onEventToggleHighlight);';

        // Création d'une fonction permettant d'initialiser le tree, puis lancement de cette dernière.
        $treeScript .= 'this.init = function() {';
        $treeScript .= 'setMask(true);';
        $treeScript .= 'var oCallback = {';
        $treeScript .= 'success: function(o) {';
        $treeScript .= 'var nodesData = YAHOO.lang.JSON.parse(o.responseText).data;';
        $treeScript .= 'for (var i in nodesData) {';
        $treeScript .= 'o.argument.tree.parseNodeData(nodesData[i], o.argument.tree.Tree.getRoot());';
        $treeScript .= '}';
        $treeScript .= 'setMask(false);';
        $treeScript .= 'o.argument.tree.Tree.draw();';
        $treeScript .= '},';
        $treeScript .= 'failure: function(o) {';
        $treeScript .= 'errorHandler(o);';
        $treeScript .= 'setMask(false);';
        $treeScript .= '},';
        $treeScript .= 'argument: {';
        $treeScript .= '\'tree\': this';
        $treeScript .= '}';
        $treeScript .= '};';
        $treeScript .= 'var sUrl = \''.$this->getActionUrl('getnodes').'&idNode=Root\';';
        $treeScript .= 'YAHOO.util.Connect.asyncRequest("get", sUrl, oCallback);';
        $treeScript .= 'this.Tree.getRoot().labelElId = \''.$this->id.'-root\';';
        $treeScript .= '};';
        $treeScript .= 'this.init();';

        // Fin de la définition de l'objet.
        $treeScript .= '};';

        // Instanciation de l'objet tree.
        $treeScript .= $this->id.' = new tree'.$this->id.'();';

        return $treeScript;
    }
    /**
     * Fonction qui génere le code du Tree.
     *
     * @return string Chaîne HTML et Javascript du tree.
     */
    protected function generateTree()
    {
        $tree = '';

        $tree .= '<div id="'.$this->id.'_container" class="tree"></div>';

        return $tree;
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_Tree $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    static function addHeader($instance=null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        // Ajout des feuilles de style.
        $broker->view->headLink()->appendStylesheet('yui/build/treeview/assets/skins/sam/treeview.css');
        $broker->view->headLink()->appendStylesheet('css/ui/tree.css');
        // Ajout des fichiers Javascript.
        $broker->view->headScript()->appendFile('yui/build/yahoo-dom-event/yahoo-dom-event.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/element/element-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/animation/animation-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/treeview/treeview-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/connection/connection-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/json/json-min.js', 'text/javascript');

        $broker->view->headScript()->appendFile('scripts/ui/form-action.js', 'text/javascript');
        $broker->view->headLink()->appendStylesheet('markitup/skins/markitup/style.css');
        $broker->view->headLink()->appendStylesheet('markitup/skins/textile/style.css');
        $broker->view->headScript()->appendFile('markitup/jquery.markitup.js', 'text/javascript');
        $broker->view->headScript()->appendFile('markitup/sets/textile/set.js', 'text/javascript');

        parent::addHeader($instance);
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        $script .= $this->getTreeScript();

        // Ajout de la possibilité de plier / déplier l'ensemble de l'arbre.
        if (($this->expandAll === true) || ($this->collapseAll === true)) {
            $script .= $this->getFoldScript();
        }

        // Ajout du panneau d'édition.
        if (($this->_editNode['state'] === true) || ($this->deleteNode === true)) {
            $script .= $this->getEditScript();
        }

        // Ajout du panneau d'ajout.
        if ($this->addNode === true) {
            $script .= $this->getAddScript();
        }

        return $script;
    }

    /**
     * Génère le code HTML.
     *
     * @return mixed(void|string) chaîne html du tree.
     */
    public function getHTML()
    {
        $html = '';

        // Ajout de la possibilité de plier / déplier l'ensemble de l'arbre.
        $html .= $this->generateFold();

        // Ajout du tree.
        $html .= $this->generateTree();

        // Ajout du panneau d'édition.
        if (($this->_editNode['state'] === true) || ($this->deleteNode === true)) {
            $html .= $this->generateEdit();
        }

        // Ajout du panneau d'ajout.
        if ($this->addNode === true) {
            $html .= $this->generateAdd();
        }

        return $html;
    }

}
