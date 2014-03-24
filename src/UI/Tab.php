<?php
use MyCLabs\MUIH\GenericVoidTag;

/**
 * @author valentin.claras
 * @package UI
 * @subpackage Tab
 */

/**
 * Allow to create Tab to add in a TabView
 *
 * @package UI
 * @subpackage Tab
 */
class UI_Tab
{
    /**
     * Définition du message affiché lors du chargement du texte brute.
     *
     * @var string
     */
    public $loadingText = null;

    /**
     * Définition du message affiché lorsqu'une erreur se produit au chargement de texte brut.
     *
     * @var string
     */
    public $errorText = null;

    /**
     * Unique identifiant
     *
     * @var string
     */
    public $id;

    /**
     * The Tab's label text
     *
     * @var string
     */
    public $label = '';

    /**
     * Flag indicating whether or not the Tab is currently active.
     *
     * @var bool = false
     */
    public $active = false;

    /**
     * Flag indicating whether or not the Tab is currently disabled.
     *
     * @var bool = false
     */
    public $disabled = false;

    /**
     * If set, tab data is loaded dynamically from this url when tab is activated
     *
     * @var string
     */
    public $dataSource = null;

    /**
     * Flag indicating whether or not the content will be charged each time.
     *
     * @var bool = false
     */
    public $useCache = true;

    /**
     * The content displayed when the tab is active.
     *
     * @var string
     */
    public $content = '';


    /**
     * Constructor
     *
     * @param string $id
     * @param string $label
     * @param string $content
     */
    public function __construct($id, $label='', $content='')
    {
        $this->id = $id;
        $this->label = $label;
        $this->content = $content;

        $imageLoading = new GenericVoidTag('img');
        $imageLoading->setAttribute('src', 'images/ui/ajax-loader_large.gif');
        $this->loadingText = $imageLoading->getHTML().' '.__('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
    }

    /**
     * Renvoi l'id du tab pour un tab view donné.
     *
     * @param UI_Tab_View $tab
     *
     * @return string
     */
    public function getId($tab)
    {
        return $tab->id.'_'.$this->id;
    }

    /**
     * Renvoi le javascripts de l'interface.
     *
     * @param UI_Tab_View $tabView
     *
     * @return string
     */
    public function getScript($tabView)
    {
        $script = '';

        // Ajout d'un traitement pour empécher le fonctionnement du tab si il est désactivé.
        $script .= '$(\'#'.$this->getId($tabView).'_li\').click(function(e) {';
        $script .= 'if ($(\'#'.$this->getId($tabView).'_li\').hasClass(\'disabled\')) {';
        $script .= 'e.preventDefault();';
        $script .= 'return false;';
        $script .= '}';
        $script .= '});';

        if ($this->dataSource !== null) {
            // Ajout d'un traitement à l'ouverture du tab.
            $script .= '$.fn.loadTab'.$this->getId($tabView).' = function(e) {';
            if ($this->useCache === true) {
                $script .= 'if ($(\'#'.$this->getId($tabView).'\').attr(\'data-cached\') == "false") {';
            }
            $script .= '$(\'#'.$this->getId($tabView).'\').html(\''.$this->loadingText.'\');';
            $script .= '$.get($(\'#'.$this->getId($tabView).'\').attr(\'data-remote\'), function(data) {';
            $script .= '$(\'#'.$this->getId($tabView).'\').html(data);';
            if ($this->useCache === true) {
                $script .= '$(\'#'.$this->getId($tabView).'\').attr(\'data-cached\', true);';
            }
            $script .= '$(\'#'.$this->getId($tabView).'\').trigger(\'tabLoaded\');';
            $script .= '}).error(function(data) {';
            $script .= '$(\'#'.$this->getId($tabView).'\').html(\''.$this->errorText.'\');';
            $script .= 'errorHandler(data);';
            $script .= '});';
            if ($this->useCache === true) {
                $script .= '}';
            }
            $script .= '};';
            $script .= '$(\'a[href="#'.$this->getId($tabView).'"]\').on(\'show\', $.fn.loadTab'.$this->getId($tabView).');';
            if ($this->active) {
                $script .= '$.fn.loadTab'.$this->getId($tabView).'();';
            }
        }

        return $script;
    }

    /**
     * Render the navigation part of the Tab.
     *
     * @param UI-Tab_View $tabView
     *
     * @return string
     */
    public function getHTMLNav($tabView)
    {
        $nav = '';

        $nav .= '<li id="'.$this->getId($tabView).'_li"';
        if ($this->disabled === true) {
            $nav .= ' class="disabled"';
        } else if ($this->active === true) {
            $nav .= ' class="active"';
        }
        $nav .= '>';

        $nav .= '<a href="#'.$this->getId($tabView).'" data-toggle="tab">';
        $nav .= $this->label;
        $nav .= '</a>';

        $nav .= '</li>';

        return $nav;
    }

    /**
     * Render the content part of the Tab.
     *
     * @param UI-Tab_View $tabView
     *
     * @return string
     */
    public function getHTMLTab($tabView)
    {
        $tab = '';

        $tab .= '<div';
        $tab .= ' id="'.$this->getId($tabView).'"';
        $tab .= ' class="tab-pane';
        if ($this->active === true) {
            $tab .= ' active';
        }
        $tab .= '"';
        if ($this->dataSource !== null) {
            $tab .= ' data-remote="'.$this->dataSource.'" data-cached="false"';
        }
        $tab .= '>';

        $tab .= $this->content;

        $tab .= '</div>';

        return $tab;
    }

}
