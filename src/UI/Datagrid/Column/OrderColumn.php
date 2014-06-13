<?php

namespace UI\Datagrid\Column;

use Exception;
use UI\Datagrid\Datagrid;

/**
 * Une classe permettant de générer une colonne gérant l'ordre des éléments.
 *
 * @author valentin.claras
 */
class OrderColumn extends GenericColumn
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

    /**
     * Définition du message affiché lors du chargement de la liste.
     *
     * @var string
     */
    public $loadingText;

    /**
     * Label affiché sur le lien du goFirst.
     *
     * @var string
     */
    public $labelGoFirst;

    /**
     * Label affiché sur le lien du goFirst impossible.
     *
     * @var string
     */
    public $labelForbiddenGoFirst;

    /**
     * Label affiché sur le lien du goUp.
     *
     * @var string
     */
    public $labelGoUp;

    /**
     * Label affiché sur le lien du goUp impossible.
     *
     * @var string
     */
    public $labelForbiddenGoUp;

    /**
     * Label affiché sur le lien du goDown.
     *
     * @var string
     */
    public $labelGoDown;

    /**
     * Label affiché sur le lien du goDown impossible.
     *
     * @var string
     */
    public $labelForbiddenGoDown;

    /**
     * Label affiché sur le lien du goLast.
     *
     * @var string
     */
    public $labelGoLast;

    /**
     * Label affiché sur le lien du goLast impossible.
     *
     * @var string
     */
    public $labelForbiddenGoLast;

    /**
     * Label affiché dans le popup d'ajout pour mettre en premier.
     *
     * @var string
     */
    public $labelAddFirst;

    /**
     * Label affiché dans le popup d'ajout pour mettre en dernier.
     *
     * @var string
     */
    public $labelAddLast;

    /**
     * Label affiché dans le popup d'ajout pour mettre en premier.
     *
     * @var string
     */
    public $labelAddAfter;

    /**
     * Permet d'ajouter une colonne plaçant l'objet en premier.
     *
     * @var bool
     */
    public $useGoFirst = true;

    /**
     * Permet d'ajouter une colonne remontant l'objet d'une place.
     *
     * @var bool
     */
    public $useGoUp = true;

    /**
     * Permet d'ajouter une colonne descendant l'objet d'une place.
     *
     * @var bool
     */
    public $useGoDown = true;

    /**
     * Permet d'ajouter une colonne plaçant l'objet en dernier.
     *
     * @var bool
     */
    public $useGoLast = true;

    /**
     * Url de l'action où récupérer la liste des posiiton pour l'ajout et le filtre.
     *
     * @var string
     */
    public $listPosition;


    public function __construct($id = null, $label = null)
    {
        if ($label === null) {
            $label = __('UI', 'name', 'order');
        }
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_CENTER;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColOrderEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColOrderLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColOrderHigher');
        $this->criteriaFilterOperator = 'eq';
        $this->filterOperatorLower = 'lt';
        $this->filterOperatorHigher = 'gt';
        $this->loadingText = __('UI', 'loading', 'loading');
        $this->errorText = str_replace('\'', '\\\'', __('UI', 'loading', 'error'));
        $this->labelGoFirst = '<i class="fa fa-angle-double-up"></i>';
        $this->labelForbiddenGoFirst = '<i class="fa fa-angle-double-up"></i>';
        $this->labelGoUp = '<i class="fa fa-angle-up"></i>';
        $this->labelForbiddenGoUp = '<i class="fa fa-angle-up"></i>';
        $this->labelGoDown = '<i class="fa fa-angle-down"></i>';
        $this->labelForbiddenGoDown = '<i class="fa fa-angle-down"></i>';
        $this->labelGoLast = '<i class="fa fa-angle-double-down"></i>';
        $this->labelForbiddenGoLast = '<i class="fa fa-angle-double-down"></i>';
        $this->labelAddFirst = __('UI', 'other', 'first');
        $this->labelAddLast = __('UI', 'other', 'last');
        $this->labelAddAfter = __('UI', 'other', 'after');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'if (typeof(sData) != "object") {';
        $format .= 'content = sData;';
        $format .= '} else {';
        $format .= 'content = sData.value;';
        $format .= 'var canUp = sData.up;';
        $format .= 'var canDown = sData.down;';
        $format .= '}';


        if ($this->editable === true) {
            $functionUpdate = $datagrid->id.'.updateOrder'.$this->id;

            if ($this->useGoFirst === true || $this->useGoUp === true) {
                $format .= 'if (canUp) {';
            }
            if ($this->useGoUp === true) {
                $format .= 'content = \'';
                $format .= '<span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goUp\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '> ';
                $format .= $this->labelGoUp;
                $format .= '</span>';
                $format .= '\' + content;';
            }
            if ($this->useGoFirst === true) {
                $format .= 'content = \'';
                $format .= '<span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goFirst\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '> ';
                $format .= $this->labelGoFirst;
                $format .= '</span>';
                $format .= '\' + content;';
            }
            if ($this->useGoFirst === true || $this->useGoUp === true) {
                $format .= '} else {';
                $format .= 'content = \'';
                $format .= $this->labelForbiddenGoFirst;
                $format .= $this->labelForbiddenGoUp;
                $format .= '\' + content;';
                $format .= '}';
            }

            if ($this->useGoDown === true || $this->useGoLast === true) {
                $format .= 'if (canDown) {';
            }
            if ($this->useGoDown === true) {
                $format .= 'content += \'';
                $format .= ' <span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goDown\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '>';
                $format .= $this->labelGoDown;
                $format .= '</span>';
                $format .= '\';';
            }
            if ($this->useGoLast === true) {
                $format .= 'content += \'';
                $format .= ' <span ';
                $format .= 'onclick="'.$functionUpdate.'(\\\'goLast\\\',\\\'\' + oRecord._oData.index + \'\\\');"';
                $format .= '>';
                $format .= $this->labelGoLast;
                $format .= '</span>';
                $format .= '\';';
            }
            if ($this->useGoDown === true || $this->useGoLast === true) {
                $format .= '} else {';
                $format .= 'content += \'';
                $format .= $this->labelForbiddenGoDown;
                $format .= $this->labelForbiddenGoLast;
                $format .= '\';';
                $format .= '}';
            }
        }
        $format .= ';';

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function getComplementaryFunction(Datagrid $datagrid)
    {
        $complementaryFunction = '';

        // Ajout d'une fonction permettant l'update de la position.
        if (($this->useGoFirst === true)
            || ($this->useGoUp === true)
            || ($this->useGoDown === true)
            || ($this->useGoLast === true)) {
            $complementaryFunction .= 'this.updateOrder'.$this->id.' = function(modif, index) {';
            $complementaryFunction .= $datagrid->id.'.StartLoading();';

            $complementaryFunction .= 'var params = \'/index/\' + index + \'/column/'.$this->id.'/value/\' + modif;';
            $complementaryFunction .= '$.post(';
            $complementaryFunction .= '\''.$datagrid->getActionUrl('updateelement').'\', ';
            $complementaryFunction .= 'params, ';
            $complementaryFunction .= 'function(data) {';
            $complementaryFunction .= 'if (data.message != "") {';
            $complementaryFunction .= 'addMessage(data.message, "success");';
            $complementaryFunction .= '}';
            if ($datagrid->pagination === true) {
                $complementaryFunction .= 'var paginator = '.$datagrid->id.'.Datagrid.getState().pagination.paginator;';
                $complementaryFunction .= 'var currentPage = paginator.getStartIndex() / paginator.getRowsPerPage();';
                $complementaryFunction .= $datagrid->id.'.filter(currentPage + 1);';
            } else {
                $complementaryFunction .= $datagrid->id.'.filter();';
            }
            $complementaryFunction .= $datagrid->id.'.EndLoading();';
            $complementaryFunction .= '}).error(function(data) {';
            $complementaryFunction .= 'errorHandler(data);';
            $complementaryFunction .= $datagrid->id.'.EndLoading();';
            $complementaryFunction .= '});';
            $complementaryFunction .= '};';
        }

        return $complementaryFunction;
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
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValue(Datagrid $datagrid)
    {
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
    }

    /**
     * {@inheritdoc}
     */
    public function getResettingFilter(Datagrid $datagrid)
    {
        throw new Exception('Col Order needs to be refactored before being able to filter element.');
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        throw new Exception('Col Order needs to be refactored before being able to add element.');
    }
}
