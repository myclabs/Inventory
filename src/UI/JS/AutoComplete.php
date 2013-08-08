<?php
/**
 * Fichier de la classe AutoComplete.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage JS
 */

/**
 * Description of AutoComplete.
 *
 * Une classe permettant de générer l'auto-complétion sur un input très rapidement.
 *
 * @package    UI
 * @subpackage JS
 */
class UI_JS_AutoComplete extends UI_Generic
{
    /**
     * Définit si le html sera généré. Utile pour simplement appliquer à un champs existant.
     *
     * Par défaut true.
     *
     * @var bool
     */
    public $generateHTML = true;

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
     * Définit le nombre minimal de caractères à saisir avant d'afficher un résultat.
     *
     * Par défaut 0.
     *
     * @var int
     */
    public $minimumInputLength = 0;

    /**
     * Booléen définissant si la valeur peut être multiple.
     *
     * Par défaut false.
     *
     * @var   bool
     */
    public $multiple = false;

    /**
     * Identifiant unique de l'input ciblé.
     *
     * @var string
     */
    public $id = null;

    /**
     * Source de la liste de l'auto-complétion.
     *  Aussi bien une url qu'un tableau de chaines.
     *
     * @var string
     */
    public $source = null;

    /**
     * Tableau d'attributs optionnels.
     *
     * Permet de stocker d'autres attributs via la méthode addAttribute.
     * (name, class, onclick, ...)
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_attributes = array();

    /**
     * Tableau d'options optionnels.
     *
     * Permet de stocker d'autres options via la méthode addOptions.
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_options = array();


    /**
     * Constructeur de la classe AutoComplete.
     *
     * L'input lié ne sera pas crée via cette classe.
     *
     * @param string $id     Identifiant de l'input lié..
     * @param string $source Source des données de l'auto-complétion. Une url ou un tableau de chaînes.
     */
    public function __construct($id=null, $source=null)
    {
        $this->id     = $id;
        $this->source = $source;
    }

    /**
     * Ajoute les fichiers CSS et Javascript à la page.
     *
     * @param UI_JS_AutoComplete $instance Permet de spécifier les headers requis en fonction de l'instance passée.
     */
    static function addHeader($instance=null)
    {
        /* @var $broker Zend_Controller_Action_Helper_ViewRenderer */
        $broker = Zend_Controller_Action_HelperBroker::getStaticHelper('ViewRenderer');

        // Ajout des feuilles de style.
        $broker->view->headLink()->appendStylesheet('select2/select2.css');
        // Ajout des fichiers Javascript.
        $broker->view->headScript()->appendFile('select2/select2.js', 'text/javascript');

        parent::addHeader($instance);
    }

    /**
     * Fonction permettant d'ajouter des attributs html à l'autocomplete.
     *
     * @param string $attributeName  Nom de l'attribut à ajouter.
     * @param string $attributeValue Valeur de l'attribut à ajouter.
     *
     * @return void
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        switch ($attributeName) {
            case 'id':
                $this->id = $attributeValue;
                break;
            case 'href':
                $this->source = $attributeValue;
                break;
            case 'multiple':
                $this->multiple = $attributeValue;
                break;
            case 'class':
                $this->_attributes[$attributeName] = $this->_attributes[$attributeName].' '.$attributeValue;
                break;
            default:
                $this->_attributes[$attributeName] = $attributeValue;
                break;
        }
    }

    /**
     * Fonction permettant d'ajouter des options de configuration javascript à l'autocomplete.
     *
     * @param string $optionName  Nom de l'option à ajouter.
     * @param string $optionValue Valeur de l'option à ajouter.
     *
     * @return void
     */
    public function addOption($optionName, $optionValue)
    {
        $this->_options[$optionName] = $optionValue;
    }

    /**
     * Renvoi le javascripts de l'interface.
     *
     * @return string
     */
    public function getScript()
    {
        $script = '';

        $script .= '$(\'#'.$this->id.'\').select2('.$this->getOptions().');';

        return $script;
    }

    /**
     * Renvoi les options du select sous forme json.
     *
     * @return string
     */
    protected function getOptions()
    {
        if (($this->withEmptyElement === true) && !(isset($this->_options['allowClear']))) {
            $this->addOption('allowClear', true);
        }
        if (($this->minimumInputLength > 0) && !(isset($this->_options['minimumInputLength']))) {
            $this->addOption('minimumInputLength', $this->minimumInputLength);
        }
        if (($this->multiple === true) && !(isset($this->_options['multiple']))) {
            if (!is_array($this->source)) {
                $this->addOption('multiple', true);
            } else {
                $this->_attributes['multiple'] = 'multiple';
            }
        }
        if ((!is_array($this->source)) && !(isset($this->_options['ajax']))) {
            $ajax = '{';
            $ajax .= 'url: "'.(string) $this->source.'",';
            $ajax .= 'dataType: "json",';
            $ajax .= 'quietMillis: 100,';
            $ajax .= 'data: function(term, page) { return {q: term} },';
            $ajax .= 'results: function(data, page) { return {results: data} },';
            $ajax .= '}';
            $this->addOption('ajax', $ajax);
        }

        $options = '{';

        foreach ($this->_options as $optionName => $optionValue) {
            $options .= $optionName.': ';
            if ($optionValue === true) {
                $options .= 'true';
            } else if ($optionValue === false) {
                $options .= 'false';
            } else {
                $options .= $optionValue;
            }
            $options .= ',';
        }

        if ($options === '{') {
            return '';
        }
        return substr($options, null, -1).'}';
    }

    /**
     * Renvoi l'HTML de l'interface.
     *
     * @return string
     */
    public function getHTML()
    {
        if ($this->generateHTML === false) {
            return '';
        }

        if (!(isset($this->_attributes['name']))) {
            $this->addAttribute('name', $this->id);
        }
        if (($this->multiple === true) && !(isset($this->_attributes['multiple']))) {
            if (!is_array($this->source)) {
                $this->addOption('multiple', true);
            } else {
                $this->_attributes['multiple'] = 'multiple';
            }
        }

        $html = '';

        if (is_array($this->source)) {
            $html .= '<select id="'.$this->id.'"';
            foreach ($this->_attributes as $attributeName => $attributeValue) {
                $html .= ' '.$attributeName.'="'.$attributeValue.'"';
            }
            $html .= '>';
            if ($this->withEmptyElement === true) {
                $html .= '<option value=""></option>';
            }
            foreach ($this->source as $idElement => $element) {
                $html .= '<option value="'.$idElement.'">'.$element.'</option>';
            }
            $html .= '</select>';
        } else {
            $html .= '<input id="'.$this->id.'" type="text"';
            foreach ($this->_attributes as $attributeName => $attributeValue) {
                $html .= ' '.$attributeName.'="'.$attributeValue.'"';
            }
            $html .= '>';
        }

        return $html;
    }

}
