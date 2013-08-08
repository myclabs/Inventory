<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Orga
 */

/**
 * Controller du datagrid de coherence
 * @package Orga
 */
class Orga_OrganizationConsistency extends Core_Model_Entity_Singleton
{
    /**
     * Methode qui vérifie la cohérence d'un cube.
     *
     * @param Orga_Model_Organization $cube
     * @return array();
     */
    public function check($cube)
    {
        $listAxes = array();
        $listParentsAxes = array();
        $listParentsMembers = array();
        $listChildrenAxes = array();
        $listChildrenMembers = array();

        $checkAxes = __('Orga', 'control', 'axisWithNoMember');
        $checkBroaderMember = __('Orga', 'control', 'memberWithMissingDirectParent');
        $checkNarrowerMember = __('Orga', 'control', 'memberWithNoDirectChild');

        foreach ($cube->getAxes() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxes[] = $axis->getLabel();
            }
            if ($axis->hasDirectBroaders()) {
                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    foreach ($axis->getMembers() as $member) {
                        try {
                            $member->getParentForAxis($broaderAxis);
                        } catch (Core_Exception_NotFound $e) {
                            $listParentsAxes[] = $axis->getLabel();
                            $listParentsMembers[] = $member->getLabel();
                        }
                    }
                    foreach ($broaderAxis->getMembers() as $parentMember) {
                        if (count($parentMember->getChildrenForAxis($axis)) === 0) {
                            $listChildrenAxes[] = $broaderAxis->getLabel();
                            $listChildrenMembers[] = $parentMember->getLabel();
                        }
                    }
                }
            }
        }

        $n = count($listAxes);
        $text1 = '';
        $i = 0;
        foreach ($listAxes as $l1) {
            if ($i == $n-1) {
                $text1 = $text1.$l1;
            } else {
                $text1 = $text1.$l1.'; ';
            }
            $i++;
        }

        $text2 = '';
        $m = count($listParentsAxes);
        for ($i = 0; $i <= $m-1; $i++) {
            if ($i == $m-1) {
                $text2 = $text2.'Axe: '.$listParentsAxes[$i].'; membre : '.$listParentsMembers[$i];
            } else {
                $text2 = $text2.'Axe: '.$listParentsAxes[$i].'; membre : '.$listParentsMembers[$i].' / ';
            }
        }

        $text3 = '';
        $l = count($listChildrenAxes);
        for ($i = 0; $i <= $l-1; $i++) {
            if ($i == $l-1) {
                $text3 = $text3.'Axe: '.$listChildrenAxes[$i].'; membre : '.$listChildrenMembers[$i];
            } else {
                $text3 = $text3.'Axe: '.$listChildrenAxes[$i].'; membre : '.$listChildrenMembers[$i].' / ';
            }
        }

        $result  = array();
        $result['okAxis'] = empty($listAxes);
        $result['controlAxis'] = $checkAxes;
        $result['failureAxis'] = $text1;

        $result['okMemberParents'] = empty($listParentsAxes);
        $result['controlMemberParents'] = $checkBroaderMember;
        $result['failureMemberParents'] = $text2;

        $result['okMemberChildren'] = empty($listChildrenAxes);
        $result['controlMemberChildren'] = $checkNarrowerMember;
        $result['failureMemberChildren'] = $text3;

        return $result;
    }
}