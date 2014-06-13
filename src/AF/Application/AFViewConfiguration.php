<?php

namespace AF\Application;

use AF\Domain\InputSet\PrimaryInputSet;
use Core_Exception_InvalidArgument;
use MyCLabs\MUIH\Tab;

/**
 * Configuration de la vue affichant un AF.
 *
 * @author  matthieu.napoli
 * @author  benjamin.bertin
 */
class AFViewConfiguration
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
     * @var PrimaryInputSet|null
     */
    protected $inputSet;

    /**
     * Saisie de l'année précédente.
     * @var PrimaryInputSet|null
     */
    protected $previousInputSet;

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
     * @var array(Tab|constant)
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

    /**
     * Est-ce qu'on peut afficher l'aperçu des résultats
     * @var bool
     */
    protected $resultsPreview = true;


    /**
     * @return PrimaryInputSet|null
     */
    public function getInputSet()
    {
        return $this->inputSet;
    }

    public function setInputSet(PrimaryInputSet $inputSet = null)
    {
        $this->inputSet = $inputSet;
    }

    /**
     * @return PrimaryInputSet|null
     */
    public function getPreviousInputSet()
    {
        return $this->previousInputSet;
    }

    public function setPreviousInputSet(PrimaryInputSet $inputSet = null)
    {
        $this->previousInputSet = $inputSet;
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
     * Usable iff: every tab is an instance of Tab or a string
     *             the input tab is in the tabs list
     * @return bool
     */
    public function isUsable()
    {
        foreach ($this->tabs as $tab) {
            if ((is_array($tab) && !($tab['tab'] instanceof Tab)) && !is_string($tab)) {
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
     * @param Tab  $tab
     * @param bool $updateURL
     */
    public function addTab(Tab $tab, $updateURL = false)
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
     * @return array(Tab|string)
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
     * Set the view mode Used between AFViewConfiguration::MODE_READ,
     * AFViewConfiguration::MODE_WRITE, AFViewConfiguration::MODE_TEST
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
     * @param array  $params
     */
    public function addToActionStack($action, $controller, $module, array $params = [])
    {
        $action = [
            'action'     => $action,
            'controller' => $controller,
            'module'     => $module,
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
     * @return boolean Est-ce qu'on peut afficher l'aperçu des résultats
     */
    public function withResultsPreview()
    {
        return $this->resultsPreview;
    }

    /**
     * @param boolean $resultsPreview Est-ce qu'on peut afficher l'aperçu des résultats
     */
    public function setResultsPreview($resultsPreview)
    {
        $this->resultsPreview = (bool) $resultsPreview;
    }
}
