<?php
/**
 * Fichier de la classe ChartImage.
 * @author     valentin.claras
 * @author     cyril.perraud
 * @package    UI
 * @subpackage Chart
 */

/**
 * Description of ChartImage.
 *
 * Une classe permettant de générer un graphique dynamic très simplement ce graphique utilise l'api google.
 * @package UI
 * @subpackage Chart
 * @see   https://developers.google.com/chart/?hl=fr-FR
 */
Abstract class UI_Chart_Generic extends UI_Generic
{
    /**
     * Largeur de l'image.
     *  Par défaut 800 pixels.
     *
     * @var   int
     */
    public $width = 800;

    /**
     * Hauteur de l'image.
     *  Par défaut 500 pixels.
     *
     * @var   int
     */
    public $height = 500;

    /**
     * Définit si l'on affiche le label des séries.
     *  Par défaut oui.
     *
     * @var   bool
     */
    public $displaySeriesLabels = true;


    /**
     * Identifiant unique du chart.
     *
     * @var string
     */
    public $id = null;

    /**
     * Nom du graphique.
     *
     * @var   string
     */
    public $name = null;

    /**
     * Tableau des séries.
     *
     * Permet de stocker les séries qui seront affichées sur le graphique.
     *
     * @var   UI_Chart_Serie[]
     *
     * @see   addSerie
     */
    protected $_series = array();

    /**
     * Tableau d'attributs optionnels.
     *
     * Permet de définir des attributs optionnels pour l'API google chart
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_attributes = array();

    /**
     *
     * Constructeur de la classe BarChart.
     *
     * @param string $id Identifiant unique de la datagrid.
     */
    public function __construct($id)
    {
        $this->id = $id;
    }

    /**
     * Fonction permettant d'ajouter des attributs au graphique.
     *
     * @param string $attributeName    Nom de l'attribut à ajouter.
     * @param string $attributeValue Valeur de l'attribut à ajouter.
     *
     * @return void
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        $this->_attributes[$attributeName] = $attributeValue;
    }

    /**
     * Fonction permettant d'ajouter une série au graphique.
     *
     * @param  UI_Chart_Serie $serie Série à ajouter.
     *
     * @return void
     */
    public function addSerie(UI_Chart_Serie $serie)
    {
        $this->_series[] = $serie;
    }

    /**
     * Renvoie les options formattées.
     *
     * @return string
     */
    protected function getOptions()
    {
        $options = '{';

        foreach ($this->_attributes as $nomAtt => $valAtt) {
            if (is_string($valAtt) && !(preg_match('#^{.*}$#', $valAtt))) {
                $options .= $nomAtt.':\''.$valAtt.'\',';
            } else if (is_array($valAtt)) {
                $options .= $nomAtt.':[\''.implode('\', \'', $valAtt).'\'],';
            } else {
                $options .= $nomAtt.':'.$valAtt.',';
            }
        }
        if (count($this->_attributes > 0)) {
            $options = substr($options, 0, -1);
        }

        return $options.'}';

        $script .= 'var '.$this->id.' = new google.visualization.PieChart(';
        $script .= 'document.getElementById(\''.$this->id.'\')';
        $script .= ');';
        $script .= 'chart.draw(data, {';
        $options = '';
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @return string
     */
    public function getHTML()
    {
        return '<div id="'.$this->id.'"></div>';
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_Datagrid $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    static function addHeader($instance=null)
    {
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');
        // Ajout des fichiers Javascript.
        $broker->view->headScript()->appendFile('https://www.google.com/jsapi', 'text/javascript');

        parent::addHeader($instance);
    }

}
