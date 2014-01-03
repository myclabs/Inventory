<?php
/**
 * Fichier de la classe Colonne.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne.
 *
 * Une classe mère (abstraite) permettant de générer une colonne.
 *
 * @deprecated
 *
 * @package    UI
 * @subpackage Datagrid
 */
abstract class UI_Datagrid_Col_Generic
{
    /**
     * Constante definissant un type de colonne.
     * ici une colonne de booléens.
     *
     * @see UI_Datagrid_Col_Bool
     */
    const TYPE_COL_BOOL = 'UI_Datagrid_Col_Bool';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de dates.
     *
     * @see UI_Datagrid_Col_Date
     */
    const TYPE_COL_DATE = 'UI_Datagrid_Col_Date';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de liens.
     *
     * @see UI_Datagrid_Col_Link
     */
    const TYPE_COL_LINK = 'UI_Datagrid_Col_Link';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de listes.
     *
     * @see UI_Datagrid_Col_List
     */
    const TYPE_COL_LIST = 'UI_Datagrid_Col_List';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de texte.
     *
     * @see UI_Datagrid_Col_LongText
     */
    const TYPE_COL_LONGTEXT = 'UI_Datagrid_Col_LongText';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de chiffres.
     *
     * @see UI_Datagrid_Col_Number
     */
    const TYPE_COL_NUMBER = 'UI_Datagrid_Col_Number';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de gestion d'ordre.
     *
     * @see UI_Datagrid_Col_Order
     */
    const TYPE_COL_ORDER = 'UI_Datagrid_Col_Order';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de pourcentage.
     *
     * @see UI_Datagrid_Col_Percent
     */
    const TYPE_COL_PERCENT = 'UI_Datagrid_Col_Percent';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de popups.
     *
     * @see UI_Datagrid_Col_Popup
     */
    const TYPE_COL_POPUP = 'UI_Datagrid_Col_Popup';

    /**
     * Constante definissant un type de colonne.
     * ici une colonne de texte.
     *
     * @see UI_Datagrid_Col_Text
     */
    const TYPE_COL_TEXT = 'UI_Datagrid_Col_Text';

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
     * @var   string
     */
    public $valueAlignment = null;

    /**
     * Définition de la constante utilisé pour le filtre sur la colonne.
     *
     * @var   string
     */
    public $filterOperator = null;

    /**
     * Pseudo constante redéfinissable
     *
     * Définition du label d'édition affiché dans la cellule.
     *
     * @var string
     */
    public $editableLabel = null;

    /**
     * Pseudo constante redéfinissable
     *
     * Définition du label du bouton de sauvegarde de l'éditeur.
     *
     * @var string
     */
    public $editLabelSave = null;

    /**
     * Pseudo constante redéfinissable
     *
     * Définition du label du bouton d'annulation de l'éditeur.
     *
     * @var string
     */
    public $editLabelCancel = null;

    /**
     * Identifiant unique de la colonne.
     *
     * Cet identifiant permet d'identifier de manière unique chaque colonne.
     * Il est utilisé pour récupérer les données de la colonne.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Nom du tri de la colonne.
     *
     * Ce nom sera passé directement à l'objet requete par le controleur Datagrid.
     * Par defaut vaut null et empèche le tri.
     *
     * @var   string
     */
    public $sortName = null;

    /**
     * Nom du filtre de la colonne.
     *
     * Ce nom sera passé directement à l'objet requete par le controleur Datagrid.
     * Par defaut vaut null et empèche le filtrage.
     *
     * @var   string
     */
    public $filterName = null;

    /**
     * Alias métier qui sera  de la colonne.
     *
     * L'alias sera passé directement à l'objet requete par le controleur Datagrid.
     * Par defaut vaut null, l'alias sera donc celui utilisé par défaut lors de la rquête.
     *
     * @var   string
     */
    public $entityAlias = null;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé comme titre de la colonne.
     *  si il reste nul, la colonne n'aura pas de titre.
     *
     * @var   string
     */
    public $label = null;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé en priorité comme titre pour l'ajout, sinon, le label de la colonne est utilisé.
     *
     * @var   string
     */
    public $labelAdd = null;

    /**
     * Label de la colonne.
     *
     * Ce texte est utilisé en priorité comme titre pour le filtre, sinon, le label de la colonne est utilisé.
     *
     * @var   string
     */
    public $labelFilter = null;

    /**
     * Permet de savoir si la colonne est présente dans la formulaire d'ajout par défaut.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $addable = true;

    /**
     * Valeur par défaut dans le popup d'ajout.
     *
     * @var   string
     */
    public $defaultAddValue = null;

    /**
     * Permet de savoir si la colonne est modifiable dans la ligne.
     *
     * Par défaut non.
     *
     * @var   bool
     */
    public $editable = false;

    /**
     * Permet de savoir si le label d'édition sera affiché dans la cellule si elle est éditable.
     *
     * Par défaut oui.
     *
     * @var   bool
     */
    public $displayLabelEditable = true;

    /**
     * Type de colonne.
     *
     * Permet de connaître le type de la colonne depuis la classe mère.
     *
     * @var   const
     *
     * @see   TYPE_COL_BOOLEEN
     * @see   TYPE_COL_DATE
     * @see   TYPE_COL_LIEN
     * @see   TYPE_COL_LISTE
     * @see   TYPE_COL_NOMBRE
     * @see   TYPE_COL_POPUP
     * @see   TYPE_COL_TEXTE
     */
    protected $_type = null;


    /**
      * Constructeur de la colonne.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
     */
    public function __construct($id=null, $label=null)
    {
        $this->id = $id;
        $this->label = $label;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->editableLabel = '<i class="fa fa-pencil-square-o"></i>';
        $this->editLabelSave = __('UI', 'verb', 'save');
        $this->editLabelCancel = __('UI', 'verb', 'cancel');
    }

    /**
     * Fonction permettant de connaître le type de la colonne.
     *
     * @return const
     *
     * @see   TYPE_COL_BOOLEEN
     * @see   TYPE_COL_DATE
     * @see   TYPE_COL_LIEN
     * @see   TYPE_COL_LISTE
     * @see   TYPE_COL_NOMBRE
     * @see   TYPE_COL_POPUP
     * @see   TYPE_COL_TEXTE
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Méthode renvoyant le formatter de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string chaîne javascript du formatter de la colonne YUI.
     */
    public function getFormattingFunction($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    abstract protected function getFormatter($datagrid);

    /**
     * Méthode renvoyant d'éventuelles fonctions complémentaires nécéssaires à la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getComplementaryFunction($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getDefinition($datagrid)
    {
        $definition = '';

        $definition .= '{';
        $definition .= 'key:"'.$this->id.'", ';
        $definition .= 'label:"<span>'.$this->label.'</span>", ';
        $definition .= 'formatter:"format'.$datagrid->id.$this->id.'"';
        if ($this->sortName !== null) {
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getSortOption($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFullSortName($datagrid)
    {
        return $this->entityAlias.'.'.$this->sortName;
    }

    /**
     * Méthode renvoyant le nom complet de filtre.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFullFilterName($datagrid)
    {
        return $this->entityAlias.'.'.$this->filterName;
    }

    /**
     * Méthode renvoyant les options d'édition de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getEditableOption($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getEditor($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    abstract protected function getEditorValue($datagrid);

    /**
     * Méthode renvoyant le champs du filtre de la colonne.
     *
     * @param UI_Datagrid $datagrid
     * @param array $defaultValue Valeur par défaut du filtre (=null).
     *
     * @return Zend_Form_Element
     */
    abstract public function getFilterFormElement($datagrid, $defaultValue=null);

    /**
     * Méthode renvoyant l'id du filtre.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getFilterFormId($datagrid)
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
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    protected function getResetFieldFilterFormSuffix($datagrid)
    {
        $resetField = '';

        $resetField .= '<i ';
        $resetField .= 'class="fa fa-'.$datagrid->filterIconResetFieldSuffix.' reset" ';
        $resetField .= 'onclick="$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');"';
        $resetField .= '>';
        $resetField .= '</i>';

        return $resetField;
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

        // Condition de saisie du filtre.
        $filterValue .= 'if ($(\'#'.$this->getFilterFormId($datagrid).'\').val() != \'\') {';

        // Ajout au filtre.
        $filterValue .= 'filter += "\"'.$this->getFullFilterName($datagrid).'\": {';
        $filterValue .= '\"'.$this->filterOperator.'\":\"" + $(\'#'.$this->getFilterFormId($datagrid).'\').val() + "\"';
        $filterValue .= '},";';

        $filterValue .= '}';

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

        $resetFields .= '$(\'#'.$this->getFilterFormId($datagrid).'\').val(\'\');';

        return $resetFields;
    }

    /**
     * Méthode renvoyant le champs du formulaire d'ajout de la colonne.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return Zend_Form_Element
     */
    abstract public function getAddFormElement($datagrid);

    /**
     * Méthode renvoyant l'id du formulaire d'ajout.
     *
     * @param UI_Datagrid $datagrid
     *
     * @return string
     */
    public function getAddFormElementId($datagrid)
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