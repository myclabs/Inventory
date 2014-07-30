<?php

use Core\Annotation\Secure;
use Orga\Domain\Workspace;

class Orga_Datagrid_Translate_WorkspacesController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * Fonction renvoyant la liste des éléments peuplant la Datagrid.
     *
     * @Secure("editWorkspaces")
     */
    public function getelementsAction()
    {
        $workspaceId = $this->getParam('workspace');
        /** @var Workspace $workspace */
        $workspace = Workspace::load($workspaceId);

        $data = [];
        $data['index'] = $workspace->getId();
        $data['identifier'] = $workspace->getId();

        foreach ($this->languages as $language) {
            $data[$language] = $workspace->getLabel()->get($language);
        }

        $this->addline($data);

        $this->send();
    }

    /**
     * Fonction modifiant la valeur d'un élément.
     *
     * @Secure("editWorkspace")
     */
    public function updateelementAction()
    {
        $workspace = Workspace::load($this->update['index']);
        $workspace->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $workspace->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
