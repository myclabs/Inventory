<?php

namespace UI\Datagrid\Column;

use UI\Datagrid\Datagrid;

/**
 * Classe représentant une colonne contenant un pourcentage.
 *
 * @author valentin.claras
 */
class PercentColumn extends NumberColumn
{
    /**
     * Définit l'élément placé avant l'affichage de la valeur.
     *
     * @var int
     */
    public $patternDisplayPreValue;

    /**
     * Définit l'élément placé après l'affichage de la valeur. Par défaut %.
     *
     * @var string
     */
    public $patternDisplayPostValue = '%';


    public function __construct($id = null, $label = null)
    {
        parent::__construct($id, $label);
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColPercentEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColPercentLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColPercentHigher');
    }

    /**
     * {@inheritdoc}
     */
    public function getFormatter(Datagrid $datagrid)
    {
        $format = '';

        $format .= 'var color = \'info\';';
        $format .= 'var width = 0;';
        $format .= 'if (typeof(sData) != "object") {';
        $format .= 'var width = parseInt(sData);';
        $format .= '} else {';
        $format .= 'if (sData.content != null) {';
        $format .= 'color = sData.content;';
        $format .= '}';
        $format .= 'width = sData.value;';
        $format .= '}';
        $format .= 'content = \'<div class="progress progress-\' + color + \'">\';';
        $format .= 'content += \'<div class="bar" style="width: \' + width + \'%;">\';';
        if (($this->patternDisplayPreValue !== null) || ($this->patternDisplayPostValue !== null)) {
            $format .= 'content += ';
            if ($this->patternDisplayPreValue !== null) {
                $format .= '\''.$this->patternDisplayPreValue.'\' + ';
            }
            $format .= 'width';
            if ($this->patternDisplayPostValue !== null) {
                $format .= ' + \''.$this->patternDisplayPostValue.'\'';
            }
            $format .= ';';
        }
        $format .= 'content += \'</div>\';';
        $format .= 'content += \'</div>\';';

        return $format;
    }

    /**
     * {@inheritdoc}
     */
    protected function addEditableFormatter()
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    protected function getEditableOption(Datagrid $datagrid)
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getEditorValue(Datagrid $datagrid)
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function getAddFormElement(Datagrid $datagrid)
    {
        // Pas d'ajout possible sur des pourcentages.
        return null;
    }
}
