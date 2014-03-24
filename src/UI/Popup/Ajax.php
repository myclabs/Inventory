<?php
use MyCLabs\MUIH\GenericVoidTag;

/**
 * Fichier de la classe Popup Ajax.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Popup
 */

/**
 * Description of popup ajax.
 * @package UI
 * @subpackage Popup
 * Une classe permettant de générer des popup dont le contenu sera chargé en ajax.
 */
class UI_Popup_Ajax extends UI_Popup_Generic
{
    /**
     * Texte affiché pendant le chargement du corps du popup.
     *
     * @var string
     */
    public $loadingText = null;

    /**
     * Image affichée pendant le chargement du corps du popup.
     *
     * @var GenericVoidTag
     */
    public $loadingImage = null;

    /**
     * Texte affiché en cas d'erreur durant le chargement du corps du popup.
     *
     * @var string
     */
    public $errorText = null;


    /**
     * Source du body.
     *
     * Cette url de base ou sera chargé le corps du popup.
     *
     * @var   string
     */
    public $source = null;


    /**
     * Constructeur de la classe PopupAjax.
     *
     * @param string $id Identifiant obligatoire pour l'objet javascript.
     * @param string $source Url de récupération du contenu.
     *
     */
    public function  __construct($id, $source='#')
    {
        $this->id = $id;
        $this->source = $source;
        $this->_type = UI_Popup_Generic::TYPE_POPUP_AJAX;
        $this->_attributes['class'] = 'modal fade';

        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->loadingText = __('UI', 'loading', 'loading').'<br>';
        $this->loadingImage = new GenericVoidTag('img');
        $this->loadingImage->setAttribute('src', 'images/ui/ajax-loader_large.gif');
        $this->loadingImage->setAttribute('alt', __('UI', 'loading', 'loading'));
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error')).'<br>';
    }

    /**
     * Renvoi le javascripts de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        // Ajout d'un listener sur chaque bouton ouvrant ce popup pour effectuer le chargement du contenu.
        $script .= '$(\'body\').on(\'click\', \'[data-target="#'.$this->id.'"]\', function(e) {';
        $script .= 'e.preventDefault();';
        $script .= 'var target = $(this);';
        $script .= 'var href = target.attr(\'href\');';
        $script .= 'if (href == \'#\') {';
        $script .= 'href = \''.$this->source.'\';';
        $script .= '}';
        $script .= '$.get(href, function(data) {';
        $script .= '$(target.attr(\'data-target\')).children(\'div.modal-body\').html(data);';
        $script .= '}).error(function(data) {';
        $script .= '$(target.attr(\'data-target\')).children(\'div.modal-body\').html(\'';
        $script .= addslashes($this->errorText);
        $script .= '\');';
        $script .= 'errorHandler(data);';
        $script .= '});';
        $script .= '});';

        // A la fermeture du popup on réinitialise le texte de chargement.
        $script .= '$(\'#'.$this->id.'\').on(\'hidden\', function(e) {';
        $script .= 'if (e.target == this) {;';
        $script .= '$(\'#'.$this->id.'\').children(\'div.modal-header\').children(\'h3\').html(\'';
        $script .= addslashes($this->title);
        $script .= '\');';
        $script .= '$(\'#'.$this->id.'\').children(\'div.modal-body\').html(\'';
        $script .= $this->getBody();
        $script .= '\');';
        $script .= '}';
        $script .= '});';

        return $script;
    }

    /**
     * Fournit le corps du popup.
     *
     * @return string
     */
    protected function getBody()
    {
        return (($this->loadingImage !== null) ? $this->loadingImage->render(false) : '').$this->loadingText;
    }

}
