<?php

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;
use UI_Form_ZendElement;

/**
 * Classe abstraite représentant une colonne de datagrid.
 *
 * @author valentin.claras
 */
abstract class GenericColumn
{
    /**
     * Constante definissant la classe positionnant les textes
     * ici une clase positionant le texte à gauche.
     */
    const DISPLAY_TEXT_LEFT = 'datagrid_align_left';

    /**
     * Constante definissant la classe positionnant les textes
     * ici une clase positionant le texte au centre.
     */
    const DISPLAY_TEXT_CENTER = 'datagrid_align_middle';

    /**
     * Constante definissant la classe positionnant les textes
     * ici une clase positionant le texte à droite.
     */
    const DISPLAY_TEXT_RIGHT = 'datagrid_align_right';

    /**
     * Définition de l'alignement du texte de la colonne.
     *
     * @var string
     */
    public $valueAlignment;

    /**
     * Définition du label d'édition affiché dans la cellule.
     *
     * @var string
     */
    public $editableLabel;

    /**
     * Définition du label du bouton de sauvegarde de l'éditeur.
     *
     * @var string
     */
    public $editLabelSave;

    /**
     * Définition du label du bouton d'annulation de l'éditeur.
     *
     * @var string
     */
    public $editLabelCancel;

    /**
     * Identifiant unique de la colonne.
     *
     * Cet identifiant permet d'identifier de manière unique chaque colonne.
     * Il est utilisé pour récupérer les données de la colonne.
     *
     * @var string
     */
    public $id;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé comme titre de la colonne.
     *  si il reste nul, la colonne n'aura pas de titre.
     *
     * @var string
     */
    public $label;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé en priorité comme titre pour l'ajout, sinon, le label de la colonne est utilisé.
     *
     * @var string
     */
    public $labelAdd;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé en priorité comme titre pour le filtre, sinon, le label de la colonne est utilisé.
     *
     * @var string
     */
    public $labelFilter;

    /**
     * Permet de savoir si la colonne est présente dans la formulaire d'ajout par défaut.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $addable = true;

    /**
     * Valeur par défaut dans le popup d'ajout.
     *
     * @var string
     */
    public $defaultAddValue;

    /**
     * Permet de savoir si la colonne est modifiable dans la ligne.
     *
     * Par défaut non.
     *
     * @var bool
     */
    public $editable = false;

    /**
     * Permet de savoir si le label d'édition sera affiché dans la cellule si elle est éditable.
     *
     * Par défaut oui.
     *
     * @var bool
     */
    public $displayLabelEditable = true;

    /**
     * Nom du criteria de la colonne pour le filtrage.
     * Par defaut vaut null et empèche le filtrage.
     *
     * @var string
     */
    public $criteriaFilterAttribute;

    /**
     * Nom de l'opérateur criteria utilisé lors du filtre sur cette colonne.
     * Par defaut vaut null et empèche le filtre.
     *
     * @var string
     */
    public $criteriaFilterOperator;

    /**
     * Nom du criteria de la colonne pour le tri.
     * Par defaut vaut null et empèche le tri.
     *
     * @var string
     */
    public $criteriaOrderAttribute;


    /**
     * @param string $id    Identifiant unique de la colonne.
     * @param string $label Texte afiché en titre de la colone.
     */
    public function __construct($id = null, $label = null)
    {
        $this->id = $id;
        $this->label = $label;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->editableLabel = '<i class="icon-pencil"></i>';
        $this->editLabelSave = __('UI', 'verb', 'save');
        $this->editLabelCancel = __('UI', 'verb', 'cancel');
    }

    /**
     * Méthode renvoyant le formatter de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string chaîne javascript du formatter de la colonne YUI.
     */
    public function getFormattingFunction(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'YAHOO.widget.DataTable.Formatter.format'.$datagrid->id.$this->id.' = ';
        $format .= 'function(Cell, oRecord, oColumn, sData) {';
        $format .= 'var content = \'\';';
        $format .= 'if ((typeof(sData) != "undefined") && (sData != null)) {';
        $format .= $this->getFormatter($datagrid);
        $format .= '}';
        $format .= $this->addEditableFormatter();
        $format .= 'Cell.innerHTML = \'<span class="'.$this->valueAlignment.'">\' + content + \'</span>\';';
        $format .= '};';

        $format .= $this->getComplementaryFunction($datagrid);

        return $format;
    }

    /**
     * Méthode renvoyant le formatter de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    abstract protected function getFormatter(Datagrid $datagrid);

    /**
     * Méthode renvoyant d'éventuelles fonctions complémentaires nécéssaires à la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    protected function getComplementaryFunction(Datagrid $datagrid)
    {
        return '';
    }

    /**
     * Ajoute l'icone d'édition à la cellule.
     *
     * @return string
     */
    protected function addEditableFormatter()
    {
        $editableFormatter = '';

        // Vérification de la possibilité d'éditer la cellule.
        if ($this->editable === true) {
            $editableFormatter .= 'if ((typeof(sData) != "undefined") && (sData != null) &&';
            $editableFormatter .= ' (typeof(sData.editable) != "undefined") && (sData.editable == false)) {';
            $editableFormatter .= 'YAHOO.util.Dom.removeClass(Cell.parentNode, \'yui-dt-editable\');';
            if ($this->displayLabelEditable === true) {
                $editableFormatter .= 'content += \'<span class="datagrid_cell_editable">';
                $editableFormatter .= '&nbsp;';
                $editableFormatter .= '</span>\';';
                $editableFormatter .= '} else {';
                $editableFormatter .= 'content += \'<span class="datagrid_cell_editable">';
                $editableFormatter .= $this->editableLabel;
                $editableFormatter .= '</span>\';';
            }
            $editableFormatter .= '}';
        }

        return $editableFormatter;
    }

    /**
     * Méthode renvoyant la définition de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getDefinition(Datagrid $datagrid)
    {
        $definition = '';

        $definition .= '{';
        $definition .= 'key:"'.$this->id.'", ';
        $definition .= 'label:"<span>'.$this->label.'</span>", ';
        $definition .= 'formatter:"format'.$datagrid->id.$this->id.'"';
        if ($this->criteriaOrderAttribute !== null) {
            $definition .= $this->getSortOption($datagrid);
        } else {
            $definition .= ', sortable:false';
        }
        if ($this->editable === true) {
            $definition .= $this->getEditableOption($datagrid);
        }
        $definition .= ' }';

        return $definition;
    }

    /**
     * Méthode renvoyant les options de tri de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    protected function getSortOption(Datagrid $datagrid)
    {
        $sortOption = '';

        $sortOption .= ', sortable:true, sortOptions:{';
        $sortOption .= 'field:\''.$this->getFullSortName($datagrid).'\',';
        $sortOption .= 'defaultDir:YAHOO.widget.DataTable.CLASS_ASC';
        $sortOption .= '}';

        return $sortOption;
    }

    /**
     * Méthode renvoyant le nom complet de tri.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getFullSortName(Datagrid $datagrid)
    {
        return $this->criteriaOrderAttribute;
    }

    /**
     * Méthode renvoyant le nom complet de filtre.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getFullFilterName(Datagrid $datagrid)
    {
        return $this->criteriaFilterAttribute;
    }

    /**
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    protected function getEditableOption(Datagrid $datagrid)
    {
        $editOption = '';

        $editOption .= ', editor:new YAHOO.widget.TextboxCellEditor({';
        $editOption .= 'asyncSubmitter: function(callback, newValue) {},';
        $editOption .= 'LABEL_SAVE: \''.$this->editLabelSave.'\',';
        $editOption .= 'LABEL_CANCEL: \''.$this->editLabelCancel.'\'';
        $editOption .= '})';

        return $editOption;
    }

    /**
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getEditor(Datagrid $datagrid)
    {
        $editor = '';

        $editor .= 'if (column.key == "'.$this->id.'") {';
        $editor .= 'var sData = record.getData(column.key);';
        $editor .= $this->getEditorValue($datagrid);
        $editor .= '}';

        return $editor;
    }

    /**
     * Méthode renvoyant l'appel à l'édition de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    abstract protected function getEditorValue(Datagrid $datagrid);

    /**
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param Datagrid   $datagrid
     * @param array|null $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return UI_Form_ZendElement
     */
    abstract public function getFilterFormElement(Datagrid $datagrid, $defaultValue = null);

    /**
     * Méthode renvoyant l'id du filtre.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterFormId(Datagrid $datagrid)
    {
        return $datagrid->id.'_'.$this->id.'_filterForm';
    }

    /**
     * Méthode renvoyant le label du filtre.
     *
     * @return string
     */
    public function getFilterFormLabel()
    {
        if ($this->labelFilter !== null) {
            return $this->labelFilter;
        } else {
            return $this->label;
        }
    }

    /**
     * Méthode renvoyant le suffix de reset du champs du filtre.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    protected function getResetFieldFilterFormSuffix(Datagrid $datagrid)
    {
        $resetField = '';

        $resetField .= '<i ';
        $resetField .= 'class="icon-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetField .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');"';
        $resetField .= '>';
        $resetField .= '</i>';

        return $resetField;
    }

    /**
     * Méthode renvoyant la valeur du champs du filtre de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        $filterValue = '';

        // Condition de saisie du filtre.
        $filterValue .= "if ($('#".$this->getFilterFormId($datagrid)."').val() != '') {";

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
        $filterValue .= '\"'.$this->criteriaFilterOperator.'\":\"" + $(\'#'.$this->getFilterFormId($datagrid).'\').val() + "\"';
        $filterValue .= '},";';

        $filterValue .= '}';

        return $filterValue;
    }

    /**
     * Méthode renvoyant la réinitialisation des champs du filtre de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        return "$('#".$this->getFilterFormId($datagrid)."').val('');";
    }

    /**
     * Méthode renvoyant le champs du formulaire d'ajout de la colonne.
     *
     * @param Datagrid $datagrid
     *
     * @return UI_Form_ZendElement
     */
    abstract public function getAddFormElement(Datagrid $datagrid);

    /**
     * Méthode renvoyant l'id du formulaire d'ajout.
     *
     * @param Datagrid $datagrid
     *
     * @return string
     */
    public function getAddFormElementId(Datagrid $datagrid)
    {
        return $datagrid->id.'_'.$this->id.'_addForm';
    }

    /**
     * Méthode renvoyant le label du formulaire d'ajout.
     *
     * @return string
     */
    public function getAddFormElementLabel()
    {
        if ($this->labelAdd !== null) {
            return $this->labelAdd;
        } else {
            return $this->label;
        }
    }
}
