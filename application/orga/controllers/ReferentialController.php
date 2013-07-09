<?php
/**
 * Classe Orga_ReferentialController
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */

use Core\Annotation\Secure;


/**
 * @author valentin.claras
 * @package Orga
 * @subpackage Controller
 */
class Orga_ReferentialController extends Core_Controller
{
    use UI_Controller_Helper_Form;

    /**
     * Redirection sur la liste.
     * @Secure("loggedIn")
     */
    public function indexAction()
    {
        $this->forward('exports');
    }

    /**
     * Liste des exports.
     * @Secure("loggedIn")
     */
    public function exportsAction()
    {
        // Formats d'exports.
        $this->view->defaultFormat = 'xls';
        $this->view->formats = [
            'xls' => 'XLS',
//            'ods' => 'ODS',
        ];

        // Liste des exports.
        $this->view->exports = [];

        $this->view->exports['AF'] = [
            'label' => __('AF', 'name', 'accountingForms'),
            'versions' => [
                'latest' => __('AF', 'name', 'accountingForms')
            ]
        ];

        $this->view->exports['Classif'] = [
            'label' => __('Classif', 'classification', 'classification'),
            'versions' => [
                'latest' => __('Classif', 'classification', 'classification')
            ]
        ];

        $this->view->exports['Techno'] = [
            'label' => __('Techno', 'name', 'parameters'),
            'versions' => [
                'latest' => __('Techno', 'name', 'parameters')
            ]
        ];

        $this->view->exports['Keyword'] = [
            'label' => __('Keyword', 'menu', 'semanticResources'),
            'versions' => [
                'latest' => __('Keyword', 'menu', 'semanticResources')
            ]
        ];

        $this->view->exports['Unit'] = [
            'label' => __('Unit', 'name', 'units'),
        ];
    }

    /**
     * @Secure("loggedIn")
     */
    public function exportAction()
    {
        $filename = 'test.xls';

        $contentType = "Content-type: application/vnd.ms-excel";
//        $contentType = "Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        header($contentType);
        header('Content-Disposition:attachement;filename='.$filename);
        header('Cache-Control: max-age=0');

        // Affichage, proposition de télécharger sous le nom donné.
        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $classifService = new Classif_Service_Classif();
        $classifService->export('xls');
    }

}