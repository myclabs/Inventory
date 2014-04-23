<?php

namespace UI\Datagrid;

use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\Icon;
use MyCLabs\MUIH\Collapse;
use MyCLabs\MUIH\Modal;
use UI\Datagrid\Column\GenericColumn;
use UI\Datagrid\Column\ListColumn;
use UI\Datagrid\Column\LongTextColumn;
use UI_Generic;
use UI_Form;
use UI_Form_Element_HTML;
use Core_Exception_InvalidArgument;
use Zend_Json;
use Zend_Json_Expr;
use Zend_Session_Namespace;
use Zend_Controller_Action_HelperBroker;
use Doctrine\Common\Collections\Criteria;

/**
 * Classe permettant de génèrer un Tableau de données (datagrid).
 *
 * @author valentin.claras
 *
 * @see    UI_Controller_Datagrid
 */
class Datagrid extends UI_Generic
{
    /**
     * Définition du message affiché dans la datagrid lorqu'elle est vide.
     *
     * @var string
     */
    public $datagridEmptyText;

    /**
     * Définition du message affiché dans la datagrid lorqu'elle contient une erreur.
     *
     * @var string
     */
    public $datagridErrorText;

    /**
     * Définition du message affiché dans la datagrid lorqu'elle est en chargement.
     *
     * @var string
     */
    public $datagridLoadingText;

    /**
     * Définition du titre par défaut du Collapse entourant le filtre affiché au dessus du datagrid.
     *
     * @var string
     */
    public $filterCollapseTitle;

    /**
     * Définition de l'aide affiché au survol dans le cas d'un filtre précédemment sauvegardé.
     *
     * @var string
     */
    public $filterCollapseActiveHint;

    /**
     * Définition du Collapse entourant le filtre affiché au dessus du datagrid.
     *
     * @var Collapse
     */
    public $filterCollapse;

    /**
     * Définition du bouton permettant de filtrer.
     *
     * @var Button
     */
    public $filterConfirmButton;

    /**
     * Définition du bouton permettant de réinitialiser le filtre.
     *
     * @var Button
     */
    public $filterResetButton;

    /**
     * Définition de l'icone permettant de réinitialiser indépendamment chaque champs du filtre.
     *
     * @var string
     */
    public $filterIconResetFieldSuffix;

    /**
     * Définition de l'icône affichée dans le bouton faisant apparaître le popup d'ajout.
     *
     * @var string
     */
    public $addButtonIcon;

    /**
     * Définition du label affiché dans le bouton faisant apparaître le popup d'ajout.
     *
     * @var string
     */
    public $addButtonLabel;

    /**
     * Définition du titre affiché dans le popup d'ajout.
     *
     * @var string
     */
    public $addPanelTitle;

    /**
     * Définition du formulaire affiché dans le popup d'ajout. Null = UI_Form par défaut.
     *
     * @var UI_Form
     */
    public $addPanelForm;

    /**
     * Définition de l'icône affichée dans le bouton de validation du popup d'ajout.
     *
     * @var string
     */
    public $addPanelConfirmIcon;

    /**
     * Définition de l'icône affichée dans le bouton d'annulation du popup d'ajout.
     *
     * @var string
     */
    public $addPanelCancelIcon;

    /**
     * Définition du label affiché dans le bouton de validation du popup d'ajout.
     *
     * @var string
     */
    public $addPanelConfirmLabel;

    /**
     * Définition du label affiché dans le bouton d'annulation du popup d'ajout.
     *
     * @var string
     */
    public $addPanelCancelLabel;

    /**
     * Définition du titre affiché dans le popup de suppression.
     *
     * @var string
     */
    public $deletePanelTitle;

    /**
     * Définition du message affiché dans le popup de suppression.
     *
     * @var string
     */
    public $deletePanelText;

    /**
     * Définition de l'icône affichée dans le bouton de validation du popup de suppression.
     *
     * @var string
     */
    public $deletePanelConfirmIcon;

    /**
     * Définition de l'icône affichée dans le bouton d'annulation du popup de suppression.
     *
     * @var string
     */
    public $deletePanelCancelIcon;

    /**
     * Définition du label affiché dans le bouton de validation du popup de suppression.
     *
     * @var string
     */
    public $deletePanelConfirmLabel;

    /**
     * Définition du label affiché dans le bouton d'annulation du popup de suppression.
     *
     * @var string
     */
    public $deletePanelCancelLabel;

    /**
     * Définition du titre affiché dans la colonne de suppression.
     *
     * @var string
     */
    public $deleteElementTitle;

    /**
     * Définition du label affiché dans la cellule lorsque la suppression est possible.
     *
     * @var string
     */
    public $deleteElementValue;

    /**
     * Définition du label affiché dans la cellule lorsque la suppression n'est pas possible.
     *
     * @var string
     */
    public $deleteElementNullValue;

    /**
     * Définition du label affiché devant le select du choix du nombre d'élément par page.
     *
     * @var string
     */
    public $paginationPerPage;

    /**
     * Définition du label affiché devant le select du choix de la page.
     *
     * @var string
     */
    public $paginationPage;

    /**
     * Définition du nombre de ligne par page, pour la pagination de la datagrid.
     *
     * @var int
     */
    public $paginationRowPerPage;

    /**
     * Définition des options du nombre de ligne par page, pour la pagination de la datagrid.
     *
     * @var array
     */
    public $paginationOptionsRowPerPage;

    /**
     * Définition du texte menant à la première page, pour la pagination de la datagrid.
     *
     * @var string
     */
    public $paginationFirstPage;

    /**
     * Définition du texte menant à la dernière page, pour la pagination de la datagrid.
     *
     * @var string
     */
    public $paginationLastPage;

    /**
     * Définition du texte menant à la page précédante, pour la pagination de la datagrid.
     *
     * @var string
     */
    public $paginationPreviousPage;

    /**
     * Définition du texte menant à la page suivante, pour la pagination de la datagrid.
     *
     * @var string
     */
    public $paginationNextPage;


    /**
     * Identifiant unique de la Datagrid.
     *
     * @var string
     */
    public $id;

    /**
     * Nom du contrôleur ou la datagrid récuperera les données.
     *
     * @var string
     */
    protected $controller;

    /**
     * Nom (optionnel) du module ou la datagrid récuperera les données.
     *
     * @var string
     */
    protected $module;

    /**
     * Tableau d'attributs optionnels qui seront envoyés au controleur.
     *
     * @var array
     */
    protected $parameters = [];

    /**
     * Tableau des colonnes de la datagrid.
     *
     * @var Column\GenericColumn[]
     */
    protected $columns = [];

    /**
     * Permet de savoir si l'ajout d'éléments est présent dans la datagrid.
     *
     * Par défaut non actif.
     *
     * @var bool
     *
     * @see setAddElement
     */
    public $addElements = false;

    /**
     * Permet de savoir si la suppression d'éléments est présente dans la datagrid.
     *
     * Par défaut non actif.
     *
     * @var bool
     *
     * @see setDeleteElement
     */
    public $deleteElements = false;

    /**
     * Nom du criteria utilisé par le controleur.
     *
     * @var string
     */
    public $criteriaName = Criteria::class;

    /**
     * Tableau de paramètres définissant le tri par défaut sur une datagrid.
     *
     * @var array
     *
     * @see setDefaultSorting
     */
    protected $defaultSorting = [];

    /**
     * Permet de savoir si la datagrid sera paginé.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $pagination = true;

    /**
     * Permet de savoir si les lignes de la datagrid peuvent être séléctionnées lors d'un clic.
     *
     * Par défaut non.
     *
     * @var bool
     */
    public $selectableElement = false;

    /**
     * Permet de savoir si les cellules éditables de la datagrid sont mises en valeur au survol.
     *
     * Par défaut non.
     *
     * @var bool
     */
    public $highlightEditableCell = false;

    /**
     * Permet de savoir si les lignes de la datagrid sont mises en valeur au survol.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $highlightElement = true;

    /**
     * Permet de savoir si les éléments devront être filtrés après une mise à jour.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $automaticFiltering = true;

    /**
     * Permet de savoir si le datagrid sera chargé à l'initialisation.
     * Sinon il faudra passer par la fonction Filtrer du datagrid.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $initialLoading = true;

    /**
     * Tableau de filtres personnalisés (indépendant des colonnes).
     *
     * @var array
     */
    protected $customFilters = [];


    /**
     * Constructeur de la classe Datagrid.
     *
     * @param string $id         Identifiant unique de la datagrid.
     * @param string $controller Nom du contrôleur de la datagrid.
     * @param string $module     Module ou se trouve le controleur de la datagrid.
     */
    public function __construct($id, $controller, $module = null)
    {
        $this->id = $id;
        $this->controller = $controller;
        $this->module = $module;

        $this->datagridEmptyText = __('UI', 'loading', 'empty');
        $this->datagridErrorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
        $this->datagridLoadingText = __('UI', 'loading', 'loading');

        // Pagination
        $this->paginationFirstPage = ' << '.__('UI', 'datagridPagination', 'first');
        $this->paginationPreviousPage = ' < '.__('UI', 'datagridPagination', 'previous');
        $this->labelPaginationPage = __('UI', 'datagridPagination', 'page');
        $this->paginationNextPage = __('UI', 'datagridPagination', 'next').' > ';
        $this->paginationLastPage = __('UI', 'datagridPagination', 'last').' >> ';
        $this->labelPaginationPerPage = __('UI', 'datagridPagination', 'perPage');
        $this->paginationRowPerPage = 10;
        $this->paginationOptionsRowPerPage[] = 5;
        $this->paginationOptionsRowPerPage[] = 10;
        $this->paginationOptionsRowPerPage[] = 20;
        $this->paginationOptionsRowPerPage[] = 50;
        $this->paginationOptionsRowPerPage[] = 100;

        // Colonne de suppression et popup de confirmation de suppression
        $this->deleteElementTitle = '<span>'.__('UI', 'name', 'deletion').'</span>';
        $this->deleteElementValue = '<i class="fa fa-trash-o"></i> '.__('UI', 'verb', 'delete');
        $this->deleteElementNullValue = '';
        $this->deletePanelTitle = __('UI', 'deletionConfirmationPopup', 'title');
        $this->deletePanelText = __('UI', 'deletionConfirmationPopup', 'text');
        $this->deletePanelConfirmIcon = 'check';
        $this->deletePanelConfirmLabel = __('UI', 'verb', 'confirm');
        $this->deletePanelCancelIcon = 'times';
        $this->deletePanelCancelLabel = __('UI', 'verb', 'cancel');

        // Bouton et popup d'ajout
        $this->addButtonLabel = __('UI', 'verb', 'add');
        $this->addButtonIcon = 'plus-circle';
        $this->addPanelTitle = __('UI', 'name', 'addition');
        $this->addPanelConfirmIcon = 'check';
        $this->addPanelConfirmLabel = __('UI', 'verb', 'validate');
        $this->addPanelCancelIcon = 'times';
        $this->addPanelCancelLabel = __('UI', 'verb', 'cancel');

        // Filtres
        $this->filterCollapseTitle = __('UI', 'name', 'filters');
        $this->filterCollapseActiveHint = __('UI', 'datagridFilter', 'TitleFilterActive');
        $this->filterCollapse = new Collapse();
        $this->filterCollapse->setTitleContent($this->filterCollapseTitle);
        $this->filterConfirmButton = new Button(__('UI', 'verb', 'filter'));
        $this->filterConfirmButton->prependContent(' ');
        $this->filterConfirmButton->prependContent(new Icon('search-plus'));
        $this->filterResetButton = new Button(__('UI', 'verb', 'reset'));
        $this->filterResetButton->prependContent(' ');
        $this->filterResetButton->prependContent(new Icon('search-minus'));
        $this->filterIconResetFieldSuffix = 'times';
        $this->defaultSorting['state'] = false;
        $this->defaultSorting['column'] = null;
        $this->defaultSorting['direction'] = Criteria::ASC;
    }

    /**
     * Fonction que permet d'ajouter des paramètres à la datagrid.
     *
     * Ces paramètres seront envoyés par l'url à chaque requête.
     *
     * @param string       $parameterName  Nom du paramètre indiqué dans l'url.
     * @param string|array $parameterValue Valeur du paramètre qui sera transmis.
     */
    public function addParam($parameterName, $parameterValue)
    {
        $this->parameters[$parameterName] = $parameterValue;
    }


    /**
     * Fonction qui permet d'ajouter des colonnes à la datagrid.
     *
     * @param Column\GenericColumn $column Colonne à rajouter.
     * @param int|null             $order  Position de la colonne.
     *
     * @throws Core_Exception_InvalidArgument
     */
    public function addCol($column, $order = null)
    {
        if (($column->id === 'index') || ($column->id === 'delete')) {
            throw new Core_Exception_InvalidArgument('Can\'t with id "index" or "delete" !');
        }

        if ($order !== null) {
            $this->columns[$order] = $column;
        } else {
            $this->columns[] = $column;
        }
    }

    /**
     * Fonction qui définit la colonne et la direction du tri par défaut dans la datagrid.
     *
     * Attention : la colonne doit avoir été ajouté avant et son nomTri doit être défini.
     *
     * @param int    $idColumn      Identifiant de la colonne suivant laquelle trier par défaut.
     * @param string $sortDirection Direction du tri.
     *
     * @see TYPE_SORT_ASC
     * @see TYPE_SORT_DESC
     */
    public function setDefaultSorting($idColumn, $sortDirection = Criteria::ASC)
    {
        foreach ($this->columns as $column) {
            // Vérification que le nom du tri est bien défini pour cette colonne
            if (($column->id === $idColumn) && ($column->criteriaOrderAttribute !== null)) {
                $this->defaultSorting['state'] = true;
                $this->defaultSorting['column'] = $idColumn;
                $this->defaultSorting['direction'] = $sortDirection;
            }
        }
    }

    /**
     * Fonction qui permet d'ajouter des filtres indépendants des colonnes.
     *
     * @param Column\GenericColumn $col Colonne sur laquelle sera basé le filtre.
     */
    public function addFilter($col)
    {
        $this->customFilters[] = $col;
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
        if ($this->module !== null) {
            $url .= $this->module . '/';
        }
        // Ajout du contrôleur.
        $url .= $this->controller . '/';

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

        $url .= '/idDatagrid/' . $this->id;
        $url .= '/criteriaName/' . rawurlencode(str_replace('\\', '|', $this->criteriaName));
        foreach ($this->parameters as $option => $valeur) {
            $url .= '/' . $option . '/' . addslashes($valeur);
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
        return $this->encodeUrl() . $action . $this->encodeParameters();
    }

    /**
     * Indique si la Datagrid possède un filtre.
     *
     * @return bool
     */
    protected function hasFilter()
    {
        if (count($this->customFilters) > 0) {
            return true;
        } else {
            foreach ($this->columns as $column) {
                if ($column->criteriaFilterAttribute !== null) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Initialise le Collapse du filtre.
     */
    protected function initFilterCollapse()
    {
        $this->filterCollapse->getCollapse()->setAttribute('id', $this->id.'_filter');

        $datagridSession = $this->getDatagridSession();
        // Vérification de la présence de valeur par défaut nécéssitant l'affichage du l'indicateur.
        if (($datagridSession['filters'] !== null) && (count($datagridSession['filters']) != 0)) {
            $icon = new Icon('filter');
            $icon->addClass('filterActive');
            $icon->setAttribute('title', $this->filterCollapseActiveHint);
            $this->filterCollapse->getTitleLink()->prependContent($icon);
        }
    }

    /**
     * Fonction qui génère le filtre du tableau.
     *
     * @return string
     */
    protected function generateFilter()
    {
        $this->initFilterCollapse();
        $datagridSession = $this->getDatagridSession();

        // Création d'un formulaire contenant les champs du filtre.
        $formFilter = new GenericTag('form');
        $formFilter->setAttribute('id', $this->id.'_filterForm');
        $formFilter->addClass('form-horizontal');

        $filters = array_merge($this->_cols, $this->_customFilters);
        foreach ($filters as $column) {
            /** @var GenericColumn $column */
            if ($column->criteriaFilterAttribute !== null) {
                if (isset($datagridSession['filters'][$column->getFullFilterName($this)])) {
                    $defaultValue = $datagridSession['filters'][$column->getFullFilterName($this)];
                } else {
                    $defaultValue = null;
                }
                $columnFilterElement = $column->getFilterFormElement($this, $defaultValue);
                if ($columnFilterElement !== null) {
                    $formFilter->appendContent($columnFilterElement);
                }
            }
        }

        $actionWrapper = new GenericTag('div');
        $actionWrapper->addClass('col-xs-10');
        $actionWrapper->addClass('col-xs-offset-2');
        $actionGroup = new GenericTag('div', $actionWrapper);
        $actionGroup->addClass('form-group');
        $formFilter->appendContent($actionGroup);

        $scriptHideWrapper = '$(\'#'.$this->id.'_filter\').collapse(\'hide\');';
        $this->filterConfirmButton->setAttribute('onclick', $this->id.'.filter();'.$scriptHideWrapper);
        $actionWrapper->appendContent($this->filterConfirmButton);
        $actionWrapper->appendContent(' ');
        $this->filterResetButton->setAttribute('onclick', $this->id.'.resetFilter();'.$this->id.'.filter();'.$scriptHideWrapper);
        $actionWrapper->appendContent($this->filterResetButton);

        $this->filterCollapse->setContent($formFilter);

        return $this->filterCollapse->getHTML();
    }

    /**
     * Fonction qui génère le filtre du tableau.
     *
     * @return string
     */
    protected function getFilterScript()
    {
        $this->initFilterCollapse();

        $filterScript = '';

        // Récupération des scripts des éléments du formulaire.
        $filters = array_merge($this->_cols, $this->_customFilters);
        foreach ($filters as $column) {
            /** @var GenericColumn $column */
            if ($column->filterName !== null) {
                if (($column instanceof ListColumn)
                    && ($column->fieldType === ListColumn::FIELD_AUTOCOMPLETE)) {
                    $options = [
                        'allowClear' => 'true',

                    ];
                    if ($column->multiple) {
                        if ($column->dynamicList) {
                            $options['multiple'] = 'true';
                        }
                    }
                    $filterScript .= '$("#'.$column->getFilterFormId($this).'").select2('.
                        Zend_Json::encode($options, false, ['enableJsonExprFinder' => true]).
                        ');';
                }
            }
        }

        // Ajout de la fonction Reinitialiser à la datagrid
        $filterScript .= $this->id.'.resetFilter = function() {';
        foreach ($filters as $column) {
            /** @var GenericColumn $column */
            if ($column->filterName !== null) {
                $filterScript .= $column->getResettingFilter($this);
            }
        }
        $filterScript .= '};';

        return $filterScript;
    }

    /**
     * Initialise le formulaire d'ajout.
     */
    protected function initAddForm()
    {
        if ($this->addPanelForm !== null) {
            $this->addPanelForm->setAttribute('id', $this->id.'_addForm');
        } else {
            $this->addPanelForm = new GenericTag('form');
            $this->addPanelForm->setAttribute('id', $this->id.'_addForm');
            $this->addPanelForm->setAttribute('method', 'POST');
            $this->addPanelForm->addClass('form-horizontal');
            foreach ($this->_cols as $column) {
                if ($column->addable == true) {
                    $columnAddElement = $column->getAddFormElement($this);
                    if ($columnAddElement !== null) {
                        $this->addPanelForm->appendContent($columnAddElement);
                    }
                }
            }
        }
        $this->addPanelForm->setAttribute('action', $this->getActionUrl('addelement'));
    }

    /**
     * Fonction qui génère le code du panneau d'ajout d'éléments.
     *
     * @return string
     */
    protected function getAddScript()
    {
        $this->initAddForm();

        $addScript = '';

        // Ajout des scripts du formulaire.
        $addScript .= $this->addPanelForm->getScript();
        foreach ($this->_cols as $column) {
            if ($column->addable === true) {
                if (($column instanceof LongTextColumn)
                    && ($column->textileEditor)) {
                    $addScript .= '$("#'.$column->getAddFormElementId($this).'").markItUp(mySettings);';
                }
                if (($column instanceof ListColumn)
                    && ($column->fieldType === ListColumn::FIELD_AUTOCOMPLETE)) {
                    $options = [
                        'allowClear' => 'true',

                    ];
                    if ($column->multiple) {
                        if ($column->dynamicList) {
                            $options['multiple'] = 'true';
                        }
                    }
                    if ($column->dynamicList) {
                        $options['ajax'] = [
                            'url' => $column->getUrlDynamicList($this, 'add'),
                            'dataType' => "json",
                            'quietMillis' => 200,
                            'data' => new Zend_Json_Expr('function(term, page) { return {q: term} }'),
                            'results' => new Zend_Json_Expr('function(data, page) { return {results: data} }')
                        ];
                    }
                    $addScript .= '$("#'.$column->getAddFormElementId($this).'").select2('.
                        Zend_Json::encode($options, false, ['enableJsonExprFinder' => true]).
                        ');';
                }
            }
        }

        // Ajout d'une fonction d'encapsulation de l'ajout.
        $addScript .= 'new AjaxForm(\'#'.$this->id.'_addForm\');';
        $addScript .= '$(\'#'.$this->id.'_addForm\').on(\'successSubmit\', function () {';
        $addScript .= 'this.reset();';
        if ($this->automaticFiltering === true) {
            if ($this->pagination === true) {
                $addScript .= 'var paginator = '.$this->id.'.Datagrid.getState().pagination.paginator;';
                $addScript .= 'var currentPage = paginator.getStartIndex() / paginator.getRowsPerPage();';
                $addScript .= $this->id.'.filter(currentPage + 1);';
            } else {
                $addScript .= $this->id.'.filter();';
            }
        }
        $addScript .= '$(\'#'.$this->id.'_addPanel\').modal(\'hide\');';
        $addScript .= '});';

        return $addScript;
    }

    /**
     * Fonction qui génère le code du panneau d'ajout d'éléments.
     *
     * @return string
     */
    protected function generateAdd()
    {
        $this->initAddForm();

        $add = '<div>';

        $addButton = new Button($this->addButtonLabel);
        $addButton->prependContent(' ');
        $addButton->prependContent(new Icon($this->addButtonIcon));
        $addButton->setAttribute('href', '#');
        $addButton->setAttribute('data-toggle', 'modal');
        $addButton->setAttribute('data-remote', 'false');
        $addButton->setAttribute('data-target', '#'.$this->id.'_addPanel');
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

        $addPanel = new Modal();
        $addPanel->setAttribute('id', $this->id.'_addPanel');
        $addPanel->large();
        $addPanel->addTitle($this->addPanelTitle);
        $addPanel->addDefaultDismissButton();
        $addPanel->setFooterContent($buttonConfirmAddPanel->getHTML().$buttonCancelAddPanel->getHTML());
        $addPanel->setContent($this->addPanelForm->getHTML());
        $addPanel->setBackdropStatic();

        $add .= $addPanel->getHTML();

        return $add;
    }

    /**
     * Fonction qui génère le code du panneau de suppression des éléments.
     *
     * @return string
     */
    protected function getDeleteScript()
    {
        // Ajout de la passation des information de la cellule au panel de suppression.
        // Ajout d'un listener sur chaque bouton ouvrant ce popup pour effectuer le chargement du contenu.
        $deleteScript = <<<JS
$('body').on('click', '[data-target="#{$this->id}_deletePanel"]', function(e) {
    e.preventDefault();
    $('.btn-primary', $('#{$this->id}_deletePanel')).attr(
        'onclick',
        '{$this->id}.delete(' + $(this).attr('href').substring(1) + ');'
    );
});
JS;
        return $deleteScript;
    }

    /**
     * Fonction qui génère le code du panneau de suppression d'éléments.
     *
     * @return string
     */
    protected function generateDelete()
    {
        $delete = '';

        // Ajout du popup de supppression.
        $buttonConfirmDeletePanel = new Button($this->deletePanelConfirmLabel, Button::TYPE_PRIMARY);
        $buttonConfirmDeletePanel->prependContent(' ');
        $buttonConfirmDeletePanel->prependContent(new Icon($this->deletePanelConfirmIcon));
        $buttonConfirmDeletePanel->closeModal($this->id.'_deletePanel');

        $buttonCancelDeletePanel = new Button($this->deletePanelCancelLabel);
        $buttonCancelDeletePanel->prependContent(' ');
        $buttonCancelDeletePanel->prependContent(new Icon($this->deletePanelCancelIcon));
        $buttonCancelDeletePanel->closeModal($this->id.'_deletePanel');

        $deletePanel = new Modal();
        $deletePanel->setAttribute('id', $this->id.'_deletePanel');
        $deletePanel->addTitle($this->deletePanelTitle);
        $deletePanel->addDefaultDismissButton();
        $deletePanel->setFooterContent($buttonConfirmDeletePanel->getHTML().$buttonCancelDeletePanel->getHTML());
        $deletePanel->setContent($this->deletePanelText);

        $delete .= $deletePanel->getHTML();

        return $delete;
    }

    /**
     * Fonction qui génère le code de la datagrid.
     *
     * @throws Core_Exception_InvalidArgument
     * @return string
     */
    protected function getDatagridScript()
    {
        $datagridSession = $this->getDatagridSession();

        // Définition d'un objet datagrid qui sera une surcouche de l'objet YUI.
        $datagridScript = "var datagrid{$this->id} = function() {";

        // Ajout des types personnalisés de colonnes.
        foreach ($this->columns as $column) {
            $datagridScript .= $column->getFormattingFunction($this);
        }
        if ($this->deleteElements === true) {
            // Définition du format de la cellule pour la colonne de suppression.
            $datagridScript .= 'YAHOO.widget.DataTable.Formatter.format'.$this->id.'Delete = ';
            $datagridScript .= 'function(Cell, oRecord, oColumn, sData) {';
            $datagridScript .= 'if ((typeof(sData) == "undefined") || (sData != false)) {';
            $datagridScript .= 'var content = \'<a href="#\' + this.getRecordIndex(oRecord) + \'" ';
            $datagridScript .= 'data-toggle="modal"';
            $datagridScript .= 'data-remote="false"';
            $datagridScript .= 'data-target="#'.$this->id.'_deletePanel" ';
            $datagridScript .= '>';
            $datagridScript .= addslashes($this->deleteElementValue);
            $datagridScript .= '</a>\';';
            $datagridScript .= '} else {';
            $datagridScript .= 'var content = \''.addslashes($this->deleteElementNullValue).'\';';
            $datagridScript .= '}';
            $datagridScript .= 'Cell.innerHTML = ';
            $datagridScript .= '\'<span class="'.Column\GenericColumn::DISPLAY_TEXT_CENTER.'">\'';
            $datagridScript .= ' + content + ';
            $datagridScript .= '\'<\/span>\';';
            $datagridScript .= '};';
        }

        // Ajout de la définition des colonnes.
        $datagridScript .= ' this.Columns = [';
        $datagridScript .= '{key:"index", hidden:true}';
        foreach ($this->columns as $column) {
            $datagridScript .= ', '.$column->getDefinition($this);
        }
        // Ajout de la colonne de suppression.
        if ($this->deleteElements === true) {
            $datagridScript .= ', {';
            $datagridScript .= 'key:"delete", ';
            $datagridScript .= 'label:"'.$this->deleteElementTitle.'", ';
            $datagridScript .= 'formatter:"format'.$this->id.'Delete", ';
            $datagridScript .= '}';
        }
        $datagridScript .= '];';

        // Ajout de la Datasource.
        $datagridScript .= 'this.DataSource = new YAHOO.util.DataSource("'.$this->encodeUrl().'");';
        // Définition du type de données échangées : Tableau Javascript.
        $datagridScript .= 'this.DataSource.responseType = YAHOO.util.DataSource.TYPE_JSON;';
        // Définition du mode d'envoie des requêtes : les unes après les autres (pile).
        $datagridScript .= 'this.DataSource.connXhrMode = "queueRequests";';
        // Définitition du format des données reçues.
        $datagridScript .= 'this.DataSource.responseSchema = {';
        $datagridScript .= 'resultsList: "data", fields: [{key: "index"}';
        foreach ($this->columns as $column) {
            $datagridScript .= ', {key: "'.$column->id.'"}';
        }
        if ($this->deleteElements === true) {
            $datagridScript .= ', {key: "delete"}';
        }

        $datagridScript .= '],';
        // Ajout des informations complémentaires.
        $datagridScript .= 'metaFields: {reponseMessage: "message", totalRecords:"totalElements"}';
        $datagridScript .= '};';

        // Ajout de l'affichage du message de retour.
        // Verification du retour de donnees avant de les utiliser.
        // Affichage du message de retour dans les commentaires.
        $datagridScript .= 'this.DataSource.doBeforeParseData = function(oRequest, oFullResponse, oCallback) {';
        $datagridScript .= 'if ((oFullResponse.message != null) && (oFullResponse.message != "")) {';
        $datagridScript .= 'addMessage(oFullResponse.message, "success");';
        $datagridScript .= '}';
        $datagridScript .= 'if ((oFullResponse.data.length == 0) && (oFullResponse.totalElements > 0)) {';
        $datagridScript .= $this->id.'.filter();';
        $datagridScript .= '}';
        $datagridScript .= 'return oFullResponse;';
        $datagridScript .= '};';

        // Configuration du format de la requête.
        // Récupération des éléments de tri et de pagination de la datagrid.
        $datagridScript .= 'this.requestBuilder = function(oState, oSelf) {';
        $datagridScript .= 'oState = oState || {pagination:null, sortedBy:null};';
        $datagridScript .= 'if (oState.sortedBy) {';
        $datagridScript .= 'var sort = '.$this->id.'.Datagrid.getColumn(oState.sortedBy.key).sortOptions.field;';
        $datagridScript .= '} else {';
        $datagridScript .= 'var sort = null;';
        $datagridScript .= '}';
        $datagridScript .= 'if (oState.sortedBy && oState.sortedBy.dir === YAHOO.widget.DataTable.CLASS_ASC) {';
        $datagridScript .= 'var dir = true;';
        $datagridScript .= '} else {';
        $datagridScript .= 'var dir = false;';
        $datagridScript .= '}';
        $datagridScript .= 'if (oState.pagination) {';
        $datagridScript .= 'var startIndex =  oState.pagination.recordOffset;';
        $datagridScript .= 'var results = oState.pagination.rowsPerPage;';
        $datagridScript .= '} else {';
        $datagridScript .= 'var startIndex = 0;';
        $datagridScript .= 'var results = null;';
        $datagridScript .= '}';
        // Ajout du filtre à la requete.
        $datagridScript .= 'var filter = \'\';';
        $datagridScript .= 'filter += "{ ";';
        if ($this->hasFilter() === true) {
            $filters = array_merge($this->columns, $this->customFilters);
            /** @var Column\GenericColumn $column */
            foreach ($filters as $column) {
                if ($column->criteriaFilterAttribute !== null) {
                    $datagridScript .= $column->getFilterValue($this);
                }
            }
            $datagridScript .= 'filter = filter.substr(0, filter.length-1);';
        }
        $datagridScript .= 'filter += "}";';
        // Mise à jour de l'indicateur de présence du filtre.
        $datagridScript .= '$(\'#'.$this->id.'_filter legend i.filterActive\').remove();';
        $datagridScript .= 'if (filter != \'{}\') {';
        $datagridScript .= '$(\'#'.$this->id.'_filter legend\').append(\'';
        $datagridScript .= ' <i class="filterActive fa fa-filter" title="'.$this->filterCollapseActiveHint.'"></i>';
        $datagridScript .= '\');';
        $datagridScript .= '}';
        // Création de requête.
        $datagridScript .= 'return "getelements';
        $datagridScript .= $this->encodeParameters();
        $datagridScript .= '/sortColumn/" + sort + "';
        $datagridScript .= '/sortDirection/" + dir + "';
        $datagridScript .= '/nbElements/" + results + "';
        $datagridScript .= '/startIndex/" + startIndex + "';
        $datagridScript .= '/filters/" + encodeURIComponent(filter);';
        $datagridScript .= '};';

        // Création d'une variable permettant de savoir si la requête Initiale a déjà été envoyé.
        if ($this->pagination === true) {
            $datagridScript .= 'this.initialRequest = '.($this->initialLoading ? 'true' : 'false').';';
        }
        // Ajout de la configuration de la datagrid.
        $datagridScript .= 'this.Configs = {';
        $datagridScript .= 'MSG_EMPTY: "'.$this->datagridEmptyText.'",';
        $datagridScript .= 'MSG_ERROR: "'.$this->datagridErrorText.'",';
        $datagridScript .= 'MSG_LOADING: "'.$this->datagridLoadingText.'",';
        // Ajout du Paginator si nécéssaire.
        // Paginator : par defaut 10 lignes affiches, option 5,10,25,50 lignes à afficher, 5 pages maximum en liens.
        if ($this->pagination === true) {
            // Récupération de l'index de départ par défault.
            if ($datagridSession['startIndex'] !== null) {
                $initialStartIndex = $datagridSession['startIndex'];
                if ($datagridSession['nbElements'] === 0) {
                    $startPage = 1;
                    $datagridSession['nbElements'] = $this->paginationRowPerPage;
                } else {
                    $startPage = $initialStartIndex / $datagridSession['nbElements'] + 1;
                }
            } else {
                $initialStartIndex = 0;
                $startPage = 1;
            }
            // Définition du template de pagination.
            $templatePagination = '';
            // Début du bloc permettant de contextualiser la pagination.
            $templatePagination .= '<span class=\"contextPagination\">';
            // Liens vers la première page et la page précédente.
            $templatePagination .= '{FirstPageLink}{PreviousPageLink}';
            // Index des éléments et liste déroulante des pages.
            $templatePagination .= ' {CurrentPageReport}';
            // Liens vers la page suivante et la dernière.
            $templatePagination .= '{NextPageLink}{LastPageLink}';
            // Fin du bloc permettant de contextualiser la pagination.
            $templatePagination .= ' | </span>';
            // Ajout du texte pour changer le nombre de lignes par page.
            $templatePagination .= $this->labelPaginationPerPage.' : {RowsPerPageDropdown}';

            $datagridScript .= 'paginator: new YAHOO.widget.Paginator({';
            $datagridScript .= 'containers: new Array("'.$this->id.'_paginationBefore", ';
            $datagridScript .= '"'.$this->id.'_paginationAfter"),';
            $datagridScript .= 'rowsPerPage: '.$datagridSession['nbElements'].',';
            $datagridScript .= 'initialPage: '.$startPage.',';
            $datagridScript .= 'totalRecords: '.($initialStartIndex + 1).',';
            $datagridScript .= 'template: "'.$templatePagination.'",';
            $datagridScript .= 'firstPageLinkLabel : "'.$this->paginationFirstPage.'",';
            $datagridScript .= 'lastPageLinkLabel : "'.$this->paginationLastPage.'",';
            $datagridScript .= 'previousPageLinkLabel : "'.$this->paginationPreviousPage.'",';
            $datagridScript .= 'nextPageLinkLabel : "'.$this->paginationNextPage.'",';
            $datagridScript .= 'pageReportValueGenerator : function(paginator) {';
            $datagridScript .= 'var curPage = paginator.getCurrentPage(), i = 1, liste = \'\';';
            $datagridScript .= 'liste += \'</span>\';';
            $datagridScript .= 'liste += \'<select ';
            $datagridScript .= 'onchange="'.$this->id.'.filter(this.options[this.selectedIndex].value);">\';';
            $datagridScript .= 'while (i <= paginator.getTotalPages()) {';
            $datagridScript .= 'if ((('.$this->id.'.initialRequest == true) && (i == '.$startPage.')) ';
            $datagridScript .= '|| (i == paginator.getCurrentPage())) {';
            $datagridScript .= 'liste += \'<option selected="selected" value="\' + i + \'">\' + i + \'</option>\';';
            $datagridScript .= '} else {';
            $datagridScript .= 'liste += \'<option value="\' + i + \'">\' + i + \'</option>\';';
            $datagridScript .= '}';
            $datagridScript .= 'i++;';
            $datagridScript .= '}';
            $datagridScript .= 'liste += \'</select>\';';
            $datagridScript .= $this->id.'.initialRequest = false;';
            $datagridScript .= 'var pageRecords = paginator.getPageRecords();';
            $datagridScript .= 'if (pageRecords == null) {';
            $datagridScript .= 'var startRecord = 1;';
            $datagridScript .= 'var endRecord = 1 + paginator.getRowsPerPage();';
            $datagridScript .= '} else {';
            $datagridScript .= 'var startRecord = pageRecords[0] + 1;';
            $datagridScript .= 'var endRecord = pageRecords[1] + 1;';
            $datagridScript .= '}';
            $datagridScript .= 'return {';
            $datagridScript .= 'startRecord: startRecord,';
            $datagridScript .= 'endRecord: endRecord,';
            $datagridScript .= 'totalRecords: paginator.getTotalRecords(),';
            $datagridScript .= 'listePages: liste,';
            $datagridScript .= 'totalPage: paginator.getTotalPages()';
            $datagridScript .= '};';
            $datagridScript .= '},';
            $datagridScript .= 'pageReportTemplate : "'.$this->labelPaginationPage;
            $datagridScript .= ' : {listePages} / {totalPage} ';
            $datagridScript .= '({startRecord} - {endRecord} / {totalRecords})",';
            $datagridScript .= 'rowsPerPageOptions : [';
            foreach ($this->paginationOptionsRowPerPage as $option) {
                $datagridScript .= '{ value : '.$option.', text : "'.$option.'" },';
            }
            $datagridScript = substr($datagridScript, 0, -1);
            $datagridScript .= ']';
            $datagridScript .= '}),';
        }

        // Récupération du filtre par défault.
        if ($datagridSession['filters'] !== null) {
            $initialFilter = json_encode($datagridSession['filters']);
        } else {
            $initialFilter = '{}';
        }
        // Récupération du tri par défaut.
        if ($datagridSession['sortColumn'] !== null) {
            $initialSortName = $datagridSession['sortColumn'];
        } else {
            $initialSortName = null;
        }
        // Récupération de l'ordre de tri par défaut.
        if ($datagridSession['sortDirection'] !== null) {
            $this->defaultSorting['direction'] = $datagridSession['sortDirection'];
        }
        // Récupération de la colonne de tri sauvegardée.
        if (($this->defaultSorting['state'] === true) || ($datagridSession['sortColumn'] !== null)) {
            foreach ($this->columns as $column) {
                // Vérification que le nom du tri est bien défini pour cette colonne.
                if ($initialSortName !== null) {
                    if ($initialSortName === $column->getFullSortName($this)) {
                        $this->defaultSorting['column'] = $column->id;
                    }
                } elseif ($column->id === $this->defaultSorting['column']) {
                    if ($column->criteriaOrderAttribute !== null) {
                        $initialSortName = $column->getFullSortName($this);
                    } else {
                        throw new Core_Exception_InvalidArgument(
                            'Default sort : No Sorting id defined for this column.'
                        );
                    }
                }
            }
            $datagridScript .= 'sortedBy : { key: "'.$this->defaultSorting['column'].'"';
            // Mise en forme de la direction du tri
            if ($datagridSession['sortDirection'] !== null) {
                // Récupération de la direction du tri sauvegardée.
                if ($datagridSession['sortDirection'] == true) {
                    $initialSortDirection = 'true';
                } else {
                    $initialSortDirection = 'false';
                }
                if ($datagridSession['sortDirection'] === true) {
                    $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_ASC';
                } else {
                    $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_DESC';
                }
            } elseif ($this->defaultSorting['direction'] === Criteria::ASC) {
                $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_ASC';
                $initialSortDirection = 'true';
            } else {
                $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_DESC';
                $initialSortDirection = 'false';
            }
            $datagridScript .= '},';
            if ($this->pagination === true) {
                $datagridScript .= 'initialRequest : "getelements';
                $datagridScript .= $this->encodeParameters();
                $datagridScript .= '/nbElements/'.$datagridSession['nbElements'];
                $datagridScript .= '/startIndex/'.$initialStartIndex;
                $datagridScript .= '/sortColumn/'.$initialSortName;
                $datagridScript .= '/sortDirection/'.$initialSortDirection;
                $datagridScript .= '/filters/'.addslashes($initialFilter).'",';
            } else {
                $datagridScript .= 'initialRequest : "getelements';
                $datagridScript .= $this->encodeParameters();
                $datagridScript .= '/nbElements/null';
                $datagridScript .= '/startIndex/0';
                $datagridScript .= '/sortColumn/'.$initialSortName;
                $datagridScript .= '/sortDirection/'.$initialSortDirection;
                $datagridScript .= '/filters/'.addslashes($initialFilter).'",';
            }
        } elseif ($this->pagination === true) {
            $datagridScript .= 'initialRequest : "getelements';
            $datagridScript .= $this->encodeParameters();
            $datagridScript .= '/nbElements/'.$datagridSession['nbElements'];
            $datagridScript .= '/startIndex/'.$initialStartIndex;
            $datagridScript .= '/sortColumn/null';
            $datagridScript .= '/sortDirection/false';
            $datagridScript .= '/filters/'.addslashes($initialFilter).'",';
        } else {
            $datagridScript .= 'initialRequest : "getelements';
            $datagridScript .= $this->encodeParameters();
            $datagridScript .= '/nbElements/null';
            $datagridScript .= '/startIndex/0';
            $datagridScript .= '/sortColumn/null';
            $datagridScript .= '/sortDirection/false';
            $datagridScript .= '/filters/'.addslashes($initialFilter).'",';
        }
        if ($this->initialLoading === true) {
            $datagridScript .= 'initialLoad: true,';
        } else {
            $datagridScript .= 'initialLoad: false,';
        }
        $datagridScript .= 'generateRequest: this.requestBuilder, dynamicData: true';
        $datagridScript .= '};';

        // Attribut datagrid qui contient l'objet YUI.
        $datagridScript .= 'this.Datagrid = new YAHOO.widget.DataTable(';
        $datagridScript .= '"'.$this->id.'_container", ';
        $datagridScript .= 'this.Columns, this.DataSource, this.Configs';
        $datagridScript .= ');';
        // Ajout de la mise à jour du nombre total d'éléments lors de la mise à jour des données.
        $datagridScript .= 'this.Datagrid.handleDataReturnPayload = function(oRequest, oResponse, oPayload) {';
        $datagridScript .= 'oPayload.totalRecords = oResponse.meta.totalRecords;';
        $datagridScript .= 'return oPayload;';
        $datagridScript .= '};';
        if ($this->pagination === true) {
            // Ajout de la contextualisation de la pagination.
            $datagridScript .= 'this.DisplayPaginationContext = function() {';
            $datagridScript .= 'var elementsPagination = YAHOO.util.Dom.getElementsByClassName(';
            $datagridScript .= '"contextPagination"';
            $datagridScript .= ');';
            $datagridScript .= 'var totalRecors = '.$this->id.'.Datagrid.getState().totalRecords;';
            $datagridScript .= 'var paginationRecords = ';
            $datagridScript .= $this->id.'.Datagrid.getState().pagination.paginator.getRowsPerPage();';
            $datagridScript .= 'if (paginationRecords >= totalRecors) {';
            $datagridScript .= 'for (var i = 0; i < elementsPagination.length; i++) {';
            $datagridScript .= 'elementsPagination[i].style.display = "none";';
            $datagridScript .= '}';
            $datagridScript .= '} else {';
            $datagridScript .= 'for (var i = 0; i < elementsPagination.length; i++) {';
            $datagridScript .= 'elementsPagination[i].style.display = "inline";';
            $datagridScript .= '}';
            $datagridScript .= '}';
            $datagridScript .= '};';
            $datagridScript .= 'this.Datagrid.getState().pagination.paginator.subscribe(';
            $datagridScript .= '"render", this.DisplayPaginationContext';
            $datagridScript .= ');';
            $datagridScript .= 'this.Datagrid.getState().pagination.paginator.subscribe(';
            $datagridScript .= '"rowsPerPageChange", ';
            $datagridScript .= 'this.DisplayPaginationContext';
            $datagridScript .= ');';
        }

        // Gestion du masque de Chargement avec deux fonctions.
        $datagridScript .= 'this.StartLoading = function() {setMask(true);';
        $datagridScript .= ''.$this->id.'.Datagrid.showTableMessage(';
        $datagridScript .= $this->id.'.Datagrid.get("MSG_LOADING")';
        $datagridScript .= ');';
        $datagridScript .= 'return true;';
        $datagridScript .= '};';
        $datagridScript .= 'this.EndLoading = function() {';
        $datagridScript .= 'setMask(false);';
        $datagridScript .= $this->id.'.Datagrid.hideTableMessage();';
        $datagridScript .= 'return true;';
        $datagridScript .= '};';
        // Affichage en cas de tri sur une colonne.
        foreach ($this->columns as $column) {
            if ($column->criteriaOrderAttribute !== null) {
                $datagridScript .= 'this.Datagrid.doBeforeSortColumn = this.StartLoading;';
                break;
            }
        }
        // Affichage du Masque lors d'un changement dans la pagination.
        if ($this->pagination === true) {
            $datagridScript .= 'this.Datagrid.getState().pagination.paginator.subscribe(';
            $datagridScript .= '"changeRequest", this.StartLoading';
            $datagridScript .= ');';
        }
        // Effacemenent lorsque que la Datagrid reçoit des données.
        $datagridScript .= 'this.Datagrid.subscribe("dataReturnEvent", this.EndLoading);';

        // Ajout de la mise en valeur des cellules editables lors du survol.
        if ($this->highlightEditableCell === true) {
            $datagridScript .= '
                    this.highlightEditableCell = function(oArgs) {
                        var elCell = oArgs.target;
                        if (YAHOO.util.Dom.hasClass(elCell, "yui-dt-editable")) {';
            if (($this->highlightElement === false) && ($this->selectableElement === false)) {
                $datagridScript .= '
                            this.highlightCell(elCell);';
            } elseif ($this->selectableElement === false) {
                $datagridScript .= '
                            this.selectCell(elCell);';
            } else {
                $datagridScript .= '
                            if (this.isSelected(elCell)) {
                                this.highlightCell(elCell);
                            } else {
                                this.selectCell(elCell);
                            }';
            }
            $datagridScript .= '
                        }
                    };
                    this.unhighlightEditableCell = function(oArgs) {
                        var elCell = oArgs.target;';
            if (($this->highlightElement === false) && ($this->selectableElement === false)) {
                $datagridScript .= '
                        this.unhighlightCell(elCell);';
            } elseif ($this->selectableElement === false) {
                $datagridScript .= '
                        this.unselectCell(elCell);';
            } else {
                $datagridScript .= '
                        this.unhighlightCell(elCell);
                        this.unselectCell(elCell);';
            }
            $datagridScript .= '
                    };
                    this.Datagrid.subscribe("cellMouseoverEvent", this.highlightEditableCell);
                    this.Datagrid.subscribe("cellMouseoutEvent", this.unhighlightEditableCell);';
        }

        // Ajout de la mise en valeur d'une ligne au survol.
        if ($this->highlightElement === true) {
            $datagridScript .= 'this.Datagrid.subscribe("rowMouseoverEvent", this.Datagrid.onEventHighlightRow);';
            $datagridScript .= 'this.Datagrid.subscribe("rowMouseoutEvent", this.Datagrid.onEventUnhighlightRow);';
        }
        // Ajout de la mise en valeur d'une ligne lors d'un clique sur l'une de ses cellules.
        if ($this->selectableElement === true) {
            $datagridScript .= 'this.getSelectedLines = function() {';
            $datagridScript .= 'var lignesSelectionnees = '.$this->id.'.Datagrid.getSelectedRows();';
            $datagridScript .= 'var indexLignes = new Array();';
            $datagridScript .= 'var x;';
            $datagridScript .= 'for(x in lignesSelectionnees) {';
            $datagridScript .= 'var ligne = document.getElementById(lignesSelectionnees[x]);';
            $datagridScript .= 'indexLignes[x] = ligne.cells[0].getElementsByTagName("div")[0].innerHTML;';
            $datagridScript .= '}';
            $datagridScript .= 'return indexLignes;';
            $datagridScript .= '};';
            $datagridScript .= 'this.Datagrid.subscribe("rowClickEvent", this.Datagrid.onEventSelectRow);';
        }

        // Ajout d'une fonction permettant de filtrer la datagrid.
        $datagridScript .= 'this.filter = function(page) {';
        $datagridScript .= 'var oState, request, oCallback;';
        $datagridScript .= 'oState = '.$this->id.'.Datagrid.getState();';
        // Si la pagination est activé il faut vérifier que la page voulue est différente de l'actuelle.
        if ($this->pagination == true) {
            $datagridScript .= 'if (typeof(page) == "undefined") {';
            $datagridScript .= 'page = 1;';
            $datagridScript .= '}';
            $datagridScript .= 'startIndex = (page-1)*oState.pagination.paginator.getRowsPerPage();';
            $datagridScript .= 'if (oState.pagination.paginator.getStartIndex() != startIndex) {';
            $datagridScript .= 'oState.pagination.paginator.setStartIndex(startIndex, false);';
            $datagridScript .= '} else {';
        }
        $datagridScript .= $this->id.'.StartLoading();';
        $datagridScript .= 'oCallback = {';
        $datagridScript .= 'success : '.$this->id.'.Datagrid.onDataReturnInitializeTable,';
        $datagridScript .= 'failure : '.$this->id.'.Datagrid.onDataReturnInitializeTable,';
        $datagridScript .= 'argument : '.$this->id.'.Datagrid.getState(),';
        $datagridScript .= 'scope : '.$this->id.'.Datagrid';
        $datagridScript .= '};';
        $datagridScript .= 'request = this.requestBuilder(oState, this.Datagrid, oCallback);';
        $datagridScript .= 'this.Datagrid.getDataSource().sendRequest(request, oCallback);';
        if ($this->pagination == true) {
            $datagridScript .= '}';
        }
        $datagridScript .= '};';

        // Ajout des évenements lors d'un double click sur une cellule.
        $datagridScript .= 'this.actionDoubleClick = function(oArgs) {';
        $datagridScript .= 'var target = oArgs.target;';
        $datagridScript .= 'var column = this.getColumn(target);';
        $datagridScript .= 'var record = this.getRecord(target);';
        $datagridScript .= 'var sData = record.getData(column.key);';
        $datagridScript .= 'switch (column.key) {';
        // Edition des cellules modifiables.
        $modifiable = false;
        foreach ($this->columns as $column) {
            if ($column->editable === true) {
                $modifiable = true;
                $datagridScript .= 'case "'.$column->id.'":';
            }
        }
        if ($modifiable === true) {
            $datagridScript .= 'column.editor.asyncSubmitter = function(callback, newValue) {';
            $datagridScript .= $this->id.'.StartLoading();';
            $datagridScript .= 'var record = this.getRecord();';
            $datagridScript .= 'var column = this.getColumn();';
            $datagridScript .= 'var sData = record.getData(column.key);';
            $datagridScript .= 'YAHOO.util.Connect.asyncRequest(';
            $datagridScript .= '"POST",';
            $datagridScript .= '"'.$this->getActionUrl('updateelement').'",';
            $datagridScript .= '{';
            $datagridScript .= 'success:function(o) {';
            $datagridScript .= 'var r = YAHOO.lang.JSON.parse(o.responseText);';
            $datagridScript .= 'callback(true, r.data);';
            $datagridScript .= 'if ((r.message != null) && (r.message != "")) {';
            $datagridScript .= 'addMessage(r.message, "success");';
            $datagridScript .= '}';
            if ($this->automaticFiltering === true) {
                if ($this->pagination === true) {
                    $datagridScript .= 'var paginator = '.$this->id.'.Datagrid.getState().pagination.paginator;';
                    $datagridScript .= 'var currentPage = paginator.getStartIndex() / paginator.getRowsPerPage();';
                    $datagridScript .= $this->id.'.filter(currentPage + 1);';
                } else {
                    $datagridScript .= $this->id.'.filter();';
                }
            }
            $datagridScript .= $this->id.'.EndLoading();';
            $datagridScript .= '},';
            $datagridScript .= 'failure:function(o) {';
            $datagridScript .= 'errorHandler(o);';
            $datagridScript .= $this->id.'.EndLoading();';
            $datagridScript .= 'callback(true, sData);';
            $datagridScript .= '},';
            $datagridScript .= 'scope:this';
            $datagridScript .= '},';
            $datagridScript .= '"index=" + record._oData.index ';
            $datagridScript .= '+ "&column=" + column.key ';
            $datagridScript .= '+ "&value=" + encodeURIComponent(newValue).replace(/%0A/gi, "<br>")';
            $datagridScript .= ');';
            $datagridScript .= '};';
            $datagridScript .= 'if ((sData == null)';
            $datagridScript .= ' || (typeof(sData) != "object")';
            $datagridScript .= ' || (typeof(sData.editable) == "undefined")';
            $datagridScript .= ' || (sData.editable != false)';
            $datagridScript .= ') {';
            foreach ($this->columns as $column) {
                if ($column->editable === true) {
                    $datagridScript .= 'if (column.key == "'.$column->id.'") {';
                    $datagridScript .= $column->getEditor($this);
                    $datagridScript .= '}';
                }
            }
            $datagridScript .= '}';
            $datagridScript .= 'break;';
        }
        $datagridScript .= 'default:';
        $datagridScript .= 'break;';
        $datagridScript .= '}';
        $datagridScript .= '};';
        $datagridScript .= 'this.Datagrid.subscribe("cellDblclickEvent", this.actionDoubleClick);';
        $datagridScript .= 'this.Datagrid.onEditorBlurEvent = function(oArgs) {return};';

        if ($this->deleteElements === true) {
            // Définition de la fonction de suppression.
            $datagridScript .= 'this.delete = function(target) {';
            $datagridScript .= $this->id.'.StartLoading();';
            $datagridScript .= 'var record = '.$this->id.'.Datagrid.getRecord(parseInt(target));';
            $datagridScript .= 'YAHOO.util.Connect.asyncRequest(';
            $datagridScript .= '"POST",';
            $datagridScript .= '"'.$this->getActionUrl('deleteelement').'",';
            $datagridScript .= '{';
            $datagridScript .= 'success: function(o) {';
            $datagridScript .= 'var r = YAHOO.lang.JSON.parse(o.responseText);';
            $datagridScript .= $this->id.'.Datagrid.deleteRow(target);';
            $datagridScript .= 'if ((r.message != null) && (r.message != "")) {';
            $datagridScript .= 'addMessage(r.message,"success");';
            $datagridScript .= '}';
            if ($this->automaticFiltering === true) {
                if ($this->pagination === true) {
                    $datagridScript .= 'var paginator = '.$this->id.'.Datagrid.getState().pagination.paginator;';
                    $datagridScript .= 'var currentPage = paginator.getStartIndex() / paginator.getRowsPerPage();';
                    $datagridScript .= $this->id.'.filter(currentPage + 1);';
                } else {
                    $datagridScript .= $this->id.'.filter();';
                }
            }
            $datagridScript .= $this->id.'.EndLoading();';
            $datagridScript .= '},';
            $datagridScript .= 'failure: function(o) {';
            $datagridScript .= 'errorHandler(o);';
            $datagridScript .= $this->id.'.EndLoading();';
            $datagridScript .= '},';
            $datagridScript .= 'scope:this';
            $datagridScript .= '},';
            $datagridScript .= '"index=" + record.getData("index")';
            $datagridScript .= ');';
            $datagridScript .= '};';
        }

        // Fin de la définition de l'objet datagrid.
        $datagridScript .= '};';

        // Instanciation de la datagrid.
        if ($this->initialLoading === true) {
            $datagridScript .= 'setMask(true);';
        }
        $datagridScript .= $this->id.' = new datagrid'.$this->id.'();';

        return $datagridScript;
    }

    /**
     * Fonction qui génère le code de la datagrid.
     *
     * @return string
     */
    protected function generateDatagrid()
    {
        $datagrid = '<div class="yui-skin-sam">';

        // Ajout de la pagination si nécéssaire.
        if ($this->pagination === true) {
            $divPaginationAvant = '<div id="'.$this->id.'_paginationBefore" class="yui-dt-paginator"></div>';
            $divPaginationApres = '<div id="'.$this->id.'_paginationAfter" class="yui-dt-paginator"></div>';
        } else {
            $divPaginationAvant = '';
            $divPaginationApres = '';
        }
        // Ajout de la datagrid dans le html.
        $datagrid .= $divPaginationAvant;
        $datagrid .= '<div id="'.$this->id.'_container"></div>';
        $datagrid .= $divPaginationApres;

        $datagrid .= '</div>';

        return $datagrid;
    }

    /**
     * Méthode renvoyant les scripts complémentaires nécéssaires à certaines colonnes.
     *
     * @return string
     */
    protected function getComplementaryColumnScript()
    {
        $complementScript = '';

        foreach ($this->columns as $column) {
            if ($column instanceof Column\PopupColumn) {
                $complementScript .= $column->getPopup($this)->getScript();
            }
        }

        return $complementScript;
    }

    /**
     * Méthode renvoyant l'html complémentaires nécéssaires à certaines colonnes.
     *
     * @return string
     */
    protected function generateColumnComplement()
    {
        $complementHTML = '';

        foreach ($this->columns as $column) {
            if ($column instanceof Column\PopupColumn) {
                $complementHTML .= $column->getPopup($this)->getHTML();
            }
        }

        return $complementHTML;
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param Datagrid $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    public static function addHeader($instance = null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        $view = $broker->view;
        // Ajout des feuilles de style.
        $view->headLink()->appendStylesheet('yui/build/datatable/assets/skins/sam/datatable.css');
        $view->headLink()->appendStylesheet('yui/build/paginator/assets/skins/sam/paginator.css');
        $view->headLink()->appendStylesheet('yui/build/calendar/assets/skins/sam/calendar.css');
        $view->headLink()->appendStylesheet('css/ui/datagrid.css');
        // Ajout des fichiers Javascript.
        $view->headScript()->appendFile('yui/build/yahoo-dom-event/yahoo-dom-event.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/element/element-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/json/json-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/connection/connection-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/datasource/datasource-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/datatable/datatable-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/paginator/paginator-min.js', 'text/javascript');
        $view->headScript()->appendFile('yui/build/calendar/calendar-min.js', 'text/javascript');

        UI_Form::addHeader();

        parent::addHeader($instance);
    }

    /**
     * Renvoie la session Datagrid correspondante.
     *
     * @return array
     */
    protected function getDatagridSession()
    {
        // Récupération de la session pour connaitre les préférences de l'utilisateur sur cette Datagrid.
        $container = \Core\ContainerSingleton::getContainer();
        $zendSessionDatagrid = new Zend_Session_Namespace($container->get('session.storage.name'));
        $idDatagrid = 'datagrid'.$this->id;
        if ((!(isset($zendSessionDatagrid->$idDatagrid))) || (!(is_array($zendSessionDatagrid->$idDatagrid)))) {
            $zendSessionDatagrid->$idDatagrid = array();
        }
        $datagridSession = &$zendSessionDatagrid->$idDatagrid;
        // Initialisation des filtres.
        if ((isset($datagridSession['nbElements'])) && ($datagridSession['nbElements'] !== null)) {
            $this->paginationRowPerPage = $datagridSession['nbElements'];
        } else {
            $datagridSession['nbElements'] = $this->paginationRowPerPage;
        }
        if (!(isset($datagridSession['filters']))) {
            $datagridSession['filters'] = null;
        }
        if (!(isset($datagridSession['sortColumn']))) {
            $datagridSession['sortColumn'] = null;
        }
        if (!(isset($datagridSession['sortDirection']))) {
            $datagridSession['sortDirection'] = null;
        }
        if (!(isset($datagridSession['startIndex']))) {
            $datagridSession['startIndex'] = null;
        }

        return $datagridSession;
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        // Ajout de la Datagrid.
        $script .= $this->getDatagridScript();

        // Ajout du filtre.
        if ($this->hasFilter() === true) {
            $script .= $this->getFilterScript();
        }

        // Ajout du panneau de suppression.
        if ($this->deleteElements === true) {
            $script .= $this->getDeleteScript();
        }

        // Ajout du panneau d'ajout.
        if ($this->addElements === true) {
            $script .= $this->getAddScript();
        }
        // Ajout des éléments spécifiques des colonnes.
        $script .= $this->getComplementaryColumnScript();

        return $script;
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @return string
     */
    public function getHTML()
    {
        $html = '';

        // Ajout du filtre.
        if ($this->hasFilter() === true) {
            $html .= $this->generateFilter();
        }

        // Ajout de la Datagrid.
        $html .= $this->generateDatagrid();

        // Ajout du panneau de suppression.
        if ($this->deleteElements === true) {
            $html .= $this->generateDelete();
        }

        // Ajout du panneau d'ajout.
        if ($this->addElements === true) {
            $html .= $this->generateAdd();
        }

        // Ajout des éléments spécifiques des colonnes.
        $html .= $this->generateColumnComplement();

        return $html;
    }
}
