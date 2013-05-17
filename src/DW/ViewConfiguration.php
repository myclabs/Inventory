<?php
/**
 * @author valentin.claras
 * @package DW
 */

/**
 * Classe de configuration de la vue d'un rapport.
 *
 * @package DW
 */
class DW_ViewConfiguration
{
    /**
     * Titre de la page.
     *
     * @var string
     */
    protected $complementaryPageTitle = '';

    /**
     * Url de destination du boutonn retour.
     *
     * @var string
     */
    protected $outputURL;

    /**
     * Url de destination du boutonn sauvegarder.
     *
     * @var string
     */
    protected $saveURL;

    /**
     * Indique si le rapport peut-être sauvegardé.
     *
     * @var bool
     */
    protected $canBeUpdated = true;

    /**
     * Indique si une copie du rapport peut-être sauvegardée.
     *
     * @var bool
     */
    protected $canBeSavedAs = true;


    /**
     * Définit le sous-titre (small) de la page.
     *
     * @param string $title
     */
    public function setComplementaryPageTitle($title)
    {
        $this->complementaryPageTitle = $title;
    }

    /**
     * Renvoie le sous-titre (small) de la page.
     *
     * @return string
     */
    public function getComplementaryPageTitle()
    {
        return $this->complementaryPageTitle;
    }

    /**
     * Définit l'url de sortie de la vue.
     *
     * @param string $url
     */
    public function setOutputURL($url)
    {
        $this->outputURL = $url;
    }

    /**
     * Renvoie l'url de redirection après une sauvegarde réussie.
     *
     * @return string
     */
    public function getSaveURL()
    {
        return $this->saveURL;
    }

    /**
     * Définit l'url de redirection après une sauvegarde réussie.
     *
     * @param string $url
     */
    public function setSaveURL($url)
    {
        $this->saveURL = $url;
    }

    /**
     * Renvoie l'url de sortie de la vue.
     *
     * @return string
     */
    public function getOutputURL()
    {
        return $this->outputURL;
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