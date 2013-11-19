<?php
/**
 * @author  matthieu.napoli
 * @author  benjamin.bertin
 * @package AF
 */

/**
 * Configuration de la vue affichant un AF
 * in order to show an AF's form
 * @package AF
 */
class AF_ViewConfiguration
{
    /**
     * Constants for base tabs
     */
    const TAB_INPUT = 'input';
    const TAB_RESULT = 'result';
    const TAB_CALCULATION_DETAILS = 'calculationDetails';
    const TAB_DOCUMENTATION = 'documentation';

    /**
     * Constant for context mode
     */
    const MODE_WRITE = 'write';
    const MODE_TEST = 'test';
    const MODE_READ = 'read';

    /**
     * @var int
     */
    protected $idInputSet;

    /**
     * The URL to go to when the user quit
     * @var string
     */
    protected $exitURL;

    /**
     * Actions to call when the AF is submitted
     * @var array
     */
    protected $actionStack;

    /**
     * URL to call for the results preview
     * @var string
     */
    protected $resultsPreviewUrl;

    /**
     * Boolean that indicate if the configuration link should appear
     * @var bool
     */
    protected $displayConfigurationLink = false;

    /**
     * The tabs to be shown
     * @var array(UI_Tab|constant)
     */
    protected $tabs = [];

    /**
     * View Mode for AF
     * @var int
     */
    protected $mode = self::MODE_WRITE;

    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * Paramètres supplémentaires à passer dans les URL
     * @var array
     */
    protected $urlParams = array();

    protected $useSession = false;


    /**
     * @return int
     */
    public function getIdInputSet()
    {
        return $this->idInputSet;
    }

    /**
     * @param int $idInputSet
     */
    public function setIdInputSet($idInputSet)
    {
        $this->idInputSet = $idInputSet;
    }

    /**
     * @param string $url
     */
    public function setExitUrl($url)
    {
        $this->exitURL = $url;
    }

    /**
     * @return string
     */
    public function getExitURL()
    {
        return $this->exitURL;
    }

    /**
     * @param bool $displayConfigurationLink
     */
    public function setDisplayConfigurationLink($displayConfigurationLink)
    {
        $this->displayConfigurationLink = $displayConfigurationLink;
    }

    /**
     * @return bool
     */
    public function getDisplayConfigurationLink()
    {
        return $this->displayConfigurationLink;
    }

    /**
     * Check if the viewConfiguration is usable
     * Usable iff: every tab is an instance of UI_Tab or a string
     *             the input tab is in the tabs list
     *             the refTechnoDB and the ouputURL are specified
     * @return bool
     */
    public function isUsable()
    {
        foreach ($this->tabs as $tab) {
            if ((is_array($tab) && !($tab['tab'] instanceof UI_Tab)) && !is_string($tab)) {
                return false;
            }
        }
        if (!in_array(self::TAB_INPUT, $this->tabs)) {
            return false;
        }
        return isset($this->exitURL);
    }

    /**
     * Add a tab
     * @param UI_Tab $tab
     * @param bool   $updateURL
     */
    public function addTab(UI_Tab $tab, $updateURL = false)
    {
        $this->tabs[] = array('tab' => $tab, 'updateURL' => $updateURL);
    }

    /**
     * Add a base tab
     * @param string $baseTabConstant One of the self::TAB_* constant
     */
    public function addBaseTab($baseTabConstant)
    {
        if ($baseTabConstant != self::TAB_INPUT
            && $baseTabConstant != self::TAB_RESULT
            && $baseTabConstant != self::TAB_CALCULATION_DETAILS
            && $baseTabConstant != self::TAB_DOCUMENTATION
        ) {
            throw new Core_Exception_InvalidArgument('The base tab should be an AF_Viewconfiguration constant');
        }
        $this->tabs[] = $baseTabConstant;
    }

    /**
     * Add all the base tabs
     */
    public function addBaseTabs()
    {
        $this->tabs[] = self::TAB_INPUT;
        $this->tabs[] = self::TAB_RESULT;
        $this->tabs[] = self::TAB_CALCULATION_DETAILS;
    }

    /**
     * Return the tabs in an array
     * @return array(UI_Tab|string)
     */
    public function getTabs()
    {
        return $this->tabs;
    }

    /**
     * Check if the base tab $tab is in the tabs
     * @param string $tab
     * @return bool
     */
    public function hasBaseTab($tab)
    {
        return in_array($tab, $this->tabs);
    }

    /**
     * Set the view mode Used between AF_ViewConfiguration::MODE_READ,
     * AF_ViewConfiguration::MODE_WRITE, AF_ViewConfiguration::MODE_TEST
     * @param int $mode
     */
    public function setMode($mode)
    {
        if ($mode !== self::MODE_READ
            && $mode !== self::MODE_WRITE
            && $mode !== self::MODE_TEST
        ) {
            throw new Core_Exception_InvalidArgument('The mode should be an AF_Viewconfiguration constant');
        }
        $this->mode = $mode;
    }

    /**
     * Return the view mode used
     * @return int
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param string $pageTitle
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * @return array
     */
    public function getActionStack()
    {
        return $this->actionStack;
    }

    /**
     * @param string $action
     * @param string $controller
     * @param string $module
     * @param array $params
     */
    public function addToActionStack($action, $controller, $module, array $params = [])
    {
        $action = [
            'action' => $action,
            'controller' => $controller,
            'module' => $module,
        ];
        if (count($params) > 0) {
            $action['params'] = $params;
        }
        $this->actionStack[] = $action;
    }

    /**
     * @param string $url
     */
    public function setResultsPreviewUrl($url)
    {
        $this->resultsPreviewUrl = $url;
    }

    /**
     * @return string
     */
    public function getResultsPreviewUrl()
    {
        return $this->resultsPreviewUrl;
    }

    /**
     * @return array
     */
    public function getUrlParams()
    {
        return $this->urlParams;
    }

    /**
     * Ajoute un paramètre supplémentaire à passer dans les URL
     * @param string $name
     * @param string $value
     */
    public function addUrlParam($name, $value)
    {
        $this->urlParams[$name] = $value;
    }

    /**
     * @return boolean
     */
    public function getUseSession()
    {
        return $this->useSession;
    }

    /**
     * @param boolean $useSession
     */
    public function setUseSession($useSession)
    {
        $this->useSession = $useSession;
    }
}
