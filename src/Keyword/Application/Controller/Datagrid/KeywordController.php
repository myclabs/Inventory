<?php

use Core\Annotation\Secure;
use DI\Annotation\Inject;
use UI\Datagrid\DatagridController;
use Keyword\Domain\Keyword;
use Keyword\Domain\KeywordRepository;

/**
 * Datagrid de la liste des mots-clés.
 *
 * @author valentin.claras
 * @author bertrand.ferry
 */
class Keyword_Datagrid_KeywordController extends DatagridController
{
    /**
     * @Inject
     * @var KeywordRepository
     */
    private $keywordRepository;

    /**
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
