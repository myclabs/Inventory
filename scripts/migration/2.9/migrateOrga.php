<?php

echo "\n".'Ajout des Tag aux éléments de la structure organisationnelle…'."\n";
/** @var Orga_Model_Organization $organization */
foreach (Orga_Model_Organization::loadList() as $organization) {
    echo "\tRoot axes" . PHP_EOL;
    /** @var Orga_Model_Axis $rootAxis */
    foreach ($organization->getRootAxes() as $rootAxis) {
        $rootAxis->updateNarrowerTag();
    }
    echo "\tAxes & members" . PHP_EOL;
    $axes = $organization->getAxes()->toArray();
    $axes = array_reverse($axes);
    /** @var Orga_Model_Axis $axis */
    foreach ($axes as $axis) {
        $axis->updateBroaderTag();
        foreach ($axis->getMembers() as $member) {
            $member->updateParentMembersHashKeys();
            $member->updateTag();
        }
    }
    echo "\tGranularities" . PHP_EOL;
    foreach ($organization->getGranularities() as $granularity) {
        $granularity->updateRef();
        $granularity->updateTag();
    }
    echo "\tCells" . PHP_EOL;
    foreach ($organization->getGranularities() as $granularity) {
        foreach ($granularity->getCells() as $cell) {
            $cell->updateMembersHashKey();
            $cell->updateTag();
        }
    }
    $organization->getGranularityByRef('global')->getCellByMembers([])->setRelevant(true);
}
echo "\n".'…ajout terminé !'."\n";

echo "\n".'-> Flush starting…'."\n";
$em->flush();
$em->clear();
echo "\n".'-> Flush ended !'."\n";

echo "\n".'Vérification de la structure…'."\n";
$wrongNarrowerTagAxes = [];
$wrongBroaderTagAxes = [];
$wrongTagMembers = [];
$wrongTagGranularities = [];
$wrongTagCells = [];
foreach (Orga_Model_Organization::loadList() as $organization) {
    foreach ($organization->getAxes() as $axis) {
        $expectedNarrowerTag = buildAxisNarrowerTag($axis);
        if ($expectedNarrowerTag !== $axis->getNarrowerTag()) {
            $wrongNarrowerTagAxes[] = [$axis, $expectedNarrowerTag];
        }
        $expectedBroaderTag = buildAxisBroaderTag($axis);
        if ($expectedBroaderTag !== $axis->getBroaderTag()) {
            $wrongBroaderTagAxes[] = [$axis, $expectedBroaderTag];
        }
    }
    foreach ($organization->getAxes() as $axis) {
        foreach ($axis->getMembers() as $member) {
            $expectedTag = buildMemberTag($member);
            if ($expectedTag !== $member->getTag()) {
                $wrongTagMembers[] = [$member, $expectedTag];
            }
        }
    }
    foreach ($organization->getGranularities() as $granularity) {
        $expectedTag = buildGranularityTag($granularity);
        if ($expectedTag !== $granularity->getTag()) {
            $wrongTagGranularities[] = [$granularity, $expectedTag];
        }
    }
    foreach ($organization->getGranularities() as $granularity) {
        foreach ($granularity->getCells() as $cell) {
            $expectedTag = buildCellTag($cell);
            if ($expectedTag !== $cell->getTag()) {
                $wrongTagCells[] = [$cell, $expectedTag];
            }
        }
    }
}
echo "\n".'…vérification terminé !'."\n";
echo "\n".'Affichage des erreurs :'."\n";
if (!empty($wrongNarrowerTagAxes)) {
    echo "\t Wrong Narrower Tag Axes :" . PHP_EOL;
    foreach ($wrongNarrowerTagAxes as $errorArray) {
        $axis = $errorArray[0];
        echo "\t\t".$axis->getId()." - ".$axis->getLabel()." : ".$axis->getNarrowerTag()." expected ".$errorArray[1]."\n";
    }
}
if (!empty($wrongBroaderTagAxes)) {
    echo "\t Wrong Broader Tag Axes :" . PHP_EOL;
    foreach ($wrongBroaderTagAxes as $errorArray) {
        $axis = $errorArray[0];
        echo "\t\t".$axis->getId()." - ".$axis->getLabel()." : ".$axis->getBroaderTag()." expected ".$errorArray[1]."\n";
    }
}
if (!empty($wrongTagMembers)) {
    echo "\t Wrong Tag Members :" . PHP_EOL;
    foreach ($wrongTagMembers as $errorArray) {
        $member = $errorArray[0];
        echo "\t\t".$member->getId()." - ".$member->getLabel()." : ".$member->getTag()." expected ".$errorArray[1]."\n";
    }
}
if (!empty($wrongTagGranularities)) {
    echo "\t Wrong Tag Granularities :" . PHP_EOL;
    foreach ($wrongTagGranularities as $errorArray) {
        $granularity = $errorArray[0];
        echo "\t\t".$granularity->getId()." - ".$granularity->getLabel()." : ".$granularity->getTag()." expected ".$errorArray[1]."\n";
    }
}
if (!empty($wrongTagMembers)) {
    echo "\t Wrong Tag Cells :" . PHP_EOL;
    foreach ($wrongTagMembers as $errorArray) {
        $cell = $errorArray[0];
        echo "\t\t".$cell->getId()." - ".$cell->getLabel()." : ".$cell->getTag()." expected ".$errorArray[1]."\n";
    }
}
echo "\n".'Migration terminée !'."\n";

$em->clear();

function buildAxisNarrowerTag (Orga_Model_Axis $axis) {
    $narrowerTag = Orga_Model_Organization::PATH_SEPARATOR;
    $directNarrower = $axis->getDirectNarrower();
    while ($directNarrower != null) {
        $narrowerTag = Orga_Model_Organization::PATH_SEPARATOR. $directNarrower->getAxisTag() . $narrowerTag;
        $directNarrower = $directNarrower->getDirectNarrower();
    }
    return $narrowerTag . $axis->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR;
}

function buildAxisBroaderTag (Orga_Model_Axis $axis) {
    if ($axis->hasDirectBroaders()) {
        $broaderTag = '';
        $criteriaDESC = Doctrine\Common\Collections\Criteria::create();
        $criteriaDESC->orderBy(['narrowerTag' => 'DESC']);
        foreach ($axis->getDirectBroaders()->matching($criteriaDESC) as $directBroader) {
            $directBroaderTag = buildAxisBroaderTag($directBroader);
            foreach (explode(Orga_Model_Organization::PATH_JOIN, $directBroaderTag) as  $pathTag) {
                $broaderTag .= $pathTag . $axis->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR . Orga_Model_Organization::PATH_JOIN;
            }
        }
        $broaderTag = substr($broaderTag, 0, -1);
    } else {
        $broaderTag = Orga_Model_Organization::PATH_SEPARATOR . $axis->getAxisTag() . Orga_Model_Organization::PATH_SEPARATOR;
    }
    return $broaderTag;
}

function buildMemberTag (Orga_Model_Member $member) {
    if ($member->hasDirectParents()) {
        $tag = '';
        foreach ($member->getDirectParents() as $directParent) {
            $directParentTag = buildMemberTag($directParent);
            foreach (explode(Orga_Model_Organization::PATH_JOIN, $directParentTag) as  $pathTag) {
                $tag .= $pathTag . $member->getMemberTag() . Orga_Model_Organization::PATH_SEPARATOR . Orga_Model_Organization::PATH_JOIN;
            }
        }
        $tag = substr($tag, 0, -1);
    } else {
        $tag = Orga_Model_Organization::PATH_SEPARATOR . $member->getMemberTag() . Orga_Model_Organization::PATH_SEPARATOR;
    }
    return $tag;
}

function buildGranularityTag (Orga_Model_Granularity $granularity) {
    if ($granularity->hasAxes()) {
        $tag = '';
        $axes = $granularity->getAxes();
        @usort($axes, ['Orga_Model_Axis', 'firstOrderAxes']);
        $axes = array_reverse($axes);
        foreach ($axes as $axis) {
            $tag .= buildAxisBroaderTag($axis) . Orga_Model_Organization::PATH_JOIN;
        }
        $tag = substr($tag, 0, -1);
    } else {
        $tag = Orga_Model_Organization::PATH_SEPARATOR;
    }
    return $tag;
}

function buildCellTag (Orga_Model_Cell $cell) {
    if ($cell->hasMembers()) {
        $tag = '';
        $members = $cell->getMembers();
        @usort($members, [Orga_Model_Member::class, 'orderMembers']);
        foreach ($members as $member) {
            $tag .= buildMemberTag($member) . Orga_Model_Organization::PATH_JOIN;
        }
        $tag = substr($tag, 0, -1);
    } else {
        $tag = Orga_Model_Organization::PATH_SEPARATOR;
    }
    return $tag;
}
