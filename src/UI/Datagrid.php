<?php
/**
 * Fichier de la classe Datagrid.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of Datagrid.
 *
 * Une classe permettant de génèrer un Tableau de données (datagrid) très simplement.
 *
 * @package    UI
 * @subpackage Datagrid
 *
 * @see   UI_Controller_Datagrid
 */
class UI_Datagrid extends UI_Generic
{
    /**
     * Constante definissant une direction de tri de colonne.
     * ici un tri ascendant.
     *
     */
    const TYPE_SORT_ASC = Core_Model_Order::ORDER_ASC;

    /**
     * Constante definissant une direction de tri de colonne.
     * ici un tri descendant.
     *
     */
    const TYPE_SORT_DESC = Core_Model_Order::ORDER_DESC;

    /**
     * Définition du message affiché dans la datagrid lorqu'elle est vide.
     *
     * @var   string
     */
    public $datagridEmptyText = null;

    /**
     * Définition du message affiché dans la datagrid lorqu'elle contient une erreur.
     *
     * @var   string
     */
    public $datagridErrorText = null;

    /**
     * Définition du message affiché dans la datagrid lorqu'elle est en chargement.
     *
     * @var   string
     */
    public $datagridLoadingText = null;

    /**
     * Définition du titre par défaut du Collapse entourant le filtre affiché au dessus du datagrid.
     *
     * @var   string
     */
    public $filterCollapseTitle = null;

    /**
     * Définition de l'aide affiché au survol dans le cas d'un filtre précédemment sauvegardé.
     *
     * @var   string
     */
    public $filterCollapseActiveHint = null;

    /**
     * Définition du Collapse entourant le filtre affiché au dessus du datagrid.
     *
     * @var   UI_HTML_Collapse
     */
    public $filterCollapse = null;

    /**
     * Définition du bouton permettant de filtrer.
     *
     * @var UI_HTML_Button
     */
    public $filterConfirmButton = null;

    /**
     * Définition du bouton permettant de réinitialiser le filtre.
     *
     * @var UI_HTML_Button
     */
    public $filterResetButton = null;

    /**
     * Définition de l'icone permettant de réinitialiser indépendamment chaque champs du filtre.
     *
     * @var string
     */
    public $filterIconResetFieldSuffix = null;

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
     * Définition du formulaire affiché dans le popup d'ajout. Null = UI_Form par défaut.
     *
     * @var   UI_Form
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
     * Définition du titre affiché dans la colonne de suppression.
     *
     * @var   string
     */
    public $deleteElementTitle = null;

    /**
     * Définition du label affiché dans la cellule lorsque la suppression est possible.
     *
     * @var   string
     */
    public $deleteElementValue = null;

    /**
     * Définition du label affiché dans la cellule lorsque la suppression n'est pas possible.
     *
     * @var   string
     */
    public $deleteElementNullValue = null;

    /**
     * Définition du label affiché devant le select du choix du nombre d'élément par page.
     *
     * @var   string
     */
    public $paginationPerPage = null;

    /**
     * Définition du label affiché devant le select du choix de la page.
     *
     * @var   string
     */
    public $paginationPage = null;

    /**
     * Définition du nombre de ligne par page, pour la pagination de la datagrid.
     *
     * @var   int
     */
    public $paginationRowPerPage = null;

    /**
     * Définition des options du nombre de ligne par page, pour la pagination de la datagrid.
     *
     * @var   array
     */
    public $paginationOptionsRowPerPage = null;

    /**
     * Définition du texte menant à la première page, pour la pagination de la datagrid.
     *
     * @var   string
     */
    public $paginationFirstPage = null;

    /**
     * Définition du texte menant à la dernière page, pour la pagination de la datagrid.
     *
     * @var   string
     */
    public $paginationLastPage = null;

    /**
     * Définition du texte menant à la page précédante, pour la pagination de la datagrid.
     *
     * @var   string
     */
    public $paginationPreviousPage = null;

    /**
     * Définition du texte menant à la page suivante, pour la pagination de la datagrid.
     *
     * @var   string
     */
    public $paginationNextPage = null;


    /**
     * Identifiant unique de la Datagrid.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Nom du contrôleur ou la datagrid récuperera les données.
     *
     * @var   string
     */
    protected $_controller = null;

    /**
     * Nom (optionnel) du module ou la datagrid récuperera les données.
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
     * Tableau des colonnes de la datagrid.
     *
     * @var   array
     *
     * @see ajouterColonne
     * @see UI_Datagrid_Col_Generic
     */
    protected $_cols = array();

    /**
     * Permet de savoir si l'ajout d'éléments est présent dans la datagrid.
     *
     * Par défaut non actif.
     *
     * @var   bool
     *
     * @see setAddElement
     */
    public $addElements = false;

    /**
     * Permet de savoir si la suppression d'éléments est présente dans la datagrid.
     *
     * Par défaut non actif.
     *
     * @var   bool
     *
     * @see setDeleteElement
     */
    public $deleteElements = false;

    /**
     * Tableau de paramètres définissant le tri par défaut sur une datagrid.
     *
     * @var   array
     *
     * @see setDefaultSorting
     */
    protected $_defaultSortting = array();

    /**
     * Permet de savoir si la datagrid sera paginé.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $pagination = true;

    /**
     * Permet de savoir si les lignes de la datagrid peuvent être séléctionnées lors d'un clic.
     *
     * Par défaut non.
     *
     * @var   bool
     */
    public $selectableElement = false;

    /**
     * Permet de savoir si les cellules éditables de la datagrid sont mises en valeur au survol.
     *
     * Par défaut non.
     *
     * @var   bool
     */
    public $highlightEditableCell = false;

    /**
     * Permet de savoir si les lignes de la datagrid sont mises en valeur au survol.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $highlightElement = true;

    /**
     * Permet de savoir si les éléments devront être filtrés après une mise à jour.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $automaticFiltering = true;

    /**
     * Permet de savoir si le datagrid sera chargé à l'initialisation.
     *  Sinon il faudra passer par la fonction Filtrer du datagrid.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $initialLoading = true;

    /**
     * Tableau de filtres personnalisés (indépendant des colonnes).
     *
     * @var array
     *
     * @see ajouterFiltrePersonnalise
     */
    protected $_customFilters = array();


    /**
     * Constructeur de la classe Datagrid.
     *
     * @param string $id         Identifiant unique de la datagrid.
     * @param string $controller Nom du contrôleur de la datagrid.
     * @param string $module     Module ou se trouve le controleur de la datagrid.
     *
     */
    public function  __construct($id, $controller, $module=null)
    {
        $this->id = $id;
        $this->_controller = $controller;
        $this->_module = $module;

        $this->datagridEmptyText = __('UI', 'loading', 'empty');
        $this->datagridErrorText = __('UI', 'loading', 'error');
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
        $this->deleteElementValue = '<i class="icon-trash"></i> '.__('UI', 'verb', 'delete');
        $this->deleteElementNullValue = '';
        $this->deletePanelTitle = __('UI', 'deletionConfirmationPopup', 'title');
        $this->deletePanelText = __('UI', 'deletionConfirmationPopup', 'text');
        $this->deletePanelConfirmIcon = 'ok icon-white';
        $this->deletePanelConfirmLabel = __('UI', 'verb', 'confirm');
        $this->deletePanelCancelIcon = 'remove';
        $this->deletePanelCancelLabel = __('UI', 'verb', 'cancel');

        // Bouton et popup d'ajout
        $this->addButtonLabel = __('UI', 'verb', 'add');
        $this->addButtonIcon = 'plus-sign';
        $this->addPanelTitle = __('UI', 'name', 'addition');
        $this->addPanelConfirmIcon = 'ok icon-white';
        $this->addPanelConfirmLabel = __('UI', 'verb', 'validate');
        $this->addPanelCancelIcon = 'remove';
        $this->addPanelCancelLabel = __('UI', 'verb', 'cancel');

        // Filtres
        $this->filterCollapseTitle = __('UI', 'name', 'filters');
        $this->filterCollapseActiveHint = __('UI', 'datagridFilter', 'TitleFilterActive');
        $this->filterCollapse = new UI_HTML_Collapse();
        $this->filterCollapse->title = $this->filterCollapseTitle;
        $this->filterConfirmButton = new UI_HTML_Button(__('UI', 'verb', 'filter'));
        $this->filterConfirmButton->icon = 'zoom-in';
        $this->filterResetButton = new UI_HTML_Button(__('UI', 'verb', 'reset'));
        $this->filterResetButton->icon = 'zoom-out';
        $this->filterIconResetFieldSuffix = 'remove';
        $this->_defaultSortting['state'] = false;
        $this->_defaultSortting['column'] = null;
        $this->_defaultSortting['direction'] = self::TYPE_SORT_ASC;
    }

    /**
     * Fonction que permet d'ajouter des paramètres à la datagrid.
     *
     * Ces paramètres seront envoyés par l'url à chaque requête.
     *
     * @param string              $parameterName    Nom du paramètre indiqué dans l'url.
     * @param mixed(string|array) $parameterValue Valeur du paramètre qui sera transmis.
     *
     * @return void
     */
    public function addParam($parameterName, $parameterValue)
    {
        $this->_parameters[$parameterName] = $parameterValue;
    }


    /**
     * Fonction qui permet d'ajouter des colonnes à la datagrid.
     *
     * @param UI_Datagrid_Col_Generic $column Colonne à rajouter.
     * @param int                     $ordre =null   Position de la colonne.
     *
     * @return void
     */
    public function addCol($column, $ordre=null)
    {
        if (($column->id === 'index') || ($column->id === 'delete')) {
            throw new Core_Exception_InvalidArgument('Can\'t with id "index" or "delete" !');
        }

        if ($ordre !== null) {
            $this->_cols[$ordre] = $column;
        } else {
            $this->_cols[] = $column;
        }
    }

    /**
     * Fonction qui définit la colonne et la direction du tri par défaut dans la datagrid.
     *
     * Attention : la colonne doit avoir été ajouté avant et son nomTri doit être défini.
     *
     * @param int   $idColumn    Identifiant de la colonne suivant laquelle trier par défaut.
     * @param const $sortDirection Direction du tri.
     *
     * @return void
     *
     * @see TYPE_SORT_ASC
     * @see TYPE_SORT_DESC
     */
    public function setDefaultSorting($idColumn, $sortDirection=self::TYPE_SORT_ASC)
    {
        foreach ($this->_cols as $column) {
            // Vérification que le nom du tri est bien défini pour cette colonne
            if (($column->id === $idColumn) && ($column->sortName !== null)) {
                $this->_defaultSortting['state'] = true;
                $this->_defaultSortting['column'] = $idColumn;
                $this->_defaultSortting['direction'] = $sortDirection;
            }
        }
    }

    /**
     * Fonction qui permet d'ajouter des filtres indépendants des colonnes.
     *
     * @param UI_Datagrid_Col_Generic $col Colonne sur laquelle sera basé le filtre.
     *
     * @return void
     *
     * @see _customFilters
     */
    public function addFilter($col)
    {
        $this->_customFilters[] = $col;
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

        $url .= 'idDatagrid=' . $this->id . '&';
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
     * Indique si la Datagrid possède un filtre.
     *
     * @return bool
     */
    protected function hasFilter()
    {
        if (count($this->_customFilters) > 0) {
            return true;
        } else {
            foreach ($this->_cols as $column) {
                if ($column->filterName !== null) {
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
        $this->filterCollapse->id = $this->id.'_filter';

        $datagridSession = $this->getDatagridSession();
        $this->filterCollapse->foldedByDefault = true;
        // Vérification de la présence de valeur par défaut nécéssitant l'affichage du l'indicateur.
        if (($datagridSession['filters'] !== null) && (count($datagridSession['filters']) != 0)) {
            $this->filterCollapse->title = $this->filterCollapseTitle.
                ' <i class="filterActive icon-filter" title="'.$this->filterCollapseActiveHint.'"></i>';
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
        $formFilter = new UI_Form($this->id.'_filterForm');
        $filters = array_merge($this->_cols, $this->_customFilters);
        foreach ($filters as $column) {
            if ($column->filterName !== null) {
                if (isset($datagridSession['filters'][$column->getFullFilterName($this)])) {
                    $defaultValue = $datagridSession['filters'][$column->getFullFilterName($this)];
                } else {
                    $defaultValue = null;
                }
                $columnFilterElement = $column->getFilterFormElement($this, $defaultValue);
                if ($columnFilterElement !== null) {
                    $formFilter->addElement($columnFilterElement);
                }
            }
        }
        $filterElement = new UI_Form_Element_HTML($this->id.'-filter');
        $this->filterConfirmButton->addAttribute('onclick', $this->id.'.filter();');
        $filterElement->content = $this->filterConfirmButton->getHTML();
        $formFilter->addActionElement($filterElement);
        $resetElement = new UI_Form_Element_HTML($this->id.'-resetFilter');
        $this->filterResetButton->addAttribute('onclick', $this->id.'.resetFilter();'.$this->id.'.filter();');
        $resetElement->content = $this->filterResetButton->getHTML();
        $formFilter->addActionElement($resetElement);

        $this->filterCollapse->body = $formFilter->getHTML();

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
        $datagridSession = $this->getDatagridSession();

        $filterScript = '';

        // Récupération des scripts des éléments du formulaire.
        $filters = array_merge($this->_cols, $this->_customFilters);
        foreach ($filters as $column) {
            if ($column->filterName !== null) {
                if (isset($datagridSession['script'][$column->getFullFilterName($this)])) {
                    $defaultValue = $datagridSession['script'][$column->getFullFilterName($this)];
                } else {
                    $defaultValue = null;
                }
                $columnFilterElement = $column->getFilterFormElement($this, $defaultValue);
                if ($columnFilterElement !== null) {
                    $filterScript .= $columnFilterElement->getElement()->getScript();
                }
            }
        }

        $filterScript .= $this->filterCollapse->getScript();

        // Ajout de la fonction Reinitialiser à la datagrid
        $filterScript .= $this->id.'.resetFilter = function() {';
        $filterScript .= '$(\'#'.$this->id.'_filter legend i.filterActive\').remove();';
        $filterScript .= '$(\'#'.$this->id.'_filter_wrapper\').collapse(\'hide\');';
        foreach ($filters as $column) {
            if ($column->filterName !== null) {
                $filterScript .= $column->getResettingFilter($this);
            }
        }
        $filterScript .= '};';
        //  Ajout du déclenchement de cette fonction sur l'evenement onlick.
        $filterScript .= '$(\'#'.$this->id.'.resetFilter\').click(function(){';
        $filterScript .= $this->id.'.resetFilter();';
        $filterScript .= $this->id.'.filter();';
        $filterScript .= '});';

        return $filterScript;
    }

    /**
     * Initialise le formulaire d'ajout.
     */
    protected function initAddForm()
    {
        if ($this->addPanelForm !== null) {
            $this->addPanelForm->setRef($this->id.'_addForm');
        } else {
            $this->addPanelForm = new UI_Form($this->id.'_addForm');
            foreach ($this->_cols as $column) {
                if ($column->addable == true) {
                    $columnAddElement = $column->getAddFormElement($this);
                    if ($columnAddElement !== null) {
                        $this->addPanelForm->addElement($columnAddElement);
                    }
                }
            }
        }
        $this->addPanelForm->setAction($this->getActionUrl('addelement'));
        $this->addPanelForm->setAjax(null, 'parse'.$this->id.'AddFormValidation');
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

        // Ajout d'une fonction d'encapsulation de l'ajout.
        $addScript .= '$.fn.parse'.$this->id.'AddFormValidation = function(response) {';
        $addScript .= 'addMessage(response.message, response.type);';
        $addScript .= 'this.get(0).reset();';
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
        $addScript .= '};';

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

        $addButton = new UI_HTML_Button($this->addButtonLabel);
        $addButton->icon = $this->addButtonIcon;
        $addButton->link = '#';
        $addButton->addAttribute('data-toggle', 'modal');
        $addButton->addAttribute('data-remote', 'false');
        $addButton->addAttribute('data-target', '#'.$this->id.'_addPanel');
        $add .= $addButton->getHTML();

        $add .= '</div>';

        // Ajout du popup d'ajout.
        $buttonConfirmAddPanel = new UI_HTML_Button($this->addPanelConfirmLabel);
        $buttonConfirmAddPanel->icon = $this->addPanelConfirmIcon;
        $buttonConfirmAddPanel->addAttribute('class', 'btn-primary');
        $buttonConfirmAddPanel->addAttribute('onclick', '$(\'#'.$this->id.'_addForm\').submit();');

        $buttonCancelAddPanel = new UI_HTML_Button($this->addPanelCancelLabel);
        $buttonCancelAddPanel->icon = $this->addPanelCancelIcon;
        $buttonCancelAddPanel->link = '#';
        $buttonCancelAddPanel->addAttribute('data-dismiss', 'modal');
        $buttonCancelAddPanel->addAttribute('data-target', '#'.$this->id.'_addPanel');
        $resetAction = '$(\'#'.$this->id.'_addForm\').get(0).reset();$(\'#'.$this->id.'_addForm\').eraseFormErrors();';
        $buttonCancelAddPanel->addAttribute('onclick', $resetAction);

        $addPanel = new UI_Popup_Static($this->id.'_addPanel');
        $addPanel->addAttribute('class', 'large');
        $addPanel->title = $this->addPanelTitle;
        $addPanel->footer = $buttonConfirmAddPanel->getHTML().$buttonCancelAddPanel->getHTML();
        $addPanel->body = $this->addPanelForm->getHTML();
        $addPanel->closeWithClick = false;

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
        $deleteScript = '';

        // Ajout de la passation des information de la cellule au panel de suppression.
        // Ajout d'un listener sur chaque bouton ouvrant ce popup pour effectuer le chargement du contenu.
        $deleteScript .= '$(\'body\').on(\'click\', \'[data-target="#'.$this->id.'_deletePanel"]\', function(e) {';
        $deleteScript .= 'e.preventDefault();';
        $deleteScript .= '$(\'.btn-primary\', $(\'#'.$this->id.'_deletePanel\')).attr(';
        $deleteScript .= '\'onclick\',';
        $deleteScript .= '\''.$this->id.'.delete(\' + $(this).attr(\'href\').substring(1) + \');\'';
        $deleteScript .= ');';
        $deleteScript .= '});';

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
        $buttonConfirmDeletePanel = new UI_HTML_Button($this->deletePanelConfirmLabel);
        $buttonConfirmDeletePanel->icon = $this->deletePanelConfirmIcon;
        $buttonConfirmDeletePanel->addAttribute('class', 'btn-primary');
        $buttonConfirmDeletePanel->link = '#';
        $buttonConfirmDeletePanel->addAttribute('data-dismiss', 'modal');
        $buttonConfirmDeletePanel->addAttribute('data-target', '#'.$this->id.'_deletePanel');

        $buttonCancelDeletePanel = new UI_HTML_Button($this->deletePanelCancelLabel);
        $buttonCancelDeletePanel->icon = $this->deletePanelCancelIcon;
        $buttonCancelDeletePanel->addAttribute('class', 'btn');
        $buttonCancelDeletePanel->link = '#';
        $buttonCancelDeletePanel->addAttribute('data-dismiss', 'modal');
        $buttonCancelDeletePanel->addAttribute('data-target', '#'.$this->id.'_deletePanel');

        $deletePanel = new UI_Popup_Static($this->id.'_deletePanel');
        $deletePanel->title = $this->deletePanelTitle;
        $deletePanel->footer = $buttonConfirmDeletePanel->getHTML().$buttonCancelDeletePanel->getHTML();
        $deletePanel->body = $this->deletePanelText;

        $delete .= $deletePanel->getHTML();

        return $delete;
    }

    /**
     * Fonction qui génère le code de la datagrid.
     *
     * @return string
     */
    protected function getDatagridScript()
    {
        $datagridSession = $this->getDatagridSession();

        $datagridScript = '';

        // Définition d'un objet datagrid qui sera une surcouche de l'objet YUI.
        $datagridScript .= 'var datagrid'.$this->id.' = function() {';

        // Ajout des types personnalisés de colonnes.
        foreach ($this->_cols as $column) {
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
            $datagridScript .= '\'<span class="'.UI_Datagrid_Col_Generic::DISPLAY_TEXT_CENTER.'">\'';
            $datagridScript .= ' + content + ';
            $datagridScript .= '\'<\/span>\';';
            $datagridScript .= '};';
        }

        // Ajout de la définition des colonnes.
        $datagridScript .= ' this.Columns = [';
        $datagridScript .= '{key:"index", hidden:true}';
        foreach ($this->_cols as $column) {
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
        foreach ($this->_cols as $column) {
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
            $filters = array_merge($this->_cols, $this->_customFilters);
            foreach ($filters as $column) {
                if ($column->filterName !== null) {
                    $datagridScript .= $column->getFilterValue($this);
                }
            }
            $datagridScript .= 'filter = filter.substr(0, filter.length-1);';
        }
        $datagridScript .= 'filter += "}";';
        $datagridScript .= 'return "getelements?';
        $datagridScript .= $this->encodeParameters();
        $datagridScript .= 'sortColumn=" + sort + "';
        $datagridScript .= '&sortDirection=" + dir + "';
        $datagridScript .= '&nbElements=" + results + "';
        $datagridScript .= '&startIndex=" + startIndex + "';
        $datagridScript .= '&filters=" + encodeURIComponent(filter);';
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
                $startIndexInitial = $datagridSession['startIndex'];
                if ($datagridSession['nbElements'] === 0) {
                    $startPage = 1;
                    $datagridSession['nbElements'] = $this->paginationRowPerPage;
                } else {
                    $startPage = $startIndexInitial / $datagridSession['nbElements'] + 1;
                }
            } else {
                $startIndexInitial = 0;
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
            $datagridScript .= 'totalRecords: '.($startIndexInitial + 1).',';
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
            $defaultSortName = $datagridSession['sortColumn'];
        } else {
            $defaultSortName = null;
        }
        // Récupération de l'ordre de tri par défaut.
        if ($datagridSession['sortDirection'] !== null) {
            $this->_defaultSortting['direction'] = $datagridSession['sortDirection'];
        }
        // Récupération de la colonne de tri sauvegardée.
        if (($this->_defaultSortting['state'] === true) || ($datagridSession['sortColumn'] !== null)) {
            foreach ($this->_cols as $column) {
                // Vérification que le nom du tri est bien défini pour cette colonne.
                if ($defaultSortName !== null) {
                    if ($defaultSortName === $column->getFullSortName($this)) {
                        $this->_defaultSortting['column'] = $column->id;
                    }
                } else if ($column->id === $this->_defaultSortting['column']) {
                    if ($column->sortName !== null) {
                        $defaultSortName = $column->getFullSortName($this);
                    } else {
                        throw new Core_Exception_InvalidArgument(
                            'Default sort : No Sorting id defined for this column.'
                        );
                    }
                }
            }
            $datagridScript .= 'sortedBy : { key: "'.$this->_defaultSortting['column'].'"';
            // Mise en forme de la direction du tri
            if ($datagridSession['sortDirection'] !== null) {
                // Récupération de la direction du tri sauvegardée.
                if ($datagridSession['sortDirection'] == true) {
                    $initialSortRequest = 'true';
                } else {
                    $initialSortRequest = 'false';
                }
                if ($datagridSession['sortDirection'] === true) {
                    $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_ASC';
                } else {
                    $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_DESC';
                }
            } else if ($this->_defaultSortting['direction'] === self::TYPE_SORT_ASC) {
                $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_ASC';
                $initialSortRequest = 'true';
            } else {
                $datagridScript .= ', dir:YAHOO.widget.DataTable.CLASS_DESC';
                $initialSortRequest = 'false';
            }
            $datagridScript .= '},';
            if ($this->pagination === true) {
                $datagridScript .= 'initialRequest : "getelements?';
                $datagridScript .= $this->encodeParameters();
                $datagridScript .= 'nbElements='.$datagridSession['nbElements'];
                $datagridScript .= '&startIndex='.$startIndexInitial;
                $datagridScript .= '&sortColumn='.$defaultSortName;
                $datagridScript .= '&sortDirection='.$initialSortRequest;
                $datagridScript .= '&filters='.addslashes($initialFilter).'",';
            } else {
                $datagridScript .= 'initialRequest : "getelements?';
                $datagridScript .= $this->encodeParameters();
                $datagridScript .= 'nbElements=null';
                $datagridScript .= '&startIndex=0';
                $datagridScript .= '&sortColumn='.$defaultSortName;
                $datagridScript .= '&sortDirection='.$initialSortRequest;
                $datagridScript .= '&filters='.addslashes($initialFilter).'",';
            }
        } else if ($this->pagination === true) {
            $datagridScript .= 'initialRequest : "getelements?';
            $datagridScript .= $this->encodeParameters();
            $datagridScript .= 'nbElements='.$datagridSession['nbElements'];
            $datagridScript .= '&startIndex='.$startIndexInitial;
            $datagridScript .= '&sortColumn=null';
            $datagridScript .= '&sortDirection=false';
            $datagridScript .= '&filters='.addslashes($initialFilter).'",';
        } else {
            $datagridScript .= 'initialRequest : "getelements?';
            $datagridScript .= $this->encodeParameters();
            $datagridScript .= 'nbElements=null';
            $datagridScript .= '&startIndex=0';
            $datagridScript .= '&sortColumn=null';
            $datagridScript .= '&sortDirection=false';
            $datagridScript .= '&filters='.addslashes($initialFilter).'",';
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
//            $datagridScript .= 'this.Datagrid.getState().pagination.paginator.subscribe(';
//            $datagridScript .= '"render", this.DisplayPaginationContext';
//            $datagridScript .= ');';
//            $datagridScript .= 'this.Datagrid.getState().pagination.paginator.subscribe(';
//            $datagridScript .= '"rowsPerPageChange", ';
//            $datagridScript .= 'this.DisplayPaginationContext';
//            $datagridScript .= ');';
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
        foreach ($this->_cols as $column) {
            if ($column->sortName !== null) {
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
            } else if ($this->selectableElement === false) {
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
            } else if ($this->selectableElement === false) {
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
        foreach ($this->_cols as $column) {
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
            foreach ($this->_cols as $column) {
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
        $datagrid = '';

        $datagrid .= '<div class="yui-skin-sam">';

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

        foreach ($this->_cols as $column) {
            switch ($column->getType()) {
                case UI_Datagrid_Col_Generic::TYPE_COL_POPUP:
                case UI_Datagrid_Col_Generic::TYPE_COL_LONGTEXT:
                    $complementScript .= $column->getPopup($this)->getScript();
                    break;
                default:
                    break;
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

        foreach ($this->_cols as $column) {
            switch ($column->getType()) {
                case UI_Datagrid_Col_Generic::TYPE_COL_POPUP:
                case UI_Datagrid_Col_Generic::TYPE_COL_LONGTEXT:
                    $complementHTML .= $column->getPopup($this)->getHTML();
                    break;
                default:
                    break;
            }
        }

        return $complementHTML;
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_Datagrid $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    static function addHeader($instance=null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        // Ajout des feuilles de style.
        $broker->view->headLink()->appendStylesheet('yui/build/datatable/assets/skins/sam/datatable.css');
        $broker->view->headLink()->appendStylesheet('yui/build/paginator/assets/skins/sam/paginator.css');
        $broker->view->headLink()->appendStylesheet('yui/build/calendar/assets/skins/sam/calendar.css');
        $broker->view->headLink()->appendStylesheet('css/ui/datagrid.css');
        // Ajout des fichiers Javascript.
        $broker->view->headScript()->appendFile('yui/build/yahoo-dom-event/yahoo-dom-event.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/element/element-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/json/json-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/connection/connection-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/datasource/datasource-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/datatable/datatable-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/paginator/paginator-min.js', 'text/javascript');
        $broker->view->headScript()->appendFile('yui/build/calendar/calendar-min.js', 'text/javascript');

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
        $configuration = Zend_Registry::get('configuration');
        $zendSessionDatagrid = new Zend_Session_Namespace($configuration->sessionStorage->name.'_'.APPLICATION_ENV);
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
