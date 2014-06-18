<?php

use AF\Application\Form\Element\FormElement;
use AF\Application\Form\Element\HTMLElement;
use AF\Application\Form\Element\ZendFormElement;
use MyCLabs\MUIH\Button;

/**
 * Generate a Captcha Image.
 *
 * @author valentin.claras
 */
class UI_Form_Element_Captcha extends Zend_Form_Element_Captcha implements ZendFormElement
{
    /**
     * Contient le chemin vers le controlleur qui doit étendre UI_Form_Element_Captcha.
     *
     * @var String
     */
    protected $_urlController;

    /**
     * @var FormElement
     */
    protected $_element;


    /**
     * @param string $name
     * @param string $urlReload
     *
     * @throws Core_Exception
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $urlReload = null)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        $cacheDir = APPLICATION_PATH.'/../public/cache/captcha';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        // Font path
        $container = \Core\ContainerSingleton::getContainer();
        if (file_exists($container->get('police.path') . 'arial.ttf')) {
            $fontPath = $container->get('police.path') . 'arial.ttf';
        } elseif (file_exists(PACKAGE_PATH . '/' . $container->get('police.path') . 'arial.ttf')) {
            $fontPath = PACKAGE_PATH . '/'  . $container->get('police.path') . 'arial.ttf';
        } else {
            throw new Core_Exception("Font file not found for captcha");
        }

        $options = array(
            'class' => 'captcha',
            'required' => true,
            'captcha' => array(
                'font' => $fontPath,
                'captcha' => 'Image',
                'fontSize' => 26,
                'wordLen' => 5,
                'timeout' => 200,
                'imgDir' => $cacheDir,
                'imgUrl' => 'cache/captcha',
                'imgAlt' => 'Captcha',
                'dotNoiseLevel' => 15,
                'lineNoiseLevel' => 5,
                //Fréquence de passage du garbage collector pour la suppression
                //des images du captcha
                'gcFreq'=> 50,
                //Durée de stockage des images
                'expiration' => 50
            )
        );

        parent::__construct($name, $options);
        $this->_element = new FormElement($this);

        if ($urlReload !== null) {
            $this->_urlController = $urlReload;
            $captchaReload = new HTMLElement('reloadCaptcha-'.$name);
            $buttonReload = new Button(__('UI', 'captcha', 'reloadCaptcha'));
            $buttonReload->setAttribute('id', $this->getId().'-reload');
            $captchaReload->content = $buttonReload->render();
            $captchaReload->setWithoutDecorators(true);

            $this->getElement()->addElement($captchaReload);
        }
    }

    /**
     * Get the associated Element.
     *
     * @return FormElement
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Utilisé par Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        if ($this->_urlController !== null) {
            // Création de la fonction permettant de rafraichir la captcha.
            $script .= '$.fn.changeImage'.$this->getId().' = function() {';
            $script .= '$.ajax({';
            $script .= 'url: \''.$this->_urlController.'\',';
            $script .= 'dataType: \'json\',';
            $script .= 'complete: function(data) {';
            $script .= 'var captcha = $.parseJSON(data.responseText);';
            $script .= '$(\'#'.$this->getId().'-input\').val(\'\');';
            $script .= '$(\'#'.$this->getId().'-input\').parent().children(\'img\').attr(\'src\', captcha.src);';
            $script .= '$(\'#'.$this->getId().'-id\').attr(\'value\', captcha.id);';
            $script .= '},';
            $script .= 'error: errorHandler,';
            $script .= '});';
            $script .= '};';
            // Ajout d'un listener sur le bouton permettant de rafraichir la captcha.
            $script .= '$(\'#'.$this->getId().'-reload\').on(\'click\', $.fn.changeImage'.$this->getId().');';
        }

        return $script;
    }
}
