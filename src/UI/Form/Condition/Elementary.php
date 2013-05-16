<?php
/**
 * @author valentin.claras
 * @package UI
 * @subpackage Form
 */

/**
 * Used to associated conditions with actions
 *
 * @package UI
 * @subpackage Form
 */
class UI_Form_Condition_Elementary extends UI_Form_Condition
{
    /**
     * Type of Validator
     *
     * @var const
     */
    public $relation;

    /**
     * Element in wich the condition act
     *
     * @var Zend_Form_Element
     */
    public $element;

    /**
     * Value of the condition, according to the Element type
     *
     * @var mixed
     */
    public $value;

    /**
     * Constructor
     *
     * @param string $ref
     * @param Zend_Form_Element $element
     * @param const $relation
     * @param unknow $value
     * @throws Core_Exception_Systeme
     *             if $ref is unvalid
     */
    public function __construct($ref, $element = null, $relation = null, $value = null)
    {
        $this->element  = $element;
        $this->relation = $relation;
        $this->value    = $value;

        parent::__construct($ref);
    }

    /**
     * Render the javascript listener.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptListener($actionRef)
    {
        $scriptListener = '';

        if (($this->element instanceof UI_Form_Element_Pattern_Value) && (!(is_null($this->element->getPercent())))) {
                $scriptListener .= '$(\'#'.$this->element->getPercent()->getId().'\')';
                $scriptListener .= '.change(formAction_'.$actionRef.');';
        }

        if (($this->element instanceof UI_Form_Element_Radio) || ($this->element instanceof UI_Form_Element_MultiCheckbox)) {
            foreach ($this->element->getOptions() as $option) {
                $scriptListener .= '$(\'#'.$this->element->getId().'_'.$option->value.'\')';
                $scriptListener .= '.change(function(e) {';
                $scriptListener .= '$(\'#'.$this->element->getId().'\').formAction_'.$actionRef.'();';
                $scriptListener .= '});';
            }
        } else {
            $scriptListener .= '$(\'#'.$this->element->getId().'\')';
            $scriptListener .= '.change(function(e) {';
            $scriptListener .= '$(\'#'.$this->element->getId().'\').formAction_'.$actionRef.'();';
            $scriptListener .= '});';
        }

        return $scriptListener;
    }

    /**
     * Render the javascript condition.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptCondition($actionRef)
    {
        $scriptCondition = '';

        $jQueryId = '$(\'#' . $this->element->getId() . '\')';
        switch (get_class($this->element)) {
            case 'UI_Form_Element_Pattern_Value':
                // On test le cas ou un champ n'a pas été rempli (utile pour les condtions composées
                $scriptCondition .= 'parseInt('.$jQueryId.'.val()) ' . $this->relation . ' ';
                if (empty($this->value)) {
                    $scriptCondition .= '""';
                } else {
                    $scriptCondition .= $this->value;
                }
                break;
            case 'UI_Form_Element_Text':
            case 'UI_Form_Element_Select':
            case 'UI_Form_Element_MultiSelect':
                $scriptCondition .= $jQueryId.'.val() ' . $this->relation . ' \'' . $this->value . '\'';
                break;
            case 'UI_Form_Element_Checkbox':
                $checked = $this->value ? 'true' : 'false';
                $scriptCondition .= $jQueryId.'.prop(\'checked\') ' . $this->relation . ' ' . $checked;
                break;
            case 'UI_Form_Element_Radio':
                $jQueryId = '$(\'#' . $this->element->getId() . '_' . $this->value . '\')';
                $scriptCondition .= $jQueryId . '.attr(\'checked\') ' . $this->relation . ' \'checked\'';
                break;
            case 'UI_Form_Element_MultiCheckbox':
                if ($this->value !== null) {
                    $jQueryId = '$(\'#' . $this->element->getId() . '_' . $this->value . '\')';
                    $scriptCondition .= $jQueryId.'.attr(\'checked\') ' . $this->relation . ' \'checked\'';
                } else {
                    $jQueryId = '$(\'input[name=' . $this->element->getName() . ']:selected\')';
                    $scriptCondition .= $jQueryId . '.length ' . $this->relation . ' ' . $this->value;
                }
                break;
            default:
                throw new Core_Exception_Systeme('Condition error');
        }

        return $scriptCondition;
    }

    /**
     * Render the javascript param.
     *
     * @param string $actionRef unique reference of the action using the condition.
     *
     * @return String
     */
    public function getScriptParams($actionRef)
    {
        $scriptParam = '';

        $jQueryId = '$(\'#' . $this->element->getId() . '\')';
        switch (get_class($this->element)) {
            case 'UI_Form_Element_Pattern_Value':
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.val() + \'';
                if (!is_null($this->element->getPercent())) {
                    $scriptParam .= '&' . $this->element->getPercent()->getId()
                                 . '=\' + $(\'#' . $this->element->getPercent()->getId() . '\').val() + \'';
                }
                break;
            case 'UI_Form_Element_Text':
            case 'UI_Form_Element_Select':
            case 'UI_Form_Element_MultiSelect':
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.val()+\'';
                break;
            case 'UI_Form_Element_Checkbox':
                $scriptParam .= $this->element->getId() . '=\' + ' . $jQueryId . '.attr(\'checked\') + \'';
                break;
            case 'UI_Form_Element_Radio':
                $jQueryId = '$(\'#' . $this->element->getId() . $this->value . '\')';
                $scriptParam .= $this->element->getId().$this->value . '=\' + ' . $jQueryId . '.attr(\'checked\') + \'';
                break;
            case 'UI_Form_Element_MultiCheckbox':
                if ($this->value !== null) {
                    $jQueryId = '$(\'#'.$this->element->getId().$this->value.'\')';
                    $scriptParam .= $this->element->getId().$this->value
                                 . '=\' + ' . $jQueryId . '.attr\'checked\') + \'';
                } else {
                    $jQueryId = '$(\'input[name=' . $this->element->getName() . ']:selected\')';
                    $scriptParam .= $this->element->getName() . '=\' + function(){';
                    $scriptParam .= 'for (var x = 0; key < ' .$jQueryId . '.length; key++) {';
                    $scriptParam .= 'if ($(' . $jQueryId . '[key]).attr(\'checked\') == \'checked\') {';
                    $scriptParam .= 'return $(' . $jQueryId . '[key]).val();';
                    $scriptParam .= '}';
                    $scriptParam .= '}';
                    $scriptParam .= '} + \'';
                }
                break;
            default:
        }

        return $scriptParam;
    }
}