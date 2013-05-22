<?php
/**
 * @author     valentin.claras
 * @package    UI
 * @subpackage View
 */

/**
 * Helper de vue pour générer une image.
 * @package    Core
 * @subpackage View
 */
class UI_View_Helper_Image extends Zend_View_Helper_Abstract
{
    /**
     * @var UI_HTML_Image
     */
    protected $_image = null;


    /**
     * Retourne le render de l'actuel image de l'aide de vue.
     *
     * @return string
     */
    public function __toString()
    {
        UI_HTML_Image::addHeader($this->_image);
        return $this->_image->getHTML();
    }

    /**
     * Génere une image html
     *
     * @param string $source
     * @param string $alt
     * @param array $attributes
     *
     * @return UI_View_Helper_Image
     */
    public function image($source, $alt, $attributes=array())
    {
        $this->_image = new UI_HTML_Image($source, $alt);
        foreach ($attributes as $name => $value) {
            $this->_image->addAttribute($name, $value);
        }
        return $this;
    }

}