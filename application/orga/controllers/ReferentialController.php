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
        /** @var User_Model_User $connectedUser */
        $connectedUser = $this->_helper->auth();

        // Formats d'exports.
        $this->view->defaultFormat = 'xlsx';
        $this->view->formats = [
            'xlsx' => __('Orga', 'exports', 'xlsx'),
            'xls' => __('Orga', 'exports', 'xls'),
        ];

        // Liste des exports.
        $this->view->exports = [];

//        // AF.
//        $this->view->exports['AF'] = [
//            'label' => __('AF', 'name', 'accountingForms'),
//            'versions' => [
//                'latest' => __('AF', 'name', 'accountingForms')
//            ]
//        ];

        // Classif.
        $this->view->exports['Classif'] = [
            'label' => __('Classif', 'classification', 'classification'),
            'versions' => [
                'latest' => __('Classif', 'classification', 'classification')
            ]
        ];

        // Techno.
        $this->view->exports['Techno'] = [
            'label' => __('Techno', 'name', 'parameters'),
            'versions' => [
                'latest' => __('Techno', 'name', 'parameters')
            ]
        ];

        // Keyword.
        $this->view->exports['Keyword'] = [
            'label' => __('Keyword', 'menu', 'semanticResources'),
            'versions' => [
                'latest' => __('Keyword', 'menu', 'semanticResources')
            ]
        ];

        // Unit.
        $this->view->exports['Unit'] = [
            'label' => __('Unit', 'name', 'units'),
        ];

        // Orga Structure
        $this->view->exports['Orga'] = [
            'label' => __('Orga', 'name', 'organization'),
            'versions' => []
        ];
        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        foreach (Orga_Model_Organization::loadList($aclQuery) as $organization) {
            $this->view->exports['Orga']['version'][$organization->getId()] = $organization->getLabel();
        }

        // Orga Inputs
        $this->view->exports['Inputs'] = [
            'label' => __('Orga', 'name', 'inputs'),
            'versions' => []
        ];
        $aclQuery = new Core_Model_Query();
        $aclQuery->aclFilter->enabled = true;
        $aclQuery->aclFilter->user = $connectedUser;
        $aclQuery->aclFilter->action = User_Model_Action_Default::VIEW();
        foreach (Orga_Model_Organization::loadList($aclQuery) as $organization) {
            $this->view->exports['Inputs']['version'][$organization->getId()] = $organization->getLabel();
        }

    }

    /**
     * @Secure("loggedIn")
     */
    public function exportAction()
    {
        $export = $this->getParam('export');
        $format = $this->getParam('format');
        if ($this->hasParam('version')) {
            $refVersion = $this->getParam('version');
        }
        $version = null;

        switch ($export) {
            case 'Classif':
                $exportService = new Classif_Service_Export();
                $streamFunction = 'stream';
                $baseFilename = 'Classif';
                break;
            case 'Keyword':
                $exportService = new Keyword_Service_Export();
                $streamFunction = 'stream';
                $baseFilename = 'Keyword';
                break;
            case 'Unit':
                $exportService = new \Unit\Application\Service\UnitExport();
                $streamFunction = 'stream';
                $baseFilename = 'Unit';
                break;
            case 'Orga':
            case 'Inputs':
                $exportService = new Orga_Service_Export();
                $version = Orga_Model_Organization::load($refVersion);
                if ($export === 'Inputs') {
                    $streamFunction = 'streamInputs';
                    $baseFilename = 'Inputs';
                } else {
                    $streamFunction = 'streamStructure';
                    $baseFilename = 'Orga';
                }
                break;
            default:
                UI_Message::addMessageStatic(__('Orga', 'export', 'notFound'), UI_Message::TYPE_ERROR);
                $this->redirect('orga/referential/exports');
                break;
        }

        $date = date(str_replace('&nbsp;', '', __('Orga', 'export', 'dateFormat')));
        //@todo A supprimer. Pour éviter les erreurs de génération en attendant la traduction.
        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $filename = $date.'_'.$baseFilename.'.'.$format;

        $contentType = "Content-type: application/vnd.ms-excel";
//        $contentType = "Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet";
        header($contentType);
        header('Content-Disposition:attachement;filename='.$filename);
        header('Cache-Control: max-age=0');

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        if ($version === null) {
            $exportService->$streamFunction($format);
        } else {
            $exportService->$streamFunction($format, $version);
        }
    }

}