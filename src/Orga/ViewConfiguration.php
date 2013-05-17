<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Orga
 */

/**
 * Classe de configuration de la vue de détails d'une cellule.
 *
 * @package Orga
 */
class Orga_ViewConfiguration
{
    // Constante des tabs par défaut.
    const TAB_ORGANISATION = 'organisation';


    /**
     * Titre de la page.
     *
     * @var string
     */
    protected $_pageTitle;

    /**
     * Url de destination du menu de navigation.
     *
     * @var string
     */
    protected $_outputURL;

    /**
     * Url de destination du menu de navigation.
     *
     * @var bool
     */
    public $_areParentCellsLink = array();

    /**
     * Tableau des tabs affichés dans la vue.
     *
     * @var array(UI_Tab|constant)
     */
    protected $_tabs = array();


    /**
     * Définit le titre de la page.
     *
     * @param string $title
     */
    public function setPageTitle($title)
    {
        $this->_pageTitle = $title;
    }

    /**
     * Renvoie le titre de la page.
     *
     * @return string
     */
    public function getPageTitle()
    {
        return $this->_pageTitle;
    }

    /**
     * Définit l'url de sortie de la vue.
     *
     * @param string $url
     */
    public function setOutputURL($url)
    {
        $this->_outputURL = $url;
    }

    /**
     * Renvoie l'url de sortie de la vue.
     *
     * @return string
     */
    public function getOutputURL()
    {
        return $this->_outputURL;
    }

    /**
     * Défini si pour une cellule parente donné, la vue doit afficher un lien.
     *
     * @param Orga_Model_Cell $parentCell
     *
     * @param bool $isLink
     */
    public function setParentCellIsALink($parentCell, $isLink)
    {
        $this->_areParentCellsLink[$parentCell->getMembersHashKey()] = $isLink;
    }

    /**
     * Indique si pour une cellule parente donné, la vue doit afficher un lien.
     *
     * @param Orga_Model_Cell $parentCell
     *
     * @return true
     */
    public function isParentCellALink($parentCell)
    {
        return ((!(isset($this->_areParentCellsLink[$parentCell->getMembersHashKey()])))
            || ($this->_areParentCellsLink[$parentCell->getMembersHashKey()] === true)
        );
    }

    /**
     * Ajoute un tab à la vue.
     *
     * @param UI_Tab $tab
     * @throws Core_Exception_InvalidArgument
     */
    public function addTab(UI_Tab $tab)
    {
        if (!$tab instanceof UI_Tab) {
            throw new Core_Exception_InvalidArgument('The tab should be an instance of UI_Tab');
        }
        $this->_tabs[] = $tab;
    }

    /**
     * Ajoute un tab de base à la vue.
     *
     * @param string $baseTabConstant One of the self::TAB_* constant
     * @throws Core_Exception_InvalidArgument
     */
    public function addBaseTab($baseTabConstant)
    {
        if ($baseTabConstant != self::TAB_ORGANISATION) {
            throw new Core_Exception_InvalidArgument('The base tab should be an Orga_Viewconfiguration constant.');
        }
        $this->_tabs[] = $baseTabConstant;
    }

    /**
     * Ajoute tous les tabs de base.
     */
    public function addBaseTabs()
    {
        $this->_tabs[] = self::TAB_ORGANISATION;
    }

    /**
     * Renvoie tous les tabs qui ont été ajoutés.
     *
     * @return array(UI_Tab|string)
     */
    public function getTabs()
    {
        return $this->_tabs;
    }

}