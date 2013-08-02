<?php
/**
 * Fichier de la classe Colonne Liste.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne liste.
 *
 * Une classe permettant de générer une colonne contenant des listes.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_List extends UI_Datagrid_Col_Generic
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
     * @var const
     */
    public $fieldType = null;

    /**
     * Définition du type de champs utilisé dans le filtre spécifiquement.
     * L'usage des champs BOX ne fonctionnent pas pour les listes dynamiques.
     * Par défaut le type de champs est celui de $fieldType.
     *
     * @var const
     */
    public $fieldTypeFilter = null;

    /**
     * Définition du mot clef du filtre pour l'égalité.
     *
     * @var string
     */
    public $keywordFilterEqual = null;

    /**
     * Définition du message affiché lors du chargement de la liste.
     *
     * @var string
     */
    public $loadingText = null;

    /**
     * Définition du message affiché lorsqu'une erreur se produit au chargement de la liste.
     *
     * @var string
     */
    public $errorText = null;

    /**
     * Définition du caractère utilisé pour la séparation des valeures lors d'une liste à choix multiple.
     *
     * @var string
     */
    public $separatorMultiple = null;

    /**
     * Permet de savoir si la liste sera chargé dynamiquement (methode getList du contrôleur).
     *
     * Par défaut non.
     *
     * @var   bool
     */
    public $dynamicList = false;

    /**
     * Tableau contenant les options de la liste (statique).
     * Chaine contenant l'url où charger la liste (dynamique).
     *
     * Par défaut null.
     *
     * @var   mixed (array|string)
     */
    public $list = null;

    /**
     * Booléen définissant si la valeur peut être multiple.
     *
     * Par défaut false.
     *
     * @var   bool
     */
    public $multiple = false;

    /**
     * Booléen défiissant si les filtres de type list peuvent comporter plusieurs valeurs.
     *
     * Par défaut false.
     *
     * @var bool
     */
    public $multipleFilters = null;

    /**
     * Nombre définissant la hauteur du champs select multiple.
     *
     * Par défaut auto.
     *
     * @var integer
     */
    public $multipleListSize = 'auto';

    /**
     * Permet de savoir si la liste aura un élément vide rajouté automatiquement.
     *  (ne sera pas pris en compte pour les filtres de type checkbox)
     *
     * Par défaut true.
     *
     * @var bool
     */
    public $withEmptyElement = true;


     /**
      * Constructeur de la classe ColList.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_LIST;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColListEqual');
        $this->filterOperator = Core_Model_Filter::OPERATOR_EQUAL;
        $this->fieldType = self::FIELD_LIST;
        $this->separatorMultiple = ', ';
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
    }

    /**
     * Créer l'url de base nécessaire à la récupération de la liste dynamique.
     *
     * @param UI_Datagrid $datagrid
     * @param string $source
     *
     * @return string
     */
    protected function getUrlDynamicList($datagrid, $source)
    {
        return $this->list . $datagrid->encodeParameters() . '/source/' . $source;
    }

    /**
     * Méthode renvoyant le formatter de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFormatter($datagrid)
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
     * Méthode renvoyant d'éventuelles fonctions complémentaires nécéssaires à la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getComplementaryFunction($datagrid)
    {
        $complementaryScript = '';
        
        if (($this->addable === true) && ($this->dynamicList === true)
            && ($this->fieldType !== self::FIELD_AUTOCOMPLETE)) {
            // Chargement de la liste dynamique à l'ouverture du popup.
            $complementaryScript .= '$(\'#'.$datagrid->id.'_addPanel\').on(\'show\', function() {';
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
            $complementaryScript .= '$(\'#'.$datagrid->id.'_addPanel\').on(\'hide\', function() {';
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
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditableOption($datagrid)
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
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditorValue($datagrid)
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
                $editorValue .= 'value = { id: value.toString(), text: value.toString()};';
                $editorValue .= '}';
            } else {
                $editorValue .= 'if (typeof(value) == "string") {';
                $editorValue .= 'value = { id: value.toString(), text: value.toString()};';
                $editorValue .= '}';
            }
            $editorValue .= '$(column.editor.textbox).val(\'\');';

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
            $editorValue .= 'for (var j = 0; j < data.length; j++) {';
            $editorValue .= 'if (data[i].id === value[j]) {';
            $editorValue .= 'selectedData.push(data[i])';
            $editorValue .= '}';
            $editorValue .= '}';
            $editorValue .= '}';
            $editorValue .= 'callback(selectedData);';
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
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     * @param array $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return Zend_Form_Element
     */
    public function getFilterFormElement($datagrid, $defaultValue=null)
    {
        if ($this->dynamicList === true) {
            return null;
        }

        if ($this->isFilterFieldMultiple()) {
            if ($this->getFilterFieldType() === self::FIELD_BOX) {
                $filterFormElement = new UI_Form_Element_MultiCheckbox($this->getFilterFormId($datagrid));
            } else {
                $filterFormElement = new UI_Form_Element_MultiSelect($this->getFilterFormId($datagrid));
                $filterFormElement->addNullOption('');
                $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);
                $filterFormElement->size = $this->multipleListSize;
            }
        } else {
            if ($this->getFilterFieldType() === self::FIELD_BOX) {
                $filterFormElement = new UI_Form_Element_Radio($this->getFilterFormId($datagrid));
            } else {
                $filterFormElement = new UI_Form_Element_Select($this->getFilterFormId($datagrid));
                $filterFormElement->addNullOption('');
                $filterFormElement->getElement()->addPrefix($this->keywordFilterEqual);
            }
        }

        $filterFormElement->setLabel($this->getFilterFormLabel());
        if ($this->getFilterFieldType() === self::FIELD_AUTOCOMPLETE) {
            // Nécessaire pour éviter le bug de select2 miltiple avec des appnd/prepend.
            if ($this->isFilterFieldMultiple() !== true) {
                $filterFormElement->useAutocomplete = true;
            }
        }
        foreach ($this->list as $idElement => $element) {
            $filterFormElement->addOption(new UI_Form_Element_Option($idElement, $idElement, $element));
        }

        // Récupération des valeurs par défaut.
        if (isset($defaultValue[$this->filterOperator])) {
            $filterFormElement->setValue($defaultValue[$this->filterOperator]);
        }
        if ($this->getFilterFieldType() === self::FIELD_BOX) {
            $resetButton = new UI_HTML_Button();
            $resetButton->icon = $datagrid->filterIconResetFieldSuffix;
            $resetAction = '$(\'#'.$this->getFilterFormId($datagrid).' :checked\')';
            $resetAction .= '.removeAttr(\'checked\');';
            $resetButton->addAttribute('onclick', $resetAction);

            $resetElement = new UI_Form_Element_HTML($this->getFilterFormId($datagrid).'_reset');
            $resetElement->content = $resetButton->getHTML();

            $filterFormElement->getElement()->addElement($resetElement);
        } else {
            if ($this->getFilterFieldType() === self::FIELD_AUTOCOMPLETE) {
                $resetFieldSuffix = '<i ';
                $resetFieldSuffix .= 'class="icon-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
                $resetFieldSuffix .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\').trigger(\'change\');"';
                $resetFieldSuffix .= '>';
                $resetFieldSuffix .= '</i>';
                $filterFormElement->getElement()->addSuffix($resetFieldSuffix);
            } else {
                $filterFormElement->getElement()->addSuffix($this->getResetFieldFilterFormSuffix($datagrid));
            }
        }

        return $filterFormElement;
    }

    /**
     * Méthode renvoyant la valeur du champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterValue($datagrid)
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
            $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
            $filterValue .= '\"'.$this->filterOperator.'\":" + selectedOptions + "';
            $filterValue .= '},";';

            $filterValue .= '}';
        } else {
            // Condition de saisie du filtre.
            $filterValue .= 'if (($(\'#'.$this->getFilterFormId($datagrid).'\').val() != null) ';
            $filterValue .= '&& ($(\'#'.$this->getFilterFormId($datagrid).'\').val() != \'\')) {';

            // Ajout au filtre.
            $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
            $filterValue .= '\"'.$this->filterOperator.'\":\"" ';
            $filterValue .= '+ $(\'#'.$this->getFilterFormId($datagrid).'\').val() + "\"';
            $filterValue .= '},";';

            $filterValue .= '}';
        }

        return $filterValue;
    }

    /**
     * Méthode renvoyant la réinitialisation des champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getResettingFilter($datagrid)
    {
        $resetFields = '';

        if ($this->dynamicList === true) {
            return '';
        }

        if ($this->getFilterFieldType() === self::FIELD_BOX) {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).' :checked\')';
            $resetFields .= '.removeAttr(\'checked\');';
        } else if ($this->getFilterFieldType() === self::FIELD_AUTOCOMPLETE) {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\').trigger(\'change\');';
        } else {
            $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');';
        }

        return $resetFields;
    }

    /**
     * Méthode renvoyant le champs du formulaire d'ajout de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return Zend_Form_Element
     */
    public function getAddFormElement($datagrid)
    {
        if ($this->dynamicList === true) {
            if ($this->fieldType === self::FIELD_AUTOCOMPLETE) {
                $addFormElement = new UI_Form_Element_Pattern_AjaxAutocomplete($this->getAddFormElementId($datagrid));
                $addFormElement->getAutocomplete()->source = $this->getUrlDynamicList($datagrid, 'add');
                $addFormElement->getAutocomplete()->multiple = $this->multiple;
                $addFormElement->setLabel($this->getAddFormElementLabel());
            } else {
                if ($this->multiple) {
                    $addFormElement = new UI_Form_Element_MultiSelect($this->getAddFormElementId($datagrid));
                    $addFormElement->size = $this->multipleListSize;
                } else {
                    $addFormElement = new UI_Form_Element_Select($this->getAddFormElementId($datagrid));
                }
                $addFormElement->setLabel($this->getAddFormElementLabel());
                $addFormElement->setValue($this->defaultAddValue);
                if (($this->multiple) && (is_array($this->defaultAddValue))) {
                    foreach ($this->defaultAddValue as $index => $defaultAddValue) {
                        $option = new UI_Form_Element_Option('loading'.$index, $defaultAddValue, $this->loadingText);
                        $addFormElement->addOption($option);
                    }
                } else {
                    $option = new UI_Form_Element_Option('loading', $this->defaultAddValue, $this->loadingText);
                    $addFormElement->addOption($option);
                }
            }
        } else {
            if ($this->multiple) {
                if ($this->fieldType === self::FIELD_BOX) {
                    $addFormElement = new UI_Form_Element_MultiCheckbox($this->getAddFormElementId($datagrid));
                } else {
                    $addFormElement = new UI_Form_Element_MultiSelect($this->getAddFormElementId($datagrid));
                    $addFormElement->size = $this->multipleListSize;
                }
            } else {
                if ($this->fieldType === self::FIELD_BOX) {
                    $addFormElement = new UI_Form_Element_Radio($this->getAddFormElementId($datagrid));
                } else {
                    $addFormElement = new UI_Form_Element_Select($this->getAddFormElementId($datagrid));
                }
            }
            if ($this->fieldType === self::FIELD_AUTOCOMPLETE) {
                $addFormElement->useAutocomplete = true;
            }
            $addFormElement->setLabel($this->getAddFormElementLabel());
            $addFormElement->setValue($this->defaultAddValue);
            if (($this->withEmptyElement === true) && ($this->fieldType !== self::FIELD_BOX)) {
                $addFormElement->addNullOption('');
            }
            foreach ($this->list as $idElement => $element) {
                $option = new UI_Form_Element_Option($idElement, $idElement, $element);
                $addFormElement->addOption($option);
            }
        }

        return $addFormElement;
    }

}