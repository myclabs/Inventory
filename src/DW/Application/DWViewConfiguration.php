<?php
/**
 * @author valentin.claras
 */

namespace DW\Application;

/**
 * Classe de configuration de la vue d'un rapport.
 *
 * @package    DW
 * @subpackage ViewConfiguration
 */
class DWViewConfiguration
{
    /**
     * @var string
     */
    protected $complementaryPageTitle = '';

    /**
     * @var string
     */
    protected $outputUrl;

    /**
     * @var string
     */
    protected $saveURL;

    /**
     * @var bool
     */
    protected $canBeUpdated = true;

    /**
     * @var bool
     */
    protected $canBeSavedAs = true;


    /**
     * @param string $title
     */
    public function setComplementaryPageTitle($title)
    {
        $this->complementaryPageTitle = $title;
    }

    /**
     * @return string
     */
    public function getComplementaryPageTitle()
    {
        return $this->complementaryPageTitle;
    }

    /**
     * @param string $url
     */
    public function setOutputUrl($url)
    {
        $this->outputUrl = $url;
    }

    /**
     * @return string
     */
    public function getSaveURL()
    {
        return $this->saveURL;
    }

    /**
     * @param string $url
     */
    public function setSaveURL($url)
    {
        $this->saveURL = $url;
    }

    /**
     * @return string
     */
    public function getOutputUrl()
    {
        return $this->outputUrl;
    }

    /**
     * @param boolean $canBeSavedAs
     */
    public function setCanBeSavedAs($canBeSavedAs)
    {
        $this->canBeSavedAs = $canBeSavedAs;
    }

    /**
     * @return boolean
     */
    public function canBeSavedAs()
    {
        return $this->canBeSavedAs;
    }

    /**
     * @param boolean $canBeUpdated
     */
    public function setCanBeUpdated($canBeUpdated)
    {
        $this->canBeUpdated = $canBeUpdated;
    }

    /**
     * @return boolean
     */
    public function canBeUpdated()
    {
        return $this->canBeUpdated;
    }

}