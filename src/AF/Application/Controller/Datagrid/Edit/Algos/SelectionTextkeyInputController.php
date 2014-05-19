<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  thibaud.rolland
 * @package AF
 */

use AF\Domain\AF;
use AF\Domain\Component\Select\SelectSingle;
use AF\Domain\Algorithm\Selection\TextKey\InputSelectionAlgo;
use Core\Annotation\Secure;

/**
 * @package AF
 */
class AF_Datagrid_Edit_Algos_SelectionTextkeyInputController extends UI_Controller_Datagrid
{

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     * @Secure("editAF")
     */
    public function getelementsAction()
    {
        /** @var $af AF */
        $af = AF::load($this->getParam('id'));
        $algos = $af->getAlgos();
        foreach ($algos as $algo) {
            if ($algo instanceof InputSelectionAlgo) {
                $data = [];
                $data['index'] = $algo->getId();
                $data['ref'] = $algo->getRef();
                try {
                    $component = SelectSingle::loadByRef($algo->getInputRef(), $af);
                    $data['input'] = $this->cellTranslatedText($component->getLabel());
                } catch (Core_Exception_NotFound $e) {
                    $data['input'] = null;
                }
                $this->addLine($data);
            }
        }
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     */
    public function addelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     */
    public function updateelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery("Invalid action");
    }

}
