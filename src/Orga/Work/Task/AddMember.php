<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Représente l'appel d'une méthode d'un service
 *
 * @package Core
 */
class Orga_Work_Task_AddMember extends Core_Work_Task
{

    /**
     * @var string
     */
    private $idAxis;

    /**
     * @var string
     */
    private $ref;

    /**
     * @var string
     */
    private $label;

    /**
     * @var array
     */
    private $listBroaderMembers = array();

    /**
     * @param Orga_Model_Axis $axis
     * @param string $ref
     * @param string $label
     * @param Orga_Model_Member[] $broaderMembers
     */
    public function __construct($axis, $ref, $label, $broaderMembers)
    {
        $this->idAxis = $axis->getId();
        $this->ref = $ref;
        $this->label = $label;
        foreach ($broaderMembers as $broaderMember) {
            $this->listBroaderMembers[] = $broaderMember->getId();
        }
    }

    /**
     * Execute
     */
    public function execute()
    {
        $member = new Orga_Model_Member(Orga_Model_Axis::load(array('id' => $this->idAxis)));
        $member->setRef($this->ref);
        $member->setLabel($this->label);
        foreach ($this->listBroaderMembers as $idBroaderMember) {
            $member->addDirectParent(Orga_Model_Member::load(array('id' => $idBroaderMember)));
        }
        $member->save();

        $entityManagers = Zend_Registry::get('EntityManagers');
        $entityManagers['default']->flush();
    }

}
