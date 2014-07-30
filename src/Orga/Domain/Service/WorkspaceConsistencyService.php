<?php
/**
 * @author valentin.claras
 * @author diana.dragusin
 * @package Orga
 */

namespace Orga\Domain\Service;

use Core\Translation\TranslatedString;
use Core_Exception_NotFound;
use Mnapoli\Translated\Translator;
use Orga\Domain\Axis;
use Orga\Domain\Granularity;
use Orga\Domain\Workspace;

/**
 * Controller du datagrid de coherence
 * @package Orga
 */
class WorkspaceConsistencyService
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    /**
     * Methode qui vérifie la cohérence d'un cube.
     *
     * @param Workspace $workspace
     * @return array();
     */
    public function check($workspace)
    {
        $listAxes = array();
        $listParentsAxes = array();
        $listParentsMembers = array();
        $listChildrenAxes = array();
        $listChildrenMembers = array();

        $checkAxes = __('Orga', 'control', 'axisWithNoMember');
        $checkBroaderMember = __('Orga', 'control', 'memberWithMissingDirectParent');
        $checkNarrowerMember = __('Orga', 'control', 'memberWithNoDirectChild');

        foreach ($workspace->getFirstOrderedAxes() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxes[] = $this->translator->get($axis->getLabel());
            }
            if ($axis->hasDirectBroaders()) {
                foreach ($axis->getDirectBroaders() as $broaderAxis) {
                    foreach ($axis->getOrderedMembers() as $member) {
                        try {
                            $member->getParentForAxis($broaderAxis);
                        } catch (Core_Exception_NotFound $e) {
                            $listParentsAxes[] = $this->translator->get($axis->getLabel());
                            $listParentsMembers[] = $this->translator->get($member->getLabel());
                        }
                    }
                    foreach ($broaderAxis->getOrderedMembers() as $parentMember) {
                        if (count($parentMember->getChildrenForAxis($axis)) === 0) {
                            $listChildrenAxes[] = $this->translator->get($broaderAxis->getLabel());
                            $listChildrenMembers[] = $this->translator->get($parentMember->getLabel());
                        }
                    }
                }
            }
        }

        $n = count($listAxes);
        $text1 = '';
        $i = 0;
        foreach ($listAxes as $l1) {
            if ($i == $n - 1) {
                $text1 = $text1 . $l1;
            } else {
                $text1 = $text1 . $l1 . '; ';
            }
            $i++;
        }

        $text2 = '';
        $m = count($listParentsAxes);
        for ($i = 0; $i <= $m - 1; $i++) {
            if ($i == $m - 1) {
                $text2 = $text2 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listParentsAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __(
                        'UI',
                        'other',
                        ':'
                    ) . $listParentsMembers[$i];
            } else {
                $text2 = $text2 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listParentsAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __(
                        'UI',
                        'other',
                        ':'
                    ) . $listParentsMembers[$i] . ' / ';
            }
        }

        $text3 = '';
        $l = count($listChildrenAxes);
        for ($i = 0; $i <= $l - 1; $i++) {
            if ($i == $l - 1) {
                $text3 = $text3 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listChildrenAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __(
                        'UI',
                        'other',
                        ':'
                    ) . $listChildrenMembers[$i];
            } else {
                $text3 = $text3 . __('UI', 'name', 'axis') . __('UI', 'other', ':') . $listChildrenAxes[$i]
                    . __('UI', 'other', ';') . __('UI', 'name', 'elementSmallCap') . __(
                        'UI',
                        'other',
                        ':'
                    ) . $listChildrenMembers[$i] . ' / ';
            }
        }

        $result = array();
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
