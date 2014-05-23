<?php

namespace UI\Datagrid\Column;

use MyCLabs\MUIH\Button;
use MyCLabs\MUIH\GenericTag;
use MyCLabs\MUIH\GenericVoidTag;
use MyCLabs\MUIH\Icon;
use UI\Datagrid\Datagrid;

/**
 * Classe représentant une colonne contenant des listes.
 *
 * @author valentin.claras
 */
class ListColumn extends GenericColumn
{
    /**
     * Constante definissant le type de champs à utiliser.
     * ici des champs select.
     *
     * @see fieldType
     */
    const FIELD_LIST = 'select';

    /**
     * Constante definissant le type de champs à utiliser.
     * ici des champs checkbox.
     *
     * @see fieldType
     */
    const FIELD_BOX = 'checkbox';

    /**
     * Constante definissant le type de champs à utiliser.
     * ici des champs text avec autocomplétion.
     *
     * @see fieldType
     */
    const FIELD_AUTOCOMPLETE = 'autocomplete';

    /**
     * Définition du type de champs utilisé par défaut.
     * L'usage des champs BOX ne fonctionnent pas pour les listes dynamiques.
     * L'usage des champs AUTOCOMPLETE ne fonctionnent pas pour les listes multiples.
     *
     * @var string
     */
    public $fieldType;

    /**
     * Définition du type de champs utilisé dans le filtre spécifiquement.
     * L'usage des champs BOX ne fonctionnent pas pour les listes dynamiques.
     * Par défaut le type de champs est celui de $fieldType.
     *
     * @var string
     */
    public $fieldTypeFilter;

    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual;

    /**
     * Définition du message affiché lors du chargement de la liste.
     *
     * @var string
     */
    public $loadingText;

    /**
     * Définition du message affiché lorsqu'une erreur se produit au chargement de la liste.
     *
     * @var string
     */
    public $errorText;

    /**
     * Définition du caractère utilisé pour la séparation des valeures lors d'une liste à choix multiple.
     *
     * @var string
     */
    public $separatorMultiple;

    /**
     * Permet de savoir si la liste sera chargé dynamiquement (methode getList du contrôleur).
     *
     * Par défaut non.
     *
     * @var bool
     */
    public $dynamicList = false;

    /**
     * Tableau contenant les options de la liste (statique).
     * Chaine contenant l'url où charger la liste (dynamique).
     *
     * Par défaut null.
     *
     * @var array|string
     */
    public $list;

    /**
     * Booléen définissant si la valeur peut être multiple.
     *
     * Par défaut false.
     *
     * @var bool
     */
    public $multiple = false;

    /**
     * Booléen défiissant si les filtres de type list peuvent comporter plusieurs valeurs.
     *
     * Par défaut false.
     *
     * @var bool
     */
    public $multipleFilters;

    /**
     * Permet de savoir si la liste aura un élément vide rajouté automatiquement.
     *  (ne sera pas pris en compte pour les filtres de type checkbox)
     *
     * Par défaut true.
     *
     * @var bool
     */
    public $withEmptyElement = true;


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColListEqual');
        $this->criteriaFilterOperator = 'eq';
        $this->fieldType = self::FIELD_LIST;
        $this->separatorMultiple = ', ';
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
    }

    /**
     * Créer l'url de base nécessaire à la récupération de la liste dynamique.
     *
     * @param Datagrid $datagrid
     * @param string $source
     *
     * @return string
     */
    public function getUrlDynamicList(Datagrid $datagrid, $source)
    {
        return $this->list . $datagrid->encodeParameters() . '/source/' . $source;
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'var value;';
        if ($this->dynamicList === true) {
            $format .= 'content = null;';
        }
        $format .= 'if (typeof(sData) != "object") {';
        $format .= 'value = sData;';
        $format .= '} else {';
        $format .= 'if (sData.content != null) {';
        $format .= 'content = sData.content;';
        $format .= '} else {';
        $format .= 'value = sData.value;';
        $format .= '}';
        $format .= '}';
        if ($this->dynamicList === true) {
            $format .= 'if (content == null) {';
            $format .= 'content = \''.$this->loadingText.'\';';
            $format .= '$.get(';
            $format .= '\''.$this->getUrlDynamicList($datagrid, 'format').'\',';
            $format .= '{ source: "editor", index : oRecord.getData(\'index\') },';
            $format .= 'function(data) {';
            if ($this->multiple === true) {
                $format .= 'var content = \'\';';
                $format .= 'for (var i = 0; i < value.length; i++) {';
                $format .= 'if (typeof(data[value[i].toString()]) != "undefined") {';
                $format .= 'if (content != \'\') {';
                $format .= 'content += \''.$this->separatorMultiple.'\';';
                $format .= '}';
                $format .= 'content += data[value[i].toString()];';
                $format .= '}';
                $format .= '}';
            } else {
                $format .= 'if (typeof(data[value]) != "undefined") {';
                $format .= 'var content = data[value]';
                $format .= '}';
            }
            $format .= $this->addEditableFormatter();
            $format .= 'Cell.innerHTML = content;';
            $format .= '}).error(function(o){';
            $format .= 'Cell.innerHTML = \''.$this->errorText.'\'';
            $format .= '});';
        } else {
            $format .= 'if (content == \'\') {';
            if ($this->multiple === true) {
                $format .= 'if (typeof(valeur) == "string") {';
                $format .= 'value = new Array(value);';
                $format .= '}';
                $format .= 'for (var i in value) {';
            }
            foreach ($this->list as $idElem => $element) {
                if ($this->multiple == true) {
                    $format .= 'if (value[i]  == "'.$idElem.'") {';
                    $format .= 'if (content != \'\') {';
                    $format .= 'content += \''.$this->separatorMultiple.'\';';
                    $format .= '}';
                } else {
                    $format .= 'if (value == "'.$idElem.'") {';
                }
                $format .= 'content += \''.addslashes($element).'\';';
                $format .= '}';
            }
            if ($this->multiple == true) {
                $format .= '}';
            }
        }
        $format .= '}';

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getComplementaryFunction(Datagrid $datagrid)
    {
        $complementaryScript = '';
        
        if (($this->addable === true) && ($this->dynamicList === true)
            && ($this->fieldType !== self::FIELD_AUTOCOMPLETE)) {
            // Chargement de la liste dynamique à l'ouverture du popup.
            $complementaryScript .= '$(\'#'.$datagrid->id.'_addPanel\').on(\'show.bs.modal\', function() {';
            $complementaryScript .= 'var listAddFormField = $(\'#'.$this->getAddFormElementId($datagrid).'\');';
            $complementaryScript .= 'var value = listAddFormField.val();';
            $complementaryScript .= '$.get(';
            $complementaryScript .= '\''.$this->getUrlDynamicList($datagrid, 'add').'\', ';
            $complementaryScript .= 'function(o){';
            $complementaryScript .= 'listAddFormField.formActionSetOptions(o);';
            $complementaryScript .= 'listAddFormField.val(value);';
            $complementaryScript .= '}';
            $complementaryScript .= ').error(function(o) {';
            $complementaryScript .= 'var errorOptions = {};';
            $complementaryScript .= 'errorOptions[value] = \''.$this->errorText.'\';';
            $complementaryScript .= 'listAddFormField.formActionSetOptions(errorOptions);';
            $complementaryScript .= 'listAddFormField.val(value);';
            $complementaryScript .= '});';
            $complementaryScript .= '});';
            // Remise à zéro de la liste à la fermeture du popup.
            $complementaryScript .= '$(\'#'.$datagrid->id.'_addPanel\').on(\'hide.bs.modal\', function() {';
            $complementaryScript .= 'var listAddFormField = $(\'#'.$this->getAddFormElementId($datagrid).'\');';
            $complementaryScript .= 'var loadingOptions = {};';
            if (($this->multiple) && (is_array($this->defaultAddValue))) {
                foreach ($this->defaultAddValue as $defaultAddValue) {
                    $complementaryScript .= 'loadingOptions[\''.$defaultAddValue.'\'] = \''.$this->loadingText.'\';';
                }
                $complementaryScript .= 'listAddFormField.formActionSetOptions(loadingOptions);';
                $complementaryScript .= 'listAddFormField.val([\''.implode('\',\'', $this->defaultAddValue).'\']);';
            } else {
                $complementaryScript .= 'loadingOptions[\''.$this->defaultAddValue.'\'] = \''.$this->loadingText.'\';';
                $complementaryScript .= 'listAddFormField.formActionSetOptions(loadingOptions);';
                $complementaryScript .= 'listAddFormField.val(\''.$this->defaultAddValue.'\');';
            }
            $complementaryScript .= '});';
        }
        
        return $complementaryScript;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditableOption(Datagrid $datagrid)
    {
        if (($this->dynamicList === true) && ($this->fieldType === self::FIELD_AUTOCOMPLETE)) {
            return parent::getEditableOption($datagrid);
        }

        $editOption = '';

        $liste = '[';
        if ($this->dynamicList === false) {
            if (($this->fieldType !== self::FIELD_BOX) && ($this->withEmptyElement === true)) {
                $liste .= '{label:"", value:""},';
                if (empty($this->list)) {
                    $liste = substr($liste, 0, -1);
                }
            }
            foreach ($this->list as $index => $valeur) {
                $liste .= '{label:"'.addslashes($valeur).'", value:"'.$index.'"},';
            }
            if (!(empty($this->list))) {
                $liste = substr($liste, 0, -1);
            }
        } else {
            $liste .= '"'.$this->loadingText.'"';
        }
        $liste .= ']';
        if (($this->dynamicList !== true) && ($this->multiple === true) && ($this->fieldType === self::FIELD_BOX)) {
            $editOption .= ', editor:new YAHOO.widget.CheckboxCellEditor({';
            $editOption .= 'checkboxOptions:'.$liste.', '.
                            'asyncSubmitter: function(callback, newValue) {},'.
                            'LABEL_SAVE: \''.$this->editLabelSave.'\','.
                            'LABEL_CANCEL: \''.$this->editLabelCancel.'\''.
                        '})';
        } else {
            $editOption .= ', editor:new YAHOO.widget.DropdownCellEditor({';
            if ($this->multiple === true) {
                $editOption .= 'multiple: true, ';
            } else {
                $editOption .= 'multiple: false, ';
            }
            $editOption .= 'dropdownOptions:'.$liste.', '.
                            'asyncSubmitter: function(callback, newValue) {},'.
                            'LABEL_SAVE: \''.$this->editLabelSave.'\','.
                            'LABEL_CANCEL: \''.$this->editLabelCancel.'\''.
                        '})';
        }

        return $editOption;
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        $editorValue = '';

        $editorValue .= 'this.onEventShowCellEditor(oArgs);';

        if (($this->dynamicList === true) && ($this->fieldType === self::FIELD_AUTOCOMPLETE)) {
            $editorValue .= 'if ((typeof(sData) == "undefined") || (sData == null)) {';
            $editorValue .= 'var value = \'\';';
            $editorValue .= '} else if (typeof(sData) != "object") {';
            $editorValue .= 'var value = sData;';
            $editorValue .= '} else {';
            $editorValue .= 'var value = sData.value;';
            $editorValue .= '}';
            if ($this->multiple === true) {
                $editorValue .= 'if (typeof(value) == "string") {';
                $editorValue .= 'value = new Array(value.toString());';
                $editorValue .= '}';
            } else {
                $editorValue .= 'if (typeof(value) == "string") {';
                $editorValue .= 'value = new Array(value.toString());';
                $editorValue .= '}';
            }
            $editorValue .= '$(column.editor.textbox).val(\'\');';
            $editorValue .= '$(column.editor.textbox).select2(\'val\', \'\');';

            $editorValue .= '$.get(';
            $editorValue .= '\''.$this->getUrlDynamicList($datagrid, 'edit').'\',';
            $editorValue .= '{ source: "editor", index : record.getData(\'index\'), "_": "1" },';
            $editorValue .= 'function(data) {';

            $editorValue .= '$(column.editor.textbox).select2(';
            $editorValue .= '{';
            $editorValue .= 'containerCss: {width: "100%"},';
            if ($this->withEmptyElement === true) {
                $editorValue .= 'allowClear: true,';
            }
            if ($this->multiple === true) {
                $editorValue .= 'multiple: true,';
            }
            $editorValue .= 'data: data,';
            $editorValue .= 'initSelection: function(element, callback){';
            $editorValue .= 'var selectedData = new Array();';
            $editorValue .= 'for (var i = 0; i < data.length; i++) {';
            $editorValue .= 'for (var j = 0; j < value.length; j++) {';
            $editorValue .= 'if (data[i].id === value[j].id) {';
            $editorValue .= 'selectedData.push(data[i])';
            $editorValue .= '}';
            $editorValue .= '}';
            $editorValue .= '}';
            if ($this->multiple === true) {
                $editorValue .= 'callback(selectedData);';
            } else {
                $editorValue .= 'callback(selectedData[0]);';
            }
            $editorValue .= '},';
            $editorValue .= 'ajax: {';
            $editorValue .= 'url: "'.$this->getUrlDynamicList($datagrid, 'edit').'",';
            $editorValue .= 'dataType: "json",';
            $editorValue .= 'quietMillis: 100,';
            $editorValue .= 'data: function(term, page) { return {q: term} },';
            $editorValue .= 'results: function(data, page) { return {results: data} },';
            $editorValue .= '},';
            $editorValue .= '}';
            $editorValue .= ');';

            $editorValue .= '$(column.editor.textbox).select2(\'val\', value);';
            $editorValue .= '});';
        } else {
            if (($this->multiple === true) && ($this->fieldType === self::FIELD_BOX) && ($this->dynamicList !== true)) {
                $editorValue .= 'var options = column.editor.checkboxes;';
            } else {
                $editorValue .= 'var options = column.editor.dropdown.options;';
            }
            if ($this->dynamicList === true) {
                $editorValue .= '$(options).remove();';
                $editorValue .= '$(column.editor.dropdown).append($(\'<option></option>\')';
                $editorValue .= '.attr(\'value\', \'\').text(\''.$this->loadingText.'\'));';
                $editorValue .= '$.get(';
                $editorValue .= '\''.$this->getUrlDynamicList($datagrid, 'edit').'\',';
                $editorValue .= '{ source: "editor", index : record.getData(\'index\') },';
                $editorValue .= 'function(data) {';
                $editorValue .= '$(column.editor.dropdown).formActionSetOptions(data);';
            }
            $editorValue .= 'if ((typeof(sData) == "undefined") || (sData == null)) {';
            $editorValue .= 'var value = \'\';';
            $editorValue .= '} else if (typeof(sData) != "object") {';
            $editorValue .= 'var value = sData;';
            $editorValue .= '} else {';
            $editorValue .= 'var value = sData.value;';
            $editorValue .= '}';
            if ($this->multiple === true) {
                $editorValue .= 'if (typeof(value) == "string") {';
                $editorValue .= 'value = new Array(value.toString());';
                $editorValue .= '}';
            } else {
                $editorValue .= 'if (typeof(value) == "string") {';
                $editorValue .= 'value = value.toString();';
                $editorValue .= '}';
            }
            $editorValue .= 'for (var i = 0; i < options.length; i++) {';
            if ($this->multiple === true) {
                $editorValue .= 'var selectedIndex = false;';
                $editorValue .= 'for (var j in value) {';
                if (($this->fieldType === self::FIELD_BOX) && ($this->dynamicList !== true)) {
                    $editorValue .= 'if (options[i].value == value[j]) {';
                    $editorValue .= 'options[i].checked = true;';
                    $editorValue .= 'selectedIndex = true;';
                    $editorValue .= '}';
                    $editorValue .= '}';
                    $editorValue .= 'if (selectedIndex == false) {';
                    $editorValue .= 'options[i].checked = false;';
                } else {
                    $editorValue .= 'if (options[i].value == value[j]) {';
                    $editorValue .= 'options[i].selected = \'selected\';';
                    $editorValue .= 'selectedIndex = true;';
                    $editorValue .= '}';
                    $editorValue .= '}';
                    $editorValue .= 'if (selectedIndex == false) {';
                    $editorValue .= 'options[i].selected = false;';
                    $editorValue .= 'options[i].removeAttribute(\'selected\');';
                }
                $editorValue .= '}';
            } else {
                $editorValue .= 'if (options[i].value == value) {';
                $editorValue .= 'options[i].selected = \'selected\';';
                $editorValue .= '} else {';
                $editorValue .= 'options[i].selected = false;';
                $editorValue .= 'options[i].removeAttribute(\'selected\');';
                $editorValue .= '}';
            }
            $editorValue .= '}';
            if ($this->dynamicList === true) {
                $editorValue .= '}).error(function(o){';
                $editorValue .= '$(options).remove();';
                $editorValue .= '$(column.editor.dropdown).append($(\'<option></option>\')';
                $editorValue .= '.attr(\'value\', \'\').text(\''.$this->errorText.'\'));';
                $editorValue .= '});';
            }
            if ($this->fieldType === self::FIELD_AUTOCOMPLETE) {
                $editorValue .= '$(column.editor.dropdown).select2(';
                $editorValue .= '{';
                if ($this->withEmptyElement === true) {
                    $editorValue .= 'allowClear: true,';
                }
                $editorValue .= 'containerCss: {width: "100%"},';
                $editorValue .= '}';
                $editorValue .= ');';
            }
        }

        return $editorValue;
    }

    /**
     * Indique le type de champs utilisé pour le filtre.
     *
     * @return string
     */
    protected function getFilterFieldType()
    {
        if ($this->fieldTypeFilter === null) {
            return $this->fieldType;
        } else {
            return $this->fieldTypeFilter;
        }
    }

    /**
     * Indique la multiplicité de champs utilisé pour le filtre.
     *
     * @return string
     */
    protected function isFilterFieldMultiple()
    {
        if ($this->multipleFilters === null) {
            return $this->multiple;
        } else {
            return $this->multipleFilters;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null)
    {
        if ($this->dynamicList === true) {
            return null;
        }

        $colWrapper = new GenericTag('div');
        $colWrapper->addClass('form-group');

        $colLabel = new GenericTag('label', $this->getFilterFormLabel());
        $colLabel->setAttribute('for', $this->getFilterFormId($datagrid));
        $colLabel->addClass('col-sm-2');
        $colLabel->addClass('control-label');
        $colLabel->addClass('field-label');
        $colWrapper->appendContent($colLabel);

        $selectWrapper = new GenericTag('div');
        $selectWrapper->addClass('col-sm-10');

        // Valeur par défaut du filtre.
        $defaultFilterValue = null;
        if (isset($defaultValue[$this->criteriaFilterOperator])) {
            $defaultFilterValue = $defaultValue[$this->criteriaFilterOperator];
        }

        if ($this->isFilterFieldMultiple()) {
            if ($this->getFilterFieldType() === self::FIELD_BOX) {
                $selectWrapper->setAttribute('id', $this->getFilterFormId($datagrid));

                foreach ($this->list as $idElement => $element) {
                    $elementInput = new GenericVoidTag('input');
                    $elementInput->setAttribute('type', 'checkbox');
                    $elementInput->setAttribute('name', $this->getFilterFormId($datagrid));
                    $elementInput->setAttribute('value', $idElement);
                    $elementInput->setAttribute('id', $this->getFilterFormId($datagrid).'_'.$idElement);
                    $elementOption = new GenericTag('label');
                    $elementOption->addClass('checkbox-inline');
                    $elementOption->appendContent($elementInput);
                    $elementOption->appendContent($element);
                    $selectWrapper->appendContent($elementOption);

                    if (($defaultFilterValue === $idElement)
                        || (is_array($defaultFilterValue) && (in_array($idElement, $defaultFilterValue)))) {
                        $elementOption->setBooleanAttribute('checked');
                    }
                }
            } else {
                $selectInput = new GenericTag('select');
                $selectInput->setAttribute('name', $this->getFilterFormId($datagrid).'[]');
                $selectInput->setAttribute('id', $this->getFilterFormId($datagrid));
                if ($this->fieldType !== self::FIELD_AUTOCOMPLETE) {
                    $selectInput->addClass('form-control');
                }
                $selectInput->setAttribute('multiple', 'multiple');

                $elementOption = new GenericTag('option', '');
                $elementOption->setAttribute('value', '');
                $selectInput->appendContent($elementOption);
                foreach ($this->list as $idElement => $element) {
                    $elementOption = new GenericTag('option', $element);
                    $elementOption->setAttribute('value', $idElement);
                    $selectInput->appendContent($elementOption);

                    if (($defaultFilterValue === $idElement)
                        || (is_array($defaultFilterValue) && (in_array($idElement, $defaultFilterValue)))) {
                        $elementOption->setBooleanAttribute('selected');
                    }
                }

                $selectWrapper->appendContent($selectInput);
            }
        } else {
            if ($this->getFilterFieldType() === self::FIELD_BOX) {
                $selectWrapper->setAttribute('id', $this->getFilterFormId($datagrid));

                foreach ($this->list as $idElement => $element) {
                    $elementInput = new GenericVoidTag('input');
                    $elementInput->setAttribute('type', 'radio');
                    $elementInput->setAttribute('name', $this->getFilterFormId($datagrid));
                    $elementInput->setAttribute('value', $idElement);
                    $elementInput->setAttribute('id', $this->getFilterFormId($datagrid).'_'.$idElement);
                    $elementOption = new GenericTag('label');
                    $elementOption->addClass('radio-inline');
                    $elementOption->appendContent($elementInput);
                    $elementOption->appendContent($element);
                    $selectWrapper->appendContent($elementOption);

                    if ($defaultFilterValue === $idElement) {
                        $elementOption->setBooleanAttribute('checked');
                    }
                }
            } else {
                $selectInput = new GenericTag('select');
                $selectInput->setAttribute('name', $this->getFilterFormId($datagrid));
                $selectInput->setAttribute('id', $this->getFilterFormId($datagrid));
                if ($this->fieldType !== self::FIELD_AUTOCOMPLETE) {
                    $selectInput->addClass('form-control');
                }

                $elementOption = new GenericTag('option', '');
                $elementOption->setAttribute('value', '');
                $selectInput->appendContent($elementOption);
                foreach ($this->list as $idElement => $element) {
                    $elementOption = new GenericTag('option', $element);
                    $elementOption->setAttribute('value', $idElement);
                    $selectInput->appendContent($elementOption);

                    if ($defaultFilterValue === $idElement) {
                        $elementOption->setBooleanAttribute('selected');
                    }
                }

                $selectWrapper->appendContent($selectInput);
            }
        }

        if ($this->getFilterFieldType() === self::FIELD_BOX) {
            $resetFieldIcon = new Icon($datagrid->filterIconResetFieldSuffix);
            $resetFieldIcon->addClass('reset');

            $resetFieldSuffix = new Button($resetFieldIcon);
            $resetFieldSuffix->setAttribute('onclick', '$(\'input[name=\\\''.$this->getFilterFormId($datagrid).'\\\']:checked\').removeAttr(\'checked\');');
            $selectWrapper->appendContent(' ');
            $selectWrapper->appendContent($resetFieldSuffix);
        }

        $colWrapper->appendContent($selectWrapper);

        return $colWrapper;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        $filterValue = '';

        if ($this->dynamicList === true) {
            return '';
        }
        // Ajout au filtre.
        if (($this->getFilterFieldType() === self::FIELD_BOX) && ($this->dynamicList !== true)) {
            $filterValue .= 'var inputOptions = $(\'#'.$this->getFilterFormId($datagrid).' :checked\');';
            $filterValue .= 'var selectedOptions = \'[\';';
            $filterValue .= 'for (var i = 0; i < inputOptions.length; i++) {';
            $filterValue .= 'if (selectedOptions != \'[\') {';
            $filterValue .= 'selectedOptions += \', \';';
            $filterValue .= '}';
            $filterValue .= 'selectedOptions += \'"\' + $(inputOptions[i]).val() + \'"\';';
            $filterValue .= '}';
            $filterValue .= 'selectedOptions += \']\';';

            // Condition de saisie du filtre.
            $filterValue .= 'if (selectedOptions != \'[]\') {';

            // Ajout au filtre.
            $filterValue .= 'filter += "{\"'.$this->getFullFilterName($datagrid).'\": {';
            $filterValue .= '\"'.$this->criteriaFilterOperator.'\":" + selectedOptions + "';
            $filterValue .= '}},";';

            $filterValue .= '}';
        } else {
            // Condition de saisie du filtre.
            $filterValue .= 'if (($(\'#'.$this->getFilterFormId($datagrid).'\').val() != null) ';
            $filterValue .= '&& ($(\'#'.$this->getFilterFormId($datagrid).'\').val() != \'\')) {';

            // Ajout au filtre.
            $filterValue .= 'filter += "{\"'.$this->getFullFilterName($datagrid).'\": {';
            $filterValue .= '\"'.$this->criteriaFilterOperator.'\":\"" ';
            $filterValue .= '+ $(\'#'.$this->getFilterFormId($datagrid).'\').val() + "\"';
            $filterValue .= '}},";';

            $filterValue .= '}';
        }

        return $filterValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        $resetFields = '';

        if ($this->dynamicList === true) {
            return '';
        }

        if ($this->getFilterFieldType() === self::FIELD_BOX) {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).' :checked\')';
            $resetFields .= '.removeAttr(\'checked\');';
        } elseif ($this->getFilterFieldType() === self::FIELD_AUTOCOMPLETE) {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\').trigger(\'change\');';
        } else {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');';
        }

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

        $selectWrapper = new GenericTag('div');
        $selectWrapper->addClass('col-sm-10');

        if ($this->dynamicList === true) {
            if ($this->fieldType === self::FIELD_AUTOCOMPLETE) {
                $textInput = new GenericVoidTag('input');
                $textInput->setAttribute('type', 'hidden');
                $textInput->setAttribute('name', $this->getAddFormElementId($datagrid));
                $textInput->setAttribute('id', $this->getAddFormElementId($datagrid));
                $textInput->setAttribute('value', $this->defaultAddValue);
                $selectWrapper->appendContent($textInput);
            } else {
                $selectInput = new GenericTag('select');
                $selectInput->setAttribute('name', $this->getAddFormElementId($datagrid));
                $selectInput->setAttribute('id', $this->getAddFormElementId($datagrid));
                $selectInput->addClass('form-control');
                if ($this->multiple) {
                    $selectInput->setAttribute('name', $this->getAddFormElementId($datagrid).'[]');
                    $selectInput->setAttribute('multiple', 'multiple');
                }
                if (($this->multiple) && (is_array($this->defaultAddValue))) {
                    foreach ($this->defaultAddValue as $index => $defaultAddValue) {
                        $elementOption = new GenericTag('option', $this->loadingText);
                        $elementOption->setAttribute('value', $defaultAddValue);
                        $selectInput->appendContent($elementOption);
                    }
                } else {
                    $elementOption = new GenericTag('option', $this->loadingText);
                    $elementOption->setAttribute('value', $this->defaultAddValue);
                    $selectInput->appendContent($elementOption);
                }
                $selectWrapper->appendContent($selectInput);
            }
        } else {
            if ($this->multiple) {
                if ($this->fieldType === self::FIELD_BOX) {
                    $selectWrapper->setAttribute('id', $this->getAddFormElementId($datagrid));

                    foreach ($this->list as $idElement => $element) {
                        $elementInput = new GenericVoidTag('input');
                        $elementInput->setAttribute('type', 'checkbox');
                        $elementInput->setAttribute('name', $this->getAddFormElementId($datagrid));
                        $elementInput->setAttribute('value', $idElement);
                        $elementInput->setAttribute('id', $this->getAddFormElementId($datagrid).'_'.$idElement);
                        $elementOption = new GenericTag('label');
                        $elementOption->addClass('checkbox-inline');
                        $elementOption->appendContent($elementInput);
                        $elementOption->appendContent($element);
                        $selectWrapper->appendContent($elementOption);

                        if (is_array($this->defaultAddValue) && in_array($idElement, $this->defaultAddValue)
                            || ($idElement === $this->defaultAddValue)) {
                            $elementOption->setBooleanAttribute('checked');
                        }
                    }
                } else {
                    $selectInput = new GenericTag('select');
                    $selectInput->setAttribute('name', $this->getAddFormElementId($datagrid).'[]');
                    $selectInput->setAttribute('id', $this->getAddFormElementId($datagrid));
                    if ($this->fieldType !== self::FIELD_AUTOCOMPLETE) {
                        $selectInput->addClass('form-control');
                    }
                    $selectInput->setAttribute('multiple', 'multiple');

                    if ($this->withEmptyElement === true) {
                        $elementOption = new GenericTag('option', '');
                        $elementOption->setAttribute('value', '');
                        $selectInput->appendContent($elementOption);
                    }
                    foreach ($this->list as $idElement => $element) {
                        $elementOption = new GenericTag('option', $element);
                        $elementOption->setAttribute('value', $idElement);
                        if (is_array($this->defaultAddValue) && in_array($idElement, $this->defaultAddValue)
                            || ($idElement === $this->defaultAddValue)) {
                            $elementOption->setBooleanAttribute('selected');
                        }
                        $selectInput->appendContent($elementOption);
                    }

                    $selectWrapper->appendContent($selectInput);
                }
            } else {
                if ($this->fieldType === self::FIELD_BOX) {
                    $selectWrapper->setAttribute('id', $this->getAddFormElementId($datagrid));

                    foreach ($this->list as $idElement => $element) {
                        $elementInput = new GenericVoidTag('input');
                        $elementInput->setAttribute('type', 'radio');
                        $elementInput->setAttribute('name', $this->getAddFormElementId($datagrid));
                        $elementInput->setAttribute('value', $idElement);
                        $elementInput->setAttribute('id', $this->getAddFormElementId($datagrid).'_'.$idElement);
                        $elementOption = new GenericTag('label');
                        $elementOption->addClass('radio-inline');
                        $elementOption->appendContent($elementInput);
                        $elementOption->appendContent($element);
                        $selectWrapper->appendContent($elementOption);

                        if ($idElement === $this->defaultAddValue) {
                            $elementOption->setBooleanAttribute('checked');
                        }
                    }
                } else {
                    $selectInput = new GenericTag('select');
                    $selectInput->setAttribute('name', $this->getAddFormElementId($datagrid));
                    $selectInput->setAttribute('id', $this->getAddFormElementId($datagrid));
                    if ($this->fieldType !== self::FIELD_AUTOCOMPLETE) {
                        $selectInput->addClass('form-control');
                    }

                    if ($this->withEmptyElement === true) {
                        $elementOption = new GenericTag('option', '');
                        $elementOption->setAttribute('value', '');
                        $selectInput->appendContent($elementOption);
                    }
                    foreach ($this->list as $idElement => $element) {
                        $elementOption = new GenericTag('option', $element);
                        $elementOption->setAttribute('value', $idElement);
                        if ($idElement === $this->defaultAddValue) {
                            $elementOption->setBooleanAttribute('selected');
                        }
                        $selectInput->appendContent($elementOption);
                    }

                    $selectWrapper->appendContent($selectInput);
                }
            }
        }

        $colWrapper->appendContent($selectWrapper);

        return $colWrapper;
    }
}
