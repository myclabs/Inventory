<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate a Captcha Image.
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_Captcha extends Zend_Form_Element_Captcha
{
    /**
     * Contient le chemin vers le controlleur qui doit étendre UI_Form_Element_Captcha.
     *
     * @var String
     */
    protected $_urlController;

    /**
     * Reference to a UI_Form_Element, to access to its methods.
     *
     * @var UI_Form_Element
     */
    protected $_element;


    /**
     * Constructor
     *
     * @param String $name
     * @param string $urlReload
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $urlReload=null)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        $cacheDir = APPLICATION_PATH.'/../public/cache/captcha';
        if (!file_exists($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }

        $config = Zend_Registry::get('configuration');
        $options = array(
            'class' => 'captcha',
            'required' => true,
            'captcha' => array(
                'font' => $config->police->path.'Arial.ttf',
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
        $this->_element = new UI_Form_Element($this);

        if ($urlReload !== null) {
            $this->_urlController = $urlReload;
            $captchaReload = new UI_Form_Element_HTML('reloadCaptcha-'.$name);
            $buttonReload = new UI_HTML_Button(__('UI', 'captcha', 'reloadCaptcha'));
            $buttonReload->id = $this->getId().'-reload';
            $captchaReload->content = $buttonReload->render(false);
            $captchaReload->setWithoutDecorators(true);

            $this->getElement()->addElement($captchaReload);
        }
    }

    /**
     * Get the associated Element.
     *
     * @return UI_Form_Element
     */
    public function getElement()
    {
        return $this->_element;
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
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