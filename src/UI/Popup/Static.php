<?php
/**
 * Fichier de la classe Popup Static.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Popup
 */

/**
 * Description of popup static.
 * @package    UI
 * @subpackage Popup
 *
 * Une classe permettant de générer des popup d'info ou de saisie très rapidement.
 */
class UI_Popup_Static extends UI_Popup_Generic
{
    /**
     * Texte du body.
     *
     * Ce texte est corps du popup
     *  Il s'agira du texte affiché dans les PopupStatique
     *  ou de l'url par defaut pour les PopupAjax.
     *
     * @var   string
     */
    public $body = null;


    /**
     * Constructeur de la classe PopupStatique.
     *
     * @param string $id Identifiant obligatoire pour l'objet javascript.
     *
     */
    public function  __construct($id)
    {
        $this->id = $id;
        $this->_type = UI_Popup_Generic::TYPE_POPUP_STATIQUE;
        $this->_attributes['class'] = 'modal fade';
    }

    /**
     * Fournit le corps du popup.
     *
     * @return string
     */
    protected function getBody()
    {
        return $this->body;
    }

}