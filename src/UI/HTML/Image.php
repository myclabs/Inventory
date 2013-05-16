<?php
/**
 * Fichier de la classe Image.
 *
 * @author     valentin.claras
 *
 * @package    UI
 * @subpackage HTML
 */

/**
 * Description of image.
 *
 * @package    UI
 * @subpackage HTML
 */
class UI_HTML_Image extends UI_Generic
{
    /**
     * Identifiant unique de l'image.
     *
     * @var   string
     */
    public $id = null;

    /**
     * Source du fichier image.
     *
     * @var   string
     */
    public $source = null;

    /**
     * Alternatif à l'image.
     *
     * @var   string
     */
    public $alt = null;

    /**
     * Tableau d'attributs optionnels.
     *
     * Permet de stocker d'autres attributs via la méthode addAttribute
     * (name, class, ...)
     *
     * @var   array
     *
     * @see   addAttribute
     */
    protected $_attributes = array();


    /**
     * Constructeur de la classe Image.
     *
     * Si cette classe est utilisée dans un package, alors il faudra
     * spécifier le nom du package dans lequel on se trouve.
     *
     * @param string $source     Chemin d'accès au fichier image.
     * @param string $alt        Texte alternatif à l'image.
     */
    public function  __construct($source=null, $alt=null)
    {
        $this->source = $source;
        $this->alt    = $alt;
    }

    /**
     * Fonction permettant d'ajouter des attributs à la balise image.
     *
     * @param string $attributeName  Nom de l'attribut à ajouter.
     * @param string $attributeValue Valeur de l'attribut à ajouter.
     *
     * @return void
     */
    public function addAttribute($attributeName, $attributeValue)
    {
        // Vérification de l'attribut pour éviter l'écrasement / la duplication.
        switch ($attributeName) {
            case 'id':
                $this->id = $attributeValue;
                break;
            case 'src':
                $this->source = $attributeValue;
                break;
            case 'alt':
                $this->alt = $attributeValue;
                break;
            default:
                $this->_attributes[$attributeName] = $attributeValue;
                break;
        }
    }

    /**
     * Méthode affichant l'image.
     *
     * @return mixed (void|string) chaine html de l'image.
     */
    public function getHTML()
    {
        // Vérifie que la source de l'image a bien été spécifié.
        if ($this->source === null) {
            throw new Core_Exception_UndefinedAttribute('An Image need a source to be rendered.');
        }

        // Formattage des textes pour permettre l'affichage.
        if ($this->alt !== null) {
            $this->alt = preg_replace('#\r#', '', $this->alt);
            $this->alt = preg_replace('#\n#', '<br>', $this->alt);
        }

        $html = '';

        // Ouverture de la balise.
        $html .= '<img ';

        // Ajout de l'id.
        if ($this->id !== null) {
            $html .= 'id="'.$this->id.'" ';
        }

        $html .= 'src="'.$this->source.'" ';
        // Ajout de l'alternative.
        $html .= 'alt="'.$this->alt.'" ';

        // Ajout des attributs optionnels.
        foreach ($this->_attributes as $name => $value) {
            $html .= $name.'="'.$value.'" ';
        }

        // Fermeture de la balise.
        $html .= '>';

        return $html;
    }

}