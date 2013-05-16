<?php
/**
 * Fichier de la classe Colonne Percent.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage Datagrid
 */

/**
 * Description of colonne percent.
 *
 * Une classe permettant de générer une colonne contenant un pourcentage.
 *
 * @package    UI
 * @subpackage Datagrid
 */
class UI_Datagrid_Col_Percent extends UI_Datagrid_Col_Number
{
    /**
     * Définit l'élément placé avant l'affichage de la valeur.
     *  Par défaut null.
     *
     * @var int
     */
    public $patternDisplayPreValue = null;

    /**
     * Définit l'élément placé après l'affichage de la valeur.
     *  Par défaut %.
     *
     * @var int
     */
    public $patternDisplayPostValue = '%';


     /**
      * Constructeur de la classe Col_Percent.
      *
      * @param string $id    Identifiant unique de la colonne.
      * @param string $label Texte afiché en titre de la colone.
      */
    public function __construct($id=null, $label=null)
    {
        parent::__construct($id, $label);
        // Définition du type de la classe.
        $this->_type = self::TYPE_COL_PERCENT;
        // Définition des pseudo-constantes pouvant être redéfinies.
        $this->valueAlignment = self::DISPLAY_TEXT_LEFT;
        $this->keywordFilterEqual = __('UI', 'datagridFilter', 'ColPercentEqual');
        $this->keywordFilterLower = __('UI', 'datagridFilter', 'ColPercentLower');
        $this->keywordFilterHigher = __('UI', 'datagridFilter', 'ColPercentHigher');
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
     * Ajoute l'icone d'édition à la cellule.
     *
     * @return string
     */
    protected function addEditableFormatter()
    {
        // Pas d'édition possible sur des pourcentages.
        return '';
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
        // Pas d'édition possible sur des pourcentages.
        return '';
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
        // Pas d'édition possible sur des pourcentages.
        return '';
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
        // Pas d'ajout possible sur des pourcentages.
        return null;
    }

}