<?php
/**
 * Fichier de la classe Generic.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Generic
 */

/**
 * Description of Generic.
 *
 * Une classe permettant de donner une forme unique au classe de UI.
 *
 * @package    UI
 * @subpackage Generic
 */
abstract class UI_Generic
{

    /**
     * Séparateur
     */
    const REF_SEPARATOR = "__";

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_Generic $instance
     */
    public static function addHeader($instance=null)
    {
        /* @var $broker Zend_Controller_Action_Helper_ViewRenderer */
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

        if ($instance !== null) {
            $script = $instance->getScript();
            if ($script !== '') {
                $broker->view->headScript()->appendScript(
                    '$(document).ready(function(){'.$script.'});',
                    'text/javascript',
                    array('noescape' => true)
                );
            }
        }
    }

    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @return string
     */
    public function getHTML()
    {
        return '';
    }

    /**
     * Méthode renvoyant le code html suivi du code javascript.
     *
     * @return mixed string
     */
    public final function render()
    {
        $render = $this->getHTML();
        $script = $this->getScript();
        if ($script !== '') {
            $script = '<script type="text/javascript">$(document).ready(function(){'.$script.'});</script>';
        }
        return $render.$script;
    }

    /**
     * Méthode renvoyant le code html et ajoutant le script au header.
     *
     * @return void
     */
    public final function display()
    {
        static::addHeader($this);
        echo $this->getHTML();
    }

}
