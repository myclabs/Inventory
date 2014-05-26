<?php

use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Member;
use Core\Annotation\Secure;

class Classification_Datagrid_Translate_MembersController extends UI_Controller_Datagrid
{
    /**
     * @Inject("translation.languages")
     * @var string[]
     */
    private $languages;

    /**
     * @Secure("editClassificationLibrary")
     */
    public function getelementsAction()
    {
        $library = ClassificationLibrary::load($this->getParam('library'));

        $count = 0;
        foreach ($library->getAxes() as $axis) {
            foreach ($axis->getMembers() as $member) {
                $data = array();
                $data['index'] = $member->getId();
                $data['identifier'] = $member->getAxis()->getRef().' | '.$member->getRef();

                foreach ($this->languages as $language) {
                    $data[$language] = $member->getLabel()->get($language);
                }
                $this->addline($data);
                $count++;
            }
        }

        $this->totalElements = $count;

        $this->send();
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function updateelementAction()
    {
        $member = Member::load($this->update['index']);
        $member->getLabel()->set($this->update['value'], $this->update['column']);

        $this->data = $member->getLabel()->get($this->update['column']);
        $this->send(true);
    }
}
