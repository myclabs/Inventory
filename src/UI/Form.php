<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Allow you to create forms
 *
 * @author MyC.Sense
 * @package UI
 * @subpackage Form
 */
class UI_Form extends Zend_Form
{
    /**
     * Unique reference of the Form
     *
     * @var string
     */
    protected $_ref;

    /**
     * Défini si le formulaire est validé par ajax.
     *
     * @var bool
     */
    protected $_ajax = true;

    /**
     * Fonction javascript utilisé lors du succes de la validation ajax.
     *
     * @var string
     */
    protected $_ajaxSuccessFunction = null;

    /**
     * Groupe principale dans lequel seront tous les éléments.
     *
     * @var UI_Form_Element_Group
     */
    protected $_mainGroup = null;

    /**
     * Groupe dans lequel seront placé les éléments d'action en bas.
     *
     * @var UI_Form_Element_Group
     */
    protected $_actionGroup = null;

    /**
     * If true, the form is read-only
     *
     * @var bool = false
     */
    protected $readOnly = false;

    /**
     * Constructor
     * @see Zend/Zend_Form::_construct()
     *
     * @param string $ref
     * @return void
     */
    public function __construct($ref)
    {
        if (!is_string($ref) || trim($ref) === '') {
            throw new Core_Exception_UndefinedAttribute('Ref attribute is required');
        }

        $this->_ref = $ref;

        $this->addElementPrefixPath('UI_Form_Decorator', dirname(__FILE__).'/Form/Decorator/',
                                Zend_Form_Element::DECORATOR);

        $this->setAttrib('id', $ref);
        $this->setAttrib('class', 'form form-horizontal');

        $this->initMainGroup();

        parent::__construct();
    }

    /**
     * Change the initial Ref of the Form.
     *
     * @param string $ref
     *
     * @throws Core_Exception_UndefinedAttribute
     */
    public function setRef($ref)
    {
        if (!is_string($ref) || trim($ref) === '') {
            throw new Core_Exception_UndefinedAttribute('Ref attribute is required');
        }

        $this->_ref = $ref;
        $this->setAttrib('id', $ref);
    }

    /**
     * @see Zend/Zend_Form::loadDefaultDecorators()
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('Form');
        }
    }

    /**
     * Ajoute le groupe principal au formulaire.
     */
    protected function initMainGroup()
    {
        $mainGroup = new UI_Form_Element_Group('main-'.$this->_ref);
        $mainGroup->addDecorator('MainGroup');
        $this->_mainGroup = $mainGroup;

        parent::addElement($mainGroup, 'main_'.$this->_ref);
    }

    /**
     * @param UI_Form_Element $element
     * @param string $name
     * @param array|Zend_Config $options
     * @see Zend_Form::addElement()
     */
    public function addElement($element, $name=null, $options=null)
    {
        $this->_mainGroup->addElement($element);
    }

    /**
     * @param Zend_Form_Element $element
     * @see Zend_Form::addElement()
     */
    public function addActionElement($element)
    {
        if ($this->_actionGroup === null) {
            $this->initActionGroup();
        }

        $this->_actionGroup->addElement($element);
    }

    /**
     * Ajoute le groupe principal au formulaire.
     */
    protected function initActionGroup()
    {
        $actionGroup = new UI_Form_Element_Group('action-'.$this->_ref);
        $actionGroup->addDecorator('ActionGroup');
        $this->_actionGroup = $actionGroup;

        $this->_mainGroup->addElement($actionGroup);
    }

    /**
     * Add foldAll / unFold links to the form
     */
    public function addFoldAll()
    {
        $foldAllHTML = new UI_Form_Element_HTML('foldAllGroups');

        $foldOptions = array(
                'tag'         => 'a',
                'href'        => '#'.$this->getId().' .subGroup > div.collapse.in',
                'data-toggle' => 'collapse',
//                 'onclick'     => '$(\'#'.$this->getId().' .subGroup > div.collapse.in\').collapse(\'hide\');'
            );

        $unfoldOptions = array(
                'tag'         => 'a',
                'href'        => '#'.$this->getId().' .subGroup > div.collapse:not(.in)',
                'data-toggle' => 'collapse',
//                 'onclick'     => '$(\'#'.$this->getId().' .subGroup > div.collapse:not(.in)\').collapse(\'show\');',
            );

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($foldOptions);
        $fold = $htmlTagDecorator->render(__('UI', 'verb', 'collapseAll'));

        $htmlTagDecorator = new Zend_Form_Decorator_HtmlTag();
        $htmlTagDecorator->setOptions($unfoldOptions);
        $unfold = $htmlTagDecorator->render(__('UI', 'verb', 'expandAll'));

        $foldAllHTML->setWithoutDecorators(true);
        $htmlTagDecorator = new UI_Form_Decorator_ActionGroup();
        $htmlTagDecorator->setElement($foldAllHTML);
        $foldAllHTML->content = $htmlTagDecorator->render($fold . '/' . $unfold);

        $this->addElement($foldAllHTML);
    }

    /**
     * Add a submit button to the form.
     * @param string $label Button caption
     * @param string $ref
     */
    public function addSubmitButton($label=null, $ref=null)
    {
        if ($label == null) {
            $label = __('UI', 'verb', 'save');
        }
        $this->addActionElement(new UI_Form_Element_Submit((($ref === null) ? $label : $ref), $label));
    }

    /**
     * Add a reset button to the form.
     * @param string $label Button caption
     * @param string $ref
     */
    public function addResetButton($label=null, $ref=null)
    {
        if ($label == null) {
            $label = __('UI', 'verb', 'reset');
        }
        $this->addActionElement(new UI_Form_Element_Reset((($ref === null) ? $label : $ref), $label));
    }

    /**
     * Remplace la validation standard par une validation ajax.
     *
     * @param bool $flag Active ou désactive la soumission en ajax
     * @param string $successFunction Méthode JS à exécuter lorsque le formulaire a été soumis avec succès
     */
    public function setAjax($flag=true, $successFunction=null)
    {
        if ($flag !== null) {
            $this->_ajax = $flag;
        }
        $this->_ajaxSuccessFunction = $successFunction;
    }

    /**
     * Ajoute une classe CSS
     *
     * @param string $class
     */
    public function addClass($class)
    {
        $this->setAttrib('class', $this->getAttrib('class') . ' ' . $class);
    }

    /**
     * @param bool $readOnly
     */
    public function setReadOnly($readOnly)
    {
        $this->readOnly = (bool) $readOnly;
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page
     *
     * @param UI_Form $instance
     *
     * @return void
     */
    public static function addHeader($instance=null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        // Ajout des feuilles de style.
        $broker->view->headLink()->appendStylesheet('css/ui/form.css');
        $broker->view->headScript()->appendFile('scripts/ui/form-ajax.js', 'text/javascript');
        $broker->view->headScript()->appendFile('scripts/ui/form-action.js', 'text/javascript');

        $broker->view->headLink()->appendStylesheet('markitup/skins/markitup/style.css');
        $broker->view->headLink()->appendStylesheet('markitup/skins/textile/style.css');
        $broker->view->headScript()->appendFile('markitup/jquery.markitup.js', 'text/javascript');
        $broker->view->headScript()->appendFile('markitup/sets/textile/set.js', 'text/javascript');

        $broker->view->headLink()->appendStylesheet('bootstrap/css/datepicker.css');
        $broker->view->headScript()->appendFile('bootstrap/js/bootstrap-datepicker.js', 'text/javascript');

        UI_JS_AutoComplete::addHeader();

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
     * Render the script
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        // Event for handling any change on the form: set a global JS var indicating if any change happened.
        $script .= 'form'.$this->_ref.'HasChanged = false;';
        $script .= '$(\'#'.$this->_ref.' input, #'.$this->_ref.' select\').change(function(e) {';
        $script .= 'form'.$this->_ref.'HasChanged = true;';
        $script .= '});';

        // Event for handling reset on form.
        $script .= '$(\'#'.$this->_ref.'\').bind(\'reset\', function(e) {';
        foreach ($this->getElements() as $zendFormElement) {
            $script .= $zendFormElement->getElement()->getResetScript();
        }
        $script .= '});';

        // Ajax submission.
        if ($this->_ajax === true) {
            $script .= '$(\'#'.$this->_ref.'\').submit(function(event) {';
            $script .= 'event.preventDefault();';
            $script .= '$(\'#'.$this->_ref.'\').eraseFormErrors();';
            $script .= '$.post(';
            $script .= '$(\'#'.$this->_ref.'\').attr(\'action\'),';
            $script .= '$(\'#'.$this->_ref.'\').parseFormData(),';
            // Success callback
            if ($this->_ajaxSuccessFunction !== null) {
                $script .= 'function(data, textStatus, jqXHR){';
                $script .= '$(\'#'.$this->_ref.'\').'.$this->_ajaxSuccessFunction.'(data, textStatus, jqXHR);';
                $script .= '}';
            } else {
                $script .= 'function(o){$(\'#'.$this->_ref.'\').parseFormValidation(o);}';
            }
            // Error callback
            $script .= ').error(';
            $script .= 'function(o) {';
            $script .= '$(\'#'.$this->_ref.'\').parseFormErrors(o);';
            foreach ($this->getElements() as $zendFormElement) {
                $script .= $zendFormElement->getElement()->getErrorScript();
            }
            $script .= '}';
            $script .= ');';
            $script .= '});';
        }

        foreach ($this->_elements as $zendFormElement) {
            $script .= $zendFormElement->getElement()->getScript();
        }

        return $script;
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @param  Zend_View_Interface $view
     *
     * @return string
     */
    public function getHTML(Zend_View_Interface $view=null)
    {
        if ($this->readOnly) {
            $this->_mainGroup->getElement()->setReadOnly();
        }
        return parent::render($view);
    }

    /**
     * Méthode renvoyant le code html suivi du code javascript.
     *
     * @param  Zend_View_Interface $view
     *
     * @return mixed string
     */
    public final function render(Zend_View_Interface $view=null)
    {
        $render = $this->getHTML($view);
        $script = $this->getScript();
        if ($script !== '') {
            $script = '<script type="text/javascript">$(document).ready(function(){'.$script.'});</script>';
        }
        return $render.$script;
    }

    /**
     * Méthode renvoyant le code html et ajoutant le script au header.
     *
     * @param  Zend_View_Interface $view
     *
     * @return void
     */
    public final function display(Zend_View_Interface $view=null)
    {
        static::addHeader($this);
        echo preg_replace('#&amp;nbsp;#', '&nbsp;', $this->getHTML($view));
    }
}
