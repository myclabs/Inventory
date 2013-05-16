<?php
/**
 * Fichier de la classe Button.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage HTML
 */

/**
 * Bouton avec confirmation
 *
 * @package UI
 * @subpackage HTML
 */
class UI_HTML_ConfirmButton extends UI_HTML_Button
{

    /**
     * Texte du popup de confirmation.
     *
     * @var string
     */
    public $confirmText = null;

    /**
     * Texte du bouton 'oui' du popup de confirmation.
     *
     * @var string
     */
    public $confirmYesButton = null;

    /**
     * Texte du bouton 'non' du popup de confirmation.
     *
     * @var string
     */
    public $confirmNoButton = null;

    /**
     * Est-ce qu'après confirmation le lien est appelé avec la méthode POST plutôt que GET
     *
     * @var bool
     */
    public $confirmPost = false;


    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        if ($this->id) {
            $configuration = [];
            if ($this->confirmText) {
                $configuration['text'] = $this->confirmText;
            } else {
                $configuration['text'] = __('UI', 'message', 'areYouSure');
            }
            if ($this->confirmYesButton) {
                $configuration['confirmButton'] = $this->confirmYesButton;
            } else {
                $configuration['confirmButton'] = __('UI', 'other', 'yes');
            }
            if ($this->confirmNoButton) {
                $configuration['cancelButton'] = $this->confirmNoButton;
            } else {
                $configuration['cancelButton'] = __('UI', 'verb', 'cancel');
            }
            if ($this->confirmPost) {
                $configuration['post'] = true;
            }
            $configuration = json_encode($configuration);
            return "$('#$this->id').confirm($configuration);";
        }
        return '';
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_Datagrid $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    static function addHeader($instance=null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        // Ajout des fichiers Javascript.
        $broker->view->headScript()->appendFile('scripts/ui/jquery.confirm.js', 'text/javascript');

        UI_Form::addHeader();

        parent::addHeader($instance);
    }

}
