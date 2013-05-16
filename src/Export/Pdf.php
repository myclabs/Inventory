<?php
/**
 * Fichier de la classe Pdf.
 *
 * @author     valentin.claras
 * @author     damien.corticchiato
 *
 * @package     Export
 */

/**
 * Description of Pdf.
 *
 * Une classe permettant de générer un pdf à partir d'une chaîne html.
 *
 * @package     Export
 */
class Export_Pdf
{
    /**
     * Chaîne html contenant le texte que l'on souhaite générer en pdf.
     *
     * @var   string
     */
    public $html = null;

    /**
     * Nom du fichier généré à la sortie.
     *
     * @var   string
     */
    public $fileName = null;

    /**
     * Orientation du pdf.
     *
     * L'orientation peut être 'portrait' (défaut) ou 'landscape'
     *
     * @var   string
     */
    public $orientation = 'portrait';


    /**
     * Constructeur de la classe Pdf.
     *
     * @param string $fileName Nom du fichier généré.
     *
     */
    public function  __construct($fileName=null)
    {
        $this->fileName = $fileName;
    }

    /**
     * Génère l'objet DOMPDF.
     *
     * @return DOMPDF
     */
    protected function getDomPdfObject()
    {
        // Vérifie que la source du fichier pdf a bien été spécifié.
        if ($this->html === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un texte pour générer un document pdf'
            );
        }
        // Vérifie que le nom du fichier a bien été spécifié.
        if ($this->fileName === null) {
            // Erreur Système car empèche le bon fonctionnement.
            throw new Core_Exception_InvalidArgument(
                'Il faut spécifier un nom de fichier pour le document pdf à générer'
            );
        }

        // Inclusion du ficher source de la classe pdf.
        define("DOMPDF_TEMP_DIR", PACKAGE_PATH . '/data/temp');
        define('DOMPDF_ENABLE_AUTOLOAD', false);
        require_once(PACKAGE_PATH . '/vendor/dompdf/dompdf/dompdf_config.inc.php');

        $dompdf = new DOMPDF();
        $dompdf->load_html($this->html);
        $dompdf->render();

        return $dompdf;
    }

    /**
     * Renvoie l'objet dompdf.
     *
     * @return DOMPDF
     */
    public function render()
    {
        return $this->getDomPdfObject();
    }

    /**
     * Stream le fichier pdf.
     *
     * @return string
     */
    public function display()
    {
        $dompdf = $this->getDomPdfObject();
        // Affichage, proposition de télécharger sous le nom donné.
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);
        $dompdf->stream($this->fileName.'.pdf');
    }

}
