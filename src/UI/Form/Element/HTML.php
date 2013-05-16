<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Generate an HTML element
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Element_HTML extends Zend_Form_Element
{
    /**
     * Reference to a UI_Form_Element, to access to its methods.
     *
     * @var UI_Form_Element
     */
    protected $_element;

    /**
     * HTML content
     *
     * @var string
     */
    public $content = '';

    /**
     * Flag indicates if the HTML element is render without decorators.
     *
     * @var bool
     */
    protected $_withoutDecorators = false;


    /**
     * Constructor
     *
     * @param string $name
     * @param string $content
     * @param bool $withoutDecorators
     *
     * @throws Core_Exception_InvalidArgument if $name is not valid.
     */
    public function __construct($name, $content='', $withoutDecorators=false)
    {
        if (!(is_string($name))) {
            throw new Core_Exception_InvalidArgument('Name is required for the Element');
        }
        $this->content = $content;
        $this->_withoutDecorators = $withoutDecorators;
        parent::__construct($name);
        $this->_element = new UI_Form_Element($this);
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
     * @see Zend/Form/Zend_Form_Element::loadDefaultDecorators()
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('Label');
        }
    }

    /**
     * Set withoutDecorators attribute.
     *
     * @param bool $withoutDecorators
     */
    public function setWithoutDecorators($withoutDecorators)
    {
        $this->_withoutDecorators = $withoutDecorators;
    }

    /**
     * @param Zend_View_Interface $view
     * @see Zend/Form/Zend_Form_Element::render()
     */
    public function render(Zend_View_Interface $view = null)
    {
        if ($this->_isPartialRendering) {
            return '';
        }

        if ($view !== null) {
            $this->setView($view);
        }

        if ($this->_withoutDecorators) {
            return $this->content;
        } else {
            $content = '<span id="'.$this->getName().'">'.$this->content.'</span>';
            foreach ($this->getDecorators() as $decorator) {
                $decorator->setElement($this);
                $content = $decorator->render($content);
            }
            return $content;
        }
    }

    /**
     * Utilisé par UI_Form pour fournir les scripts javascripts.
     *
     * @return string
     */
    public function getScript()
    {
        return '';
    }

}