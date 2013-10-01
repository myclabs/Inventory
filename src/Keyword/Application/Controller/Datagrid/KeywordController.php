<?php
/**
 * Classe Keyword_Datagrid_KeywordController
 * @author valentin.claras
 * @author bertrand.ferry
 * @package Keyword
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use UI\Datagrid\DatagridController;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;

/**
 * Classe controleur de la datagrid de Keyword.
 * @package Keyword
 */
class Keyword_Datagrid_KeywordController extends DatagridController
{
    /**
     * @Inject
     * @var KeywordRepository
     */
    private $keywordRepository;

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::getelementsAction()
     *
     * @Secure("viewKeyword")
     */
    public function getelementsAction()
    {
        $paginator = $this->keywordRepository->matching($this->criteria);
        /** @var Keyword $keyword */
        foreach ($paginator as $keyword) {
            $data = array();

            $data['index'] = $keyword->getRef();
            $data['label'] = $this->cellText($keyword->getLabel());
            $data['ref'] = $this->cellText($keyword->getRef());
            $data['nbRelations'] = $this->cellNumber($keyword->countAssociations());
            $data['linkToGraph'] = $this->cellLink('keyword/graph/consult?ref=' . $keyword->getRef());

            $this->addLine($data);
        }

        $this->totalElements = count($paginator);
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::addelementAction()
     *
     * @Secure("editKeyword")
     */
    public function addelementAction()
    {
        $ref = $this->getAddElementValue('ref');
        $label = $this->getAddElementValue('label');

        $refErrors = $this->keywordRepository->getErrorMessageForRef($ref);
        if ($refErrors != null) {
            $this->setAddElementErrorMessage('ref', $refErrors);
        }

        if (empty($this->_addErrorMessages)) {
            $this->keywordRepository->add(new Keyword($ref, $label));
            $this->entityManager->flush();
            $this->message = __('UI', 'message', 'added');
        }

        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::deleteelementAction()
     *
     * @Secure("deleteKeyword")
     */
    public function deleteelementAction()
    {
        $this->keywordRepository->remove($this->keywordRepository->getByRef($this->delete));
        $this->entityManager->flush();
        $this->message = __('UI', 'message', 'deleted');
        $this->send();
    }

    /**
     * (non-PHPdoc)
     * @see UI_Controller_Datagrid::updateelementAction()
     *
     * @Secure("editKeyword")
     */
    public function updateelementAction()
    {
        $keyword = $this->keywordRepository->getByRef($this->update['index']);
        $newValue = $this->update['value'];

        switch ($this->update['column']) {
            case 'label':
                $keyword->setLabel($newValue);
                break;
            case 'ref':
                $this->keywordRepository->checkRef($newValue);
                $keyword->setRef($newValue);
                break;
            default:
        }
        $this->entityManager->flush();
        $this->data = $newValue;
        $this->message = __('UI', 'message', 'updated');

        $this->send();
    }

}
