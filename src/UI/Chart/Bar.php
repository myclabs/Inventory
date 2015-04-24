<?php
/**
 * Fichier de la classe ChartBar.
 *  @author    valentin.claras
 *  @author    cyril.perraud
 * @package    UI
 * @subpackage Chart
 *
 */

/**
 * Description of ChartBar.
 *
 * Une classe permettant de générer un graph en bar très simplement.
 *  Ce graphique utilise l'api google.
 *
 * @package UI
 * @subpackage Chart
 * @see   https://developers.google.com/chart/?hl=fr-FR
 */
class UI_Chart_Bar extends UI_Chart_Generic
{
    /**
     * Angle du text
     *
     * Par défaut 50
     *
     * @var int
     */
    public $slantedTextAngle = 50;

    /**
     * Active ou non l'angle du text.
     *  Par défaut désactivé.
     *
     * @var boolean
     */
    public $slantedText = true;


    /**
     * Définit si il s'agit d'un graph vertical ou horizontal.
     *
     * @var bool
     */
    public $vertical = true;

    /**
     * Définit si les barres du graphiques sont empilées les unes sur les autres.
     *  Par défaut non (elles sont les unes à la suites des autres).
     *
     * @var   bool
     */
    public $stacked = false;

    /**
     * Définit si on affiche ou pas l'incetitude
     *  Par défaut non.
     *
     * @var bool
     */
    public $displayUncertainty = false;


    /**
     * Renvoi le javascript de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $this->addAttribute('width', $this->width);
        $this->addAttribute('height', $this->height);
        $this->addAttribute('title', addslashes($this->name));
        if ($this->stacked) {
            $this->addAttribute('isStacked', 'true');
        } else {
            $this->addAttribute('isStacked', 'false');
        }
        if (!$this->displaySeriesLabels) {
            $this->addAttribute('legend', 'none');
        }
        if ($this->vertical) {
            if (isset($this->_attributes['vAxis'])) {
                $this->_attributes['vAxis'] = substr($this->_attributes['vAxis'], 0, -1) . ', minValue: 0}';
            } else {
                $this->addAttribute('vAxis', '{minValue: 0}');
            }
        } else {
            if (isset($this->_attributes['hAxis'])) {
                $this->_attributes['hAxis'] = substr($this->_attributes['hAxis'], 0, -1) . ', minValue: 0}';
            } else {
                $this->addAttribute('hAxis', '{minValue: 0}');
            }
        }
        if ($this->slantedText) {
            if ($this->vertical) {
                if (isset($this->_attributes['hAxis'])) {
                    $this->_attributes['hAxis'] = substr($this->_attributes['hAxis'], 0, -1) . ', slantedText: true, slantedTextAngle: '.$this->slantedTextAngle.'}';
                } else {
                    $this->addAttribute('hAxis', '{slantedText: true, slantedTextAngle: '.$this->slantedTextAngle.'}');
                }
            }
        }

        $script = '';

        $script .= 'drawChart'.$this->id.' = function () {';

        $script .= 'var data = new google.visualization.DataTable();';

        if (count($this->_series) > 0) {
            $seriesColors = array();
            $locale = Core_Locale::loadDefault();
            $rowsScript = 'data.addRows([';
            $labelScript = '';
            $rows = [];
            foreach ($this->_series as $serie) {
                $script .= 'data.addColumn(\''.$serie->type.'\', \''.addslashes($serie->name).'\');';
                if (($this->displayUncertainty) && ($serie->uncertainties !== array())) {
                    $script .= 'data.addColumn({type:\'number\', role:\'interval\'});';
                    $script .= 'data.addColumn({type:\'number\', role:\'interval\'});';
                }
                $labelScript .= '\''.addslashes($serie->name).'\',';
                foreach ($serie->values as $index => $value) {
                    if (!isset($rows[$index])) {
                        $rows[$index] = '';
                    } else {
                        $rows[$index] .= ',';
                    }
                    if (is_string($value)) {
                        $rows[$index] .= '\''.addslashes($value).'\'';
                    } else {
                        $rows[$index] .= '{v:'.$value.',f:\''.$locale->formatNumber($value, 3).'\'}';
                        if (($this->displayUncertainty) && ($serie->uncertainties !== array())) {
                            if (isset($serie->uncertainties[$index])) {
                                $minUncertainty = $value - ($serie->uncertainties[$index]/100) * $value;
                                $maxUncertainty = $value + ($serie->uncertainties[$index]/100) * $value;
                            } else {
                                $minUncertainty = $value;
                                $maxUncertainty = $value;
                            }
                            $rows[$index] .= ',{v:'.$minUncertainty.',f:\''.$locale->formatNumber($minUncertainty,3).'\'}';
                            $rows[$index] .= ',{v:'.$maxUncertainty.',f:\''.$locale->formatNumber($maxUncertainty,3).'\'}';
                        }
                    }
                }
                $rowsScript .= '], ';
                if ($serie->color !== null) {
                    $seriesColors[] = $serie->color;
                }
            }
            foreach ($rows as $row) {
                $script .= 'data.addRow(['.$row.']);';
            }
            if (count($this->_series) === count($seriesColors)) {
                // Suppression de la première couleur, car la première série est l'axe.
                array_shift($seriesColors);
                $this->addAttribute('colors', $seriesColors);
            }
        }

        $script .= $this->id.' = new google.visualization.'.(($this->vertical) ? 'ColumnChart' : 'BarChart').'(';
        $script .= 'document.getElementById(\''.$this->id.'\')';
        $script .= ');';
        $script .= $this->id.'.draw(data, '.$this->getOptions().');';
        $script .= $this->id.'_data = data;';

        $script .= '};';

        $script .= 'google.load(\'visualization\', \'1\', {\'callback\':\'drawChart'.$this->id.'\', \'packages\':[\'corechart\'], \'language\': \'fr-FR\'});';

        return $script;
    }

}
