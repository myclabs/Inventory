<?php

use Account\Domain\Account;
use Core\Test\TestCase;

/**
 * Test Member class.
 */
class Orga_Test_MemberTest
{
    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->addTestSuite('Orga_Test_MemberAttributes');
        $suite->addTestSuite('Orga_Test_MemberTag');
        $suite->addTestSuite('Orga_Test_MemberHierarchy');
        $suite->addTestSuite('Orga_Test_MemberAdjacent');
        return $suite;
    }
}

class Orga_Test_MemberAttributes extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis1;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis11;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis12;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis2;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');
    }

    function testConstruct()
    {
        $member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->assertSame($this->axis2, $member2a->getAxis());

        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $this->assertSame($this->axis11, $member11a->getAxis());

        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $this->assertSame($this->axis11, $member11b->getAxis());

        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $this->assertSame($this->axis12, $member12a->getAxis());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage A member needs one parent for each broader axis of his own axis.
     */
    function testConstructWithMissingParentsEmpty()
    {
        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a');
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage A direct parent Member needs to comes from a broader axis.
     */
    function testConstructWithMissingParentsWrong()
    {
        $member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$member2a, $member11a]);
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage A direct parent from the same axis as already be given as parent.
     */
    function testConstructWithMissingParentsDuplicate()
    {
        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$member11a, $member11b, $member12a]);
    }

    function testConstructWithParents()
    {
        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$member11a, $member12a]);

        $this->assertSame($this->axis1, $member1a->getAxis());
        $this->assertSame([$member11a, $member12a], $member1a->getDirectParents()->toArray());
    }

    function testGetContextualizingParents()
    {
        $axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $axis1111 = new Orga_Model_Axis($this->organization, 'ref_1111', $axis111);

        $this->axis1->setContextualize(true);
        $this->axis11->setContextualize(true);
        $axis111->setContextualize(false);
        $axis1111->setContextualize(true);
        $this->axis12->setContextualize(true);
        $this->axis2->setContextualize(true);

        $member1111a = new Orga_Model_Member($axis1111, 'ref1111_a');

        $member111a = new Orga_Model_Member($axis111, 'ref111_a', [$member1111a]);
        $member111b = new Orga_Model_Member($axis111, 'ref111_b', [$member1111a]);

        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a', [$member111a]);
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b', [$member111b]);
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$member11a, $member12a]);
        $member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$member11b, $member12a]);
        $member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$member11a, $member12a]);

        $member2a = new Orga_Model_Member($this->axis2, 'ref2_a');

        $this->assertSame([$member11a, $member1111a, $member12a], $member1a->getContextualizingParents());
        $this->assertSame([$member11b, $member1111a, $member12a], $member1b->getContextualizingParents());
        $this->assertSame([$member11a, $member1111a, $member12a], $member1c->getContextualizingParents());
        $this->assertSame([$member1111a], $member11a->getContextualizingParents());
        $this->assertSame([$member1111a], $member11b->getContextualizingParents());
        $this->assertSame([$member1111a], $member111a->getContextualizingParents());
        $this->assertSame([$member1111a], $member111b->getContextualizingParents());
        $this->assertSame([], $member1111a->getContextualizingParents());
        $this->assertSame([], $member12a->getContextualizingParents());
        $this->assertSame([], $member2a->getContextualizingParents());
    }

    function testSetRef()
    {
        $member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->assertSame('ref2_a', $member2a->getRef());
    }

    /**
     * @expectedException Core_Exception_Duplicate
     * @expectedExceptionMessage A Member with ref "ref2_dup" already exists in this Axis
     */
    function testSetRefDuplicate()
    {
        $member2a = new Orga_Model_Member($this->axis2, 'ref2_dup');

        $member2b = new Orga_Model_Member($this->axis2, 'ref2_dup');
    }

    /**
     * @expectedException Core_Exception_Duplicate
     * @expectedExceptionMessage A Member with ref "ref1_dup" already exists in this Axis
     */
    function testSetRefDuplicateNoContextualizingParents()
    {
        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_dup', [$member11a, $member12a]);
        $member1b = new Orga_Model_Member($this->axis1, 'ref1_dup', [$member11b, $member12a]);
    }

    function testSetRefContextualizingParents()
    {
        $this->axis11->setContextualize(true);
        $this->axis12->setContextualize(true);

        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_context', [$member11a, $member12a]);
        $this->assertSame('ref1_context', $member1a->getRef());
        $member1b = new Orga_Model_Member($this->axis1, 'ref1_context', [$member11b, $member12a]);
        $this->assertSame('ref1_context', $member1b->getRef());
    }

    /**
     * @expectedException Core_Exception_Duplicate
     * @expectedExceptionMessage A Member with ref "ref1_context" already exists in this Axis
     */
    function testSetRefDuplicateContextualizingParents()
    {
        $this->axis11->setContextualize(true);
        $this->axis12->setContextualize(true);

        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_context', [$member11a, $member12a]);
        $member1b = new Orga_Model_Member($this->axis1, 'ref1_context', [$member11b, $member12a]);
        $member1c = new Orga_Model_Member($this->axis1, 'ref1_context', [$member11a, $member12a]);
    }

    function testGetExtendedLabel()
    {
        $this->axis11->setContextualize(true);
        $this->axis12->setContextualize(true);

        $member11a = new Orga_Model_Member($this->axis11, 'ref11_a');
        $member11a->getLabel()->set('Label 11 A', 'fr');
        $member11b = new Orga_Model_Member($this->axis11, 'ref11_b');
        $member11b->getLabel()->set('Label 11 B', 'fr');
        $member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $member12a->getLabel()->set('Label 12 A', 'fr');

        $member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$member11a, $member12a]);
        $member1a->getLabel()->set('Label 1 A', 'fr');
        $member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$member11b, $member12a]);
        $member1b->getLabel()->set('Label 1 B', 'fr');
        $member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$member11a, $member12a]);
        $member1c->getLabel()->set('Label 1 C', 'fr');

        $this->assertSame('Label 11 A', $member11a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 11 B', $member11b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 12 A', $member12a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 A (Label 11 A, Label 12 A)', $member1a->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 B (Label 11 B, Label 12 A)', $member1b->getExtendedLabel()->get('fr'));
        $this->assertSame('Label 1 C (Label 11 A, Label 12 A)', $member1c->getExtendedLabel()->get('fr'));
    }

}

class Orga_Test_MemberTag extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis;
    /**
     * @var Orga_Model_Member
     */
    protected $member;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis = new Orga_Model_Axis($this->organization, 'ref');

        $this->member = new Orga_Model_Member($this->axis, 'ref_member');
        $this->member->getLabel()->set('Member', 'fr');
    }

    public function testGetMemberTag()
    {
        $newMember = new Orga_Model_Member($this->axis, 'ref_new');

        $this->assertSame('1-ref:ref_member', $this->member->getMemberTag());
        $this->assertSame('1-ref:ref_new', $newMember->getMemberTag());

        $this->axis->setMemberPositioning(true);

        $this->assertSame('1-ref:1-ref_member', $this->member->getMemberTag());
        $this->assertSame('1-ref:2-ref_new', $newMember->getMemberTag());

        $this->axis->setMemberPositioning(false);

        $this->assertSame('1-ref:ref_member', $this->member->getMemberTag());
        $this->assertSame('1-ref:ref_new', $newMember->getMemberTag());
    }

    public function testGetTag()
    {
        $newMember = new Orga_Model_Member($this->axis, 'ref_new');

        $this->assertSame('/1-ref:ref_member/', $this->member->getTag());
        $this->assertSame('/1-ref:ref_new/', $newMember->getTag());

        $axisA = new Orga_Model_Axis($this->organization, 'ref_a', $this->axis);
        $axisA->setMemberPositioning(true);

        $axisB = new Orga_Model_Axis($this->organization, 'ref_b', $this->axis);

        $memberA1 = new Orga_Model_Member($axisA, 'refa_1');
        $memberA1->getLabel()->set('Label A 1', 'fr');
        $memberA2 = new Orga_Model_Member($axisA, 'refa_2');

        $memberB1 = new Orga_Model_Member($axisB, 'refb_1');

        $this->member->setDirectParentForAxis($memberA1);
        $this->member->setDirectParentForAxis($memberB1);
        $newMember->setDirectParentForAxis($memberA2);
        $newMember->setDirectParentForAxis($memberB1);

        $this->assertSame('/1-ref_a:1-refa_1/1-ref:ref_member/&/2-ref_b:refb_1/1-ref:ref_member/', $this->member->getTag());
        $this->assertSame('/1-ref_a:2-refa_2/1-ref:ref_new/&/2-ref_b:refb_1/1-ref:ref_new/', $newMember->getTag());
        $this->assertSame('/1-ref_a:1-refa_1/', $memberA1->getTag());
        $this->assertSame('/1-ref_a:2-refa_2/', $memberA2->getTag());
        $this->assertSame('/2-ref_b:refb_1/', $memberB1->getTag());

        $axisAA = new Orga_Model_Axis($this->organization, 'ref_aa', $axisA);
        $axisAA->setContextualize(true);

        $memberAA1 = new Orga_Model_Member($axisAA, 'refaa_1');
        $memberAA2 = new Orga_Model_Member($axisAA, 'refaa_2');

        $memberA1->setDirectParentForAxis($memberAA1);
        $memberA2->setDirectParentForAxis($memberAA1);

        $memberA1bis = new Orga_Model_Member($axisA, 'refa_1', [$memberAA2]);

        $member3 = new Orga_Model_Member($this->axis, 'ref_3', [$memberA1bis, $memberB1]);

        $this->assertSame('/1-ref_aa:refaa_1/1-ref_a:1-refa_1/1-ref:ref_member/&/2-ref_b:refb_1/1-ref:ref_member/', $this->member->getTag());
        $this->assertSame('/1-ref_aa:refaa_1/1-ref_a:2-refa_2/1-ref:ref_new/&/2-ref_b:refb_1/1-ref:ref_new/', $newMember->getTag());
        $this->assertSame('/1-ref_aa:refaa_2/1-ref_a:1-refa_1/1-ref:ref_3/&/2-ref_b:refb_1/1-ref:ref_3/', $member3->getTag());
        $this->assertSame('/1-ref_aa:refaa_1/1-ref_a:1-refa_1/', $memberA1->getTag());
        $this->assertSame('/1-ref_aa:refaa_1/1-ref_a:2-refa_2/', $memberA2->getTag());
        $this->assertSame('/1-ref_aa:refaa_2/1-ref_a:1-refa_1/', $memberA1bis->getTag());
        $this->assertSame('/1-ref_aa:refaa_1/', $memberAA1->getTag());
        $this->assertSame('/1-ref_aa:refaa_2/', $memberAA2->getTag());
        $this->assertSame('/2-ref_b:refb_1/', $memberB1->getTag());
    }

}

class Orga_Test_MemberHierarchy extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis1;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis11;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis111;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis12;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis2;
    /**
     * @var Orga_Model_Member
     */
    protected $member1a;
    /**
     * @var Orga_Model_Member
     */
    protected $member1b;
    /**
     * @var Orga_Model_Member
     */
    protected $member1c;
    /**
     * @var Orga_Model_Member
     */
    protected $member1d;
    /**
     * @var Orga_Model_Member
     */
    protected $member1e;
    /**
     * @var Orga_Model_Member
     */
    protected $member1f;
    /**
     * @var Orga_Model_Member
     */
    protected $member11a;
    /**
     * @var Orga_Model_Member
     */
    protected $member11b;
    /**
     * @var Orga_Model_Member
     */
    protected $member11c;
    /**
     * @var Orga_Model_Member
     */
    protected $member111a;
    /**
     * @var Orga_Model_Member
     */
    protected $member111b;
    /**
     * @var Orga_Model_Member
     */
    protected $member12a;
    /**
     * @var Orga_Model_Member
     */
    protected $member12b;
    /**
     * @var Orga_Model_Member
     */
    protected $member2a;
    /**
     * @var Orga_Model_Member
     */
    protected $member2b;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');

        $this->member111a = new Orga_Model_Member($this->axis111, 'ref111_a');
        $this->member111b = new Orga_Model_Member($this->axis111, 'ref111_b');

        $this->member11a = new Orga_Model_Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11b = new Orga_Model_Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11c = new Orga_Model_Member($this->axis11, 'ref11_c', [$this->member111b]);

        $this->member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $this->member12b = new Orga_Model_Member($this->axis12, 'ref12_b');

        $this->member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1d = new Orga_Model_Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1e = new Orga_Model_Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1f = new Orga_Model_Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);

        $this->member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->member2b = new Orga_Model_Member($this->axis2, 'ref2_b');
    }

    public function testGetDirectParentForAxis()
    {
        $this->assertSame($this->member11a, $this->member1a->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12a, $this->member1a->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member11a, $this->member1b->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12b, $this->member1b->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member11b, $this->member1c->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12a, $this->member1c->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member11b, $this->member1d->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12b, $this->member1d->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member11c, $this->member1e->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12a, $this->member1e->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member11c, $this->member1f->getDirectParentForAxis($this->axis11));
        $this->assertSame($this->member12b, $this->member1f->getDirectParentForAxis($this->axis12));
        $this->assertSame($this->member111a, $this->member11a->getDirectParentForAxis($this->axis111));
        $this->assertSame($this->member111b, $this->member11b->getDirectParentForAxis($this->axis111));
        $this->assertSame($this->member111b, $this->member11c->getDirectParentForAxis($this->axis111));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage The given Axis is not a direct broader of the Member's Axis
     */
    public function testGetDirectParentForAxisNotBroader()
    {
        $this->member1a->getDirectParentForAxis($this->axis2);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No direct parent Member matching Axis "new".
     */
    public function testGetDirectParentForAxisNoMember()
    {
        $newBroaderAxis = new Orga_Model_Axis($this->organization, 'new', $this->axis2);

        $this->member2a->getDirectParentForAxis($newBroaderAxis);
    }

    public function testGetAllParents()
    {
        $this->assertSame([$this->member11a, $this->member111a, $this->member12a], $this->member1a->getAllParents());
        $this->assertSame([$this->member11a, $this->member111a, $this->member12b], $this->member1b->getAllParents());
        $this->assertSame([$this->member11b, $this->member111b, $this->member12a], $this->member1c->getAllParents());
        $this->assertSame([$this->member11b, $this->member111b, $this->member12b], $this->member1d->getAllParents());
        $this->assertSame([$this->member11c, $this->member111b, $this->member12a], $this->member1e->getAllParents());
        $this->assertSame([$this->member11c, $this->member111b, $this->member12b], $this->member1f->getAllParents());
        $this->assertSame([$this->member111a], $this->member11a->getAllParents());
        $this->assertSame([$this->member111b], $this->member11b->getAllParents());
        $this->assertSame([$this->member111b], $this->member11c->getAllParents());
        $this->assertSame([], $this->member111a->getAllParents());
        $this->assertSame([], $this->member111b->getAllParents());
        $this->assertSame([], $this->member12a->getAllParents());
        $this->assertSame([], $this->member12b->getAllParents());
        $this->assertSame([], $this->member2a->getAllParents());
        $this->assertSame([], $this->member2b->getAllParents());
    }

    public function testGetParentForAxis()
    {
        $this->assertSame($this->member11a, $this->member1a->getParentForAxis($this->axis11));
        $this->assertSame($this->member111a, $this->member1a->getParentForAxis($this->axis111));
        $this->assertSame($this->member12a, $this->member1a->getParentForAxis($this->axis12));
        $this->assertSame($this->member11a, $this->member1b->getParentForAxis($this->axis11));
        $this->assertSame($this->member111a, $this->member1b->getParentForAxis($this->axis111));
        $this->assertSame($this->member12b, $this->member1b->getParentForAxis($this->axis12));
        $this->assertSame($this->member11b, $this->member1c->getParentForAxis($this->axis11));
        $this->assertSame($this->member111b, $this->member1c->getParentForAxis($this->axis111));
        $this->assertSame($this->member12a, $this->member1c->getParentForAxis($this->axis12));
        $this->assertSame($this->member11b, $this->member1d->getParentForAxis($this->axis11));
        $this->assertSame($this->member111b, $this->member1d->getParentForAxis($this->axis111));
        $this->assertSame($this->member12b, $this->member1d->getParentForAxis($this->axis12));
        $this->assertSame($this->member11c, $this->member1e->getParentForAxis($this->axis11));
        $this->assertSame($this->member111b, $this->member1e->getParentForAxis($this->axis111));
        $this->assertSame($this->member12a, $this->member1e->getParentForAxis($this->axis12));
        $this->assertSame($this->member11c, $this->member1f->getParentForAxis($this->axis11));
        $this->assertSame($this->member111b, $this->member1f->getParentForAxis($this->axis111));
        $this->assertSame($this->member12b, $this->member1f->getParentForAxis($this->axis12));
        $this->assertSame($this->member111a, $this->member11a->getParentForAxis($this->axis111));
        $this->assertSame($this->member111b, $this->member11b->getParentForAxis($this->axis111));
        $this->assertSame($this->member111b, $this->member11c->getParentForAxis($this->axis111));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage The given Axis is not a broader of the Member's Axis
     */
    public function testGetParentForAxisNotBroader()
    {
        $this->member1a->getParentForAxis($this->axis2);
    }

    /**
     * @expectedException Core_Exception_NotFound
     * @expectedExceptionMessage No parent Member matching Axis "new".
     */
    public function testGetParentForAxisNoMember()
    {
        $newBroaderAxis = new Orga_Model_Axis($this->organization, 'new', $this->axis111);

        $this->member1a->getParentForAxis($newBroaderAxis);
    }

    public function getChildrenForAxis()
    {
        $this->assertSame([$this->member1a], $this->member11a->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member1b], $this->member11b->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member1b], $this->member11c->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member11a], $this->member111a->getChildrenForAxis($this->axis11));
        $this->assertSame([$this->member1a, $this->member1b], $this->member111a->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member11b, $this->member11c], $this->member111b->getChildrenForAxis($this->axis11));
        $this->assertSame([$this->member1c, $this->member1d, $this->member1e, $this->member1f], $this->member111b->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member1a, $this->member1c, $this->member1e], $this->member12a->getChildrenForAxis($this->axis1));
        $this->assertSame([$this->member1b, $this->member1d, $this->member1f], $this->member12b->getChildrenForAxis($this->axis1));
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage The given Axis is not a narrower of the Member's Axis
     */
    public function testGetChildrenForAxisNotNarrower()
    {
        $this->member1a->getChildrenForAxis($this->axis2);
    }

}

class Orga_Test_MemberAdjacent extends TestCase
{
    /**
     * @var Orga_Model_Organization
     */
    protected $organization;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis1;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis11;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis111;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis12;
    /**
     * @var Orga_Model_Axis
     */
    protected $axis2;
    /**
     * @var Orga_Model_Member
     */
    protected $member1a;
    /**
     * @var Orga_Model_Member
     */
    protected $member1b;
    /**
     * @var Orga_Model_Member
     */
    protected $member1c;
    /**
     * @var Orga_Model_Member
     */
    protected $member1d;
    /**
     * @var Orga_Model_Member
     */
    protected $member1e;
    /**
     * @var Orga_Model_Member
     */
    protected $member1f;
    /**
     * @var Orga_Model_Member
     */
    protected $member1g;
    /**
     * @var Orga_Model_Member
     */
    protected $member1h;
    /**
     * @var Orga_Model_Member
     */
    protected $member1i;
    /**
     * @var Orga_Model_Member
     */
    protected $member1j;
    /**
     * @var Orga_Model_Member
     */
    protected $member1k;
    /**
     * @var Orga_Model_Member
     */
    protected $member1l;
    /**
     * @var Orga_Model_Member
     */
    protected $member11a;
    /**
     * @var Orga_Model_Member
     */
    protected $member11b;
    /**
     * @var Orga_Model_Member
     */
    protected $member11c;
    /**
     * @var Orga_Model_Member
     */
    protected $member111a;
    /**
     * @var Orga_Model_Member
     */
    protected $member111b;
    /**
     * @var Orga_Model_Member
     */
    protected $member12a;
    /**
     * @var Orga_Model_Member
     */
    protected $member12b;
    /**
     * @var Orga_Model_Member
     */
    protected $member2a;
    /**
     * @var Orga_Model_Member
     */
    protected $member2b;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->organization = new Orga_Model_Organization($this->getMockBuilder(Account::class)->disableOriginalConstructor()->getMock());

        $this->axis1 = new Orga_Model_Axis($this->organization, 'ref_1');
        $this->axis1->setMemberPositioning(true);
        $this->axis11 = new Orga_Model_Axis($this->organization, 'ref_11', $this->axis1);
        $this->axis11->setMemberPositioning(true);
        $this->axis111 = new Orga_Model_Axis($this->organization, 'ref_111', $this->axis11);
        $this->axis111->setMemberPositioning(true);
        $this->axis111->setContextualize(true);
        $this->axis12 = new Orga_Model_Axis($this->organization, 'ref_12', $this->axis1);
        $this->axis12->setMemberPositioning(true);
        $this->axis12->setContextualize(true);
        $this->axis2 = new Orga_Model_Axis($this->organization, 'ref_2');

        $this->member111a = new Orga_Model_Member($this->axis111, 'ref111_a');
        $this->member111b = new Orga_Model_Member($this->axis111, 'ref111_b');

        $this->member11a = new Orga_Model_Member($this->axis11, 'ref11_a', [$this->member111a]);
        $this->member11b = new Orga_Model_Member($this->axis11, 'ref11_b', [$this->member111b]);
        $this->member11c = new Orga_Model_Member($this->axis11, 'ref11_c', [$this->member111b]);

        $this->member12a = new Orga_Model_Member($this->axis12, 'ref12_a');
        $this->member12b = new Orga_Model_Member($this->axis12, 'ref12_b');

        $this->member1a = new Orga_Model_Member($this->axis1, 'ref1_a', [$this->member11a, $this->member12a]);
        $this->member1b = new Orga_Model_Member($this->axis1, 'ref1_b', [$this->member11a, $this->member12b]);
        $this->member1c = new Orga_Model_Member($this->axis1, 'ref1_c', [$this->member11b, $this->member12a]);
        $this->member1d = new Orga_Model_Member($this->axis1, 'ref1_d', [$this->member11b, $this->member12b]);
        $this->member1e = new Orga_Model_Member($this->axis1, 'ref1_e', [$this->member11c, $this->member12a]);
        $this->member1f = new Orga_Model_Member($this->axis1, 'ref1_f', [$this->member11c, $this->member12b]);
        $this->member1g = new Orga_Model_Member($this->axis1, 'ref1_g', [$this->member11a, $this->member12a]);
        $this->member1h = new Orga_Model_Member($this->axis1, 'ref1_h', [$this->member11a, $this->member12b]);
        $this->member1i = new Orga_Model_Member($this->axis1, 'ref1_i', [$this->member11a, $this->member12a]);
        $this->member1j = new Orga_Model_Member($this->axis1, 'ref1_j', [$this->member11b, $this->member12b]);
        $this->member1k = new Orga_Model_Member($this->axis1, 'ref1_k', [$this->member11c, $this->member12a]);
        $this->member1l = new Orga_Model_Member($this->axis1, 'ref1_l', [$this->member11c, $this->member12b]);

        $this->member2a = new Orga_Model_Member($this->axis2, 'ref2_a');
        $this->member2b = new Orga_Model_Member($this->axis2, 'ref2_b');
    }

    public function testGetPreviousMember()
    {
        $this->assertNull($this->member111a->getPreviousMember());
        $this->assertSame($this->member111a, $this->member111b->getPreviousMember());

        $this->assertNull($this->member11a->getPreviousMember());
        $this->assertNull($this->member11b->getPreviousMember());
        $this->assertSame($this->member11b, $this->member11c->getPreviousMember());

        $this->assertNull($this->member12a->getPreviousMember());
        $this->assertSame($this->member12a, $this->member12b->getPreviousMember());

        $this->assertNull($this->member1a->getPreviousMember());
        $this->assertNull($this->member1b->getPreviousMember());
        $this->assertNull($this->member1c->getPreviousMember());
        $this->assertNull($this->member1d->getPreviousMember());
        $this->assertSame($this->member1c, $this->member1e->getPreviousMember());
        $this->assertSame($this->member1d, $this->member1f->getPreviousMember());
        $this->assertSame($this->member1a, $this->member1g->getPreviousMember());
        $this->assertSame($this->member1b, $this->member1h->getPreviousMember());
        $this->assertSame($this->member1g, $this->member1i->getPreviousMember());
        $this->assertSame($this->member1f, $this->member1j->getPreviousMember());
        $this->assertSame($this->member1e, $this->member1k->getPreviousMember());
        $this->assertSame($this->member1j, $this->member1l->getPreviousMember());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage This member needs to come from an axis allowing members positioning.
     */
    public function testGetPreviousMemberNotPositionning()
    {
        $this->member2a->getPreviousMember();
    }

    public function testGetNextMember()
    {
        $this->assertSame($this->member111b, $this->member111a->getNextMember());
        $this->assertNull($this->member111b->getNextMember());

        $this->assertNull($this->member11a->getNextMember());
        $this->assertSame($this->member11c, $this->member11b->getNextMember());
        $this->assertNull($this->member11c->getNextMember());

        $this->assertSame($this->member12b, $this->member12a->getNextMember());
        $this->assertNull($this->member12b->getNextMember());

        $this->assertSame($this->member1g, $this->member1a->getNextMember());
        $this->assertSame($this->member1h, $this->member1b->getNextMember());
        $this->assertSame($this->member1e, $this->member1c->getNextMember());
        $this->assertSame($this->member1f, $this->member1d->getNextMember());
        $this->assertSame($this->member1k, $this->member1e->getNextMember());
        $this->assertSame($this->member1j, $this->member1f->getNextMember());
        $this->assertSame($this->member1i, $this->member1g->getNextMember());
        $this->assertNull($this->member1h->getNextMember());
        $this->assertNull($this->member1i->getNextMember());
        $this->assertSame($this->member1l, $this->member1j->getNextMember());
        $this->assertNull($this->member1k->getNextMember());
        $this->assertNull($this->member1l->getNextMember());
    }

    /**
     * @expectedException Core_Exception_InvalidArgument
     * @expectedExceptionMessage This member needs to come from an axis allowing members positioning.
     */
    public function testGetNextMemberNotPositionning()
    {
        $this->member2a->getNextMember();
    }


}
