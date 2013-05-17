<?php
/**
 * @package Social
 */

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @author joseph.rouffet
 * @package Social
 */
class Social_Model_GenericAction extends Social_Model_Action
{

    const QUERY_THEME = 'theme';

    /**
     * Theme auquel est associée l'action
     * @var Social_Model_Theme
     */
    protected $theme;

    /**
     * @var Collection|Social_Model_ContextAction[]
     */
    protected $contextActions;


    /**
     * @param Social_Model_Theme $theme
     */
    public function __construct(Social_Model_Theme $theme)
    {
        parent::__construct();
        $this->contextActions = new ArrayCollection();
        $this->setTheme($theme);
    }

    /**
     * @param Social_Model_Theme $theme
     */
    public function setTheme(Social_Model_Theme $theme)
    {
        $this->theme = $theme;
    }

    /**
     * @return Social_Model_Theme
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * @return Social_Model_ContextAction[]
     */
    public function getContextActions()
    {
        return $this->contextActions->toArray();
    }

    /**
     * @param Social_Model_ContextAction $contextAction
     */
    public function addContextAction(Social_Model_ContextAction $contextAction)
    {
        if (! $this->hasContextAction($contextAction)) {
            $this->contextActions->add($contextAction);
        }
    }

    /**
     * @param Social_Model_ContextAction $contextAction
     */
    public function removeContextAction(Social_Model_ContextAction $contextAction)
    {
        if ($this->hasContextAction($contextAction)) {
            $this->contextActions->removeElement($contextAction);
        }
    }

    /**
     * @param Social_Model_ContextAction $contextAction
     * @return boolean
     */
    public function hasContextAction(Social_Model_ContextAction $contextAction)
    {
        return $this->contextActions->contains($contextAction);
    }

}
