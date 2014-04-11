<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use UI\Datagrid\Datagrid;
use UI_Form_Element_Text;
use UI_Form_Element_Numeric;

/**
 * Classe représentant une colonne contenant des nombres.
 *
 * @author valentin.claras
 */
class NumberColumn extends GenericColumn
{
    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual;

    /**
     * Définition du mot clef du filtre pour l'infériorité.
     *
     * @var string
     */
    public $keywordFilterLower;

    /**
     * Définition du mot clef du filtre pour la supériorité.
     *
     * @var string
     */
    public $keywordFilterHigher;

    /**
     * Définition de la constante utilisé pour le filtre inférieur sur la colonne.
     *
     * @var string
     */
    public $filterOperatorLower;

    /**
     * Définition de la constante utilisé pour le filtre supérieur sur la colonne.
     *
     * @var string
     */
    public $filterOperatorHigher;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_RIGHT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColNumberEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColNumberLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColNumberHigher');
        $this->criteriaFilterOperator = 'eq';
        $this->filterOperatorLower = 'lt';
        $this->filterOperatorHigher = 'gt';
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        return <<<JS
if (typeof(sData) != "object") {
    content = sData;
} else {
    if (sData.content != null) {
        content = sData.content;
    } else {
        content = sData.value;
    }
}
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        return <<<JS
this.onEventShowCellEditor(oArgs);
if ((typeof(sData) == "undefined") || (sData == null)) {
    var content = '';
} else if (typeof(sData) != "object") {
    var content = sData.toString();
} else {
    content = sData.value.toString();
}
column.editor.textbox.value = content;
JS;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('label', $this->getFilterFormLabel());
        $colLabel->setAttribute('for', $this->getFilterFormId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colWrapper->appendContent($colLabel);

        $numberWrapper = new GenericTag('div');
        $numberWrapper->addClass('col-sm-3');

        $numberInput = new GenericVoidTag('input');
        $numberInput->setAttribute('type', 'text');
        $numberInput->setAttribute('name', $this->getFilterFormId($datagrid));
        $numberInput->setAttribute('id', $this->getFilterFormId($datagrid));
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->criteriaFilterOperator])) {
            $numberInput->setAttribute('value', $defaultValue[$this->criteriaFilterOperator]);
        }
        $numberInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterEqual)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterEqual);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent($this->getResetFieldFilterFormSuffix($datagrid));

        $numberWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberWrapper);


        $numberInferiorWrapper = new GenericTag('div');
        $numberInferiorWrapper->addClass('col-sm-3');
        $numberInferiorWrapper->addClass('col-sm-offset-1');

        $numberInferiorInput = new GenericVoidTag('input');
        $numberInferiorInput->setAttribute('type', 'text');
        $numberInferiorInput->setAttribute('name', $this->getFilterFormId($datagrid).'_lower');
        $numberInferiorInput->setAttribute('id', $this->getFilterFormId($datagrid).'_lower');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperatorLower])) {
            $numberInferiorInput->setAttribute('value', $defaultValue[$this->filterOperatorLower]);
        }
        $numberInferiorInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberInferiorInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterLower)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterLower);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent(
            $this->getResetFieldFilterFormSuffix($datagrid)
                ->setAttribute('onclick', '$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');')
        );

        $numberInferiorWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberInferiorWrapper);


        $numberSuperiorWrapper = new GenericTag('div');
        $numberSuperiorWrapper->addClass('col-sm-3');

        $numberSuperiorInput = new GenericVoidTag('input');
        $numberSuperiorInput->setAttribute('type', 'text');
        $numberSuperiorInput->setAttribute('name', $this->getFilterFormId($datagrid).'_higher');
        $numberSuperiorInput->setAttribute('id', $this->getFilterFormId($datagrid).'_higher');
        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperatorHigher])) {
            $numberSuperiorInput->setAttribute('value', $defaultValue[$this->filterOperatorHigher]);
        }
        $numberSuperiorInput->addClass('form-control');

        $inputGroupWrapper = new GenericTag('div', $numberSuperiorInput);
        $inputGroupWrapper->addClass('input-group');

        if (!empty($this->keywordFilterHigher)) {
            $keywordFilterPrefix = new GenericTag('span', $this->keywordFilterHigher);
            $keywordFilterPrefix->addClass('input-group-addon');
            $inputGroupWrapper->prependContent($keywordFilterPrefix);
        }

        $inputGroupWrapper->appendContent(
            $this->getResetFieldFilterFormSuffix($datagrid)
                ->setAttribute('onclick', '$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');')
        );

        $numberSuperiorWrapper->appendContent($inputGroupWrapper);

        $colWrapper->appendContent($numberSuperiorWrapper);

        return $colWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        $filterValue = '';

        // Condition de saisie du filtre.
        $filterValue .= 'var valueEqu = $(\'#'.$this->getFilterFormId($datagrid).'\').val();';
        $filterValue .= 'var valueInf = $(\'#'.$this->getFilterFormId($datagrid).'_lower\').val();';
        $filterValue .= 'var valueSup = $(\'#'.$this->getFilterFormId($datagrid).'_higher\').val();';
        $filterValue .= 'if ((valueEqu != \'\') || (valueInf != \'\') || (valueSup != \'\')) {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {";';
        $filterValue .= 'if (valueEqu != \'\') {';
        $filterValue .= 'filter += "\"'.$this->criteriaFilterOperator.'\":\"" + valueEqu + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if (valueInf != \'\') {';
        $filterValue .= 'if (valueEqu != \'\') {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'filter += "\"'.$this->filterOperatorLower.'\":\"" + valueInf + "\"";';
        $filterValue .= '}';
        $filterValue .= 'if (valueSup != \'\') {';
        $filterValue .= 'if ((valueEqu != \'\') || (valueInf != \'\')) {';
        $filterValue .= 'filter += ",";';
        $filterValue .= '}';
        $filterValue .= 'filter += "\"'.$this->filterOperatorHigher.'\":\"" + valueSup + "\"";';
        $filterValue .= '}';
        $filterValue .= 'filter += "},";';

        $filterValue .= '}';

        return $filterValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        $resetFields = '';

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');';
        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_lower\').val(\'\');';
        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'_higher\').val(\'\');';

        return $resetFields;
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('label', $this->getAddFormElementLabel());
        $colLabel->setAttribute('for', $this->getAddFormElementId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colLabel->addClass('field-label');
        $colWrapper->appendContent($colLabel);

        $numberWrapper = new GenericTag('div');
        $numberWrapper->addClass('col-sm-10');

        $numberInput = new GenericVoidTag('input');
        $numberInput->setAttribute('type', 'text');
        $numberInput->setAttribute('pattern', '-?[0-9]*[.,]?[0-9]*');
        $numberInput->setAttribute('name', $this->getAddFormElementId($datagrid));
        $numberInput->setAttribute('id', $this->getAddFormElementId($datagrid));
        $numberInput->setAttribute('value', $this->defaultAddValue);
        $numberInput->addClass('form-control');
        $numberWrapper->appendContent($numberInput);

        $colWrapper->appendContent($numberWrapper);

        return $colWrapper;
    }
}
