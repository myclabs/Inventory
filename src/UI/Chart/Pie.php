<?php
/**
 * Fichier de la classe ChartPie.
 *  @author    valentin.claras
 *  @author    cyril.perraud
 * @package    UI
 * @subpackage Chart
 *
 */

/**
 * Description of ChartBar.
 *
 * Une classe permettant de générer un graph en camembert très simplement.
 *  Ce graphique utilise l'api google.
 *
 * @package UI
 * @subpackage Chart
 * @see   https://developers.google.com/chart/?hl=fr-FR
 */
class UI_Chart_Pie extends UI_Chart_Generic
{
    /**
     * Constructeur de la classe BarChart.
     *
     * @param string $id Identifiant unique de la datagrid.
     */
    public function  __construct($id)
    {
        $this->addAttribute('pieResidueSliceLabel', '"'.__('UI', 'chart', 'pieResidueSliceLabel').'"');

        parent::__construct($id);
    }

    /**
     * Génère le code HTML.
     * @param bool $display Affiche:true ou renvoie:false le texte html.
     *  (par defaut on affiche).
     *
     * @return mixed(void|string) chaîne html de la datagrid.
     */
    public function getScript()
    {
        $this->addAttribute('width', $this->width);
        $this->addAttribute('height', $this->height);
        $this->addAttribute('title', addslashes($this->name));
        if (!$this->displaySeriesLabels) {
            $this->addAttribute('legend', "'none'");
        }

        $script = '';

        $script .= 'drawChart'.$this->id.' = function() {';

        $script .= 'var data = new google.visualization.DataTable();';
        $script .= 'data.addColumn(\'string\', \'Label\');';
        $script .= 'data.addColumn(\'number\', \'value\');';
        if (count($this->_series) > 0) {
            $seriesColors = array();
            $locale = Core_Locale::loadDefault();
            $dataScript = 'data.addRows([';
            foreach ($this->_series as $serie) {
                $value = is_array($serie->values) ? $serie->values[0] : $serie->values;
                $dataScript .= '[\''.addslashes($serie->name).'\', {v:'.$value.',f:\''.$locale->formatNumber($value, 3).'\'}],';
                if ($serie->color !== null) {
                    $seriesColors[] = $serie->color;
                }
            }
            $dataScript = substr($dataScript, 0, -1);
            $script .= $dataScript.']);';
            if (count($this->_series) === count($seriesColors)) {
                $this->addAttribute('colors', $seriesColors);
            }
        }

        $script .= $this->id.' = new google.visualization.PieChart(';
        $script .= 'document.getElementById(\''.$this->id.'\')';
        $script .= ');';
        $script .= $this->id.'.draw(data, '.$this->getOptions().');';
        $script .= $this->id.'_data = data;';

        $script .= '};';

        $script .= 'google.load("visualization", "1", {\'callback\':\'drawChart'.$this->id.'\', packages:["corechart"], \'language\': \'fr-FR\'});';

        return $script;
    }

}
