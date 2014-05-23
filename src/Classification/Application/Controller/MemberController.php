<?php
/**
 * Classe Classification_MemberController
 * @author valentin.claras
 * @package    Classification
 * @subpackage Controller
 */

use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Core\Annotation\Secure;


/**
 * Classe du controller gérant les Member.
 * @package    Classification
 * @subpackage Controller
 */
class Classification_MemberController extends Core_Controller
{
    /**
     * Action appelé à l'affichage des Member.
     *
     * @Secure("editClassificationLibrary")
     */
    public function listAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('library'));

        $this->view->assign('library', $library);
        $this->view->assign('listAxes', $library->getAxesOrderedAsAscendantTree());
        $this->view->headScript()->appendFile('scripts/ui/refRefactor.js', 'text/javascript');
    }
}
