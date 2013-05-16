<?php
/**
 * Fichier de la classe ChartSerie.
 *
 * @author     valentin.claras
 * @package    UI
 * @subpackage Chart
 */

/**
 * Description of ChartSerie.
 *
 * Une classe représentant une série d'un graphique.
 * @package    UI
 * @subpackage Chart
 *
 * @see   http://imagecharteditor.appspot.com/
 */
class UI_Chart_Serie
{
    /**
     * Nom de la série.
     *
     * @var   string
     */
    public $name = '';

    /**
     * Ensemble des valeurs de la série.
     *
     * @var   array
     */
    public $values = array();

    /**
     * Ensemble des incertitudes de la série.
     *
     * @var   array
     */
    public $uncertainties = array();

    /**
     * Couleur de la série sur le graphique (hexadécimal).
     *
     * @var   string
     */
    public $color = null;

    /**
     * Type de la série sur le graphique (string ou number).
     *
     * @var   string
     */
    public $type = 'number';

    /**
     * Constructeur de la classe ChartSerie.
     *
     * @param string $name     Nom de la série.
     * @param string $color Couleur de la série.
     *
     */
    public function  __construct($name=null, $color=null)
    {
        if ($name !== null) {
            $this->name = $name;
        }
        if ($color !== null) {
            $this->color = $color;
        }

    }

}
