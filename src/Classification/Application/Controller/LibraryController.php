<?php

use Account\Domain\Account;
use Account\Domain\AccountRepository;
use Classification\Domain\Axis;
use Classification\Domain\ClassificationLibrary;
use Classification\Domain\Member;
use Classification\Application\Service\ClassificationExportService;
use Core\Annotation\Secure;
use Core\Translation\TranslatedString;
use MyCLabs\ACL\ACL;
use User\Domain\ACL\Actions;

class Classification_LibraryController extends Core_Controller
{
    /**
     * @Inject
     * @var ACL
     */
    private $acl;

    /**
     * @Inject
     * @var AccountRepository
     */
    private $accountRepository;

    /**
     * @Inject
     * @var ClassificationExportService
     */
    private $exportService;

    /**
     * @Secure("viewClassificationLibrary")
     */
    public function viewAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $this->view->assign('library', $library);
        $canEdit = $this->acl->isAllowed($this->_helper->auth(), Actions::EDIT, $library);
        $this->view->assign('edit', $canEdit);
        $this->setActiveMenuItemClassificationLibrary($library->getId());
    }

    /**
     * @Secure("editAccount")
     */
    public function newAction()
    {
        /** @var $account Account */
        $account = $this->accountRepository->get($this->getParam('account'));

        if ($this->getRequest()->isPost()) {
            $label = trim($this->getParam('label'));

            if ($label == '') {
                UI_Message::addMessageStatic(__('UI', 'formValidation', 'allFieldsRequired'));
            } else {
                $label = $this->translator->set(new TranslatedString(), $label);
                $library = new ClassificationLibrary($account, $label);
                $library->save();
                $this->entityManager->flush();

                UI_Message::addMessageStatic(
                    __('Classification', 'library', 'libraryCreated'),
                    UI_Message::TYPE_SUCCESS
                );
                $this->redirect('classification/library/view/id/' . $library->getId());
                return;
            }
        }

        $this->view->assign('account', $account);
    }

    /**
     * @Secure("deleteClassificationLibrary")
     */
    public function deleteAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $library->delete();
        try {
            $this->entityManager->flush();
            UI_Message::addMessageStatic(__('UI', 'message', 'deleted'), UI_Message::TYPE_SUCCESS);
        } catch (Core_ORM_ForeignKeyViolationException $e) {
            UI_Message::addMessageStatic(
                __('Classification', 'library', 'libraryDeletionError'),
                UI_Message::TYPE_ERROR
            );
        }

        $this->redirect('account/dashboard');
    }

    /**
     * @Secure("editClassificationLibrary")
     */
    public function checkConsistencyAction()
    {
        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $listAxesWithoutMember = [];
        $listAxesWithMembersNotLinkedToBroader = [];
        $listAxesWithMembersNotLinkedToNarrower = [];
        $listContextIndicatorsWithLinkedAxes = [];

        foreach ($library->getAxes() as $axis) {
            if (!$axis->hasMembers()) {
                $listAxesWithoutMember[] = $this->translator->get($axis->getLabel());
            } else {
                $narrowerAxis = $axis->getDirectNarrower();
                $broaderAxes = $axis->getDirectBroaders();

                foreach ($axis->getMembers() as $member) {
                    if ($narrowerAxis !== null) {
                        $intersectMemberNarrowerMembers = array_uintersect(
                            $member->getDirectChildren(),
                            $narrowerAxis->getMembers(),
                            function(Member $a, Member $b) {
                                return (($a === $b) ? 0 : (($a > $b) ? 1 : -1));
                            }
                        );
                        if (count($intersectMemberNarrowerMembers) < 1) {
                            if (!isset($listAxesWithMembersNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()])) {
                                $listAxesWithMembersNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()] = [];
                            }
                            $listAxesWithMembersNotLinkedToNarrower[$axis->getId()][$narrowerAxis->getId()][] = $this->translator->get($member->getLabel());
                        }
                    }
                    foreach ($broaderAxes as $broaderAxis) {
                        $intersectMemberBroaderMembers = array_uintersect(
                            $member->getDirectParents(),
                            $broaderAxis->getMembers(),
                            function (Member $a, Member $b) {
                                return (($a === $b) ? 0 : (($a > $b) ? 1 : -1));
                            }
                        );
                        if (count($intersectMemberBroaderMembers) !== 1) {
                            if (!isset($listAxesWithMembersNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()])) {
                                $listAxesWithMembersNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()] = [];
                            }
                            $listAxesWithMembersNotLinkedToBroader[$axis->getId()][$broaderAxis->getId()][] = $this->translator->get($member->getLabel());
                        }
                    }
                }
            }
        }

        foreach ($library->getContextIndicators() as $contextIndicator) {
            $contextIndicatorAxes = $contextIndicator->getAxes();
            $contextIndicatorErrors = array();
            foreach ($contextIndicatorAxes as $contextIndicatorAxis) {
                foreach ($contextIndicatorAxes as $contextIndicatorAxisVerif) {
                    if (($contextIndicatorAxis !== $contextIndicatorAxisVerif)
                        && ($contextIndicatorAxis->isNarrowerThan($contextIndicatorAxisVerif))) {
                        $contextIndicatorErrors[] = '('
                            . $this->translator->get($contextIndicatorAxis->getLabel())
                            . ' - ' . $this->translator->get($contextIndicatorAxisVerif->getLabel())
                            . ')';
                    }
                }
            }
            if (count($contextIndicatorErrors) > 0) {
                $listContextIndicatorsWithLinkedAxes[] = array(
                    'contextIndicator' => $contextIndicator,
                    'axes' => $contextIndicatorErrors
                );
            }
        }

        $messages = [];

        if (!empty($listAxesWithoutMember)) {
            $message = new stdClass();
            $message->control = __('Classification', 'control', 'axisWithNoMember');
            $message->occurences = implode(', ', $listAxesWithoutMember);
            $messages[] = $message;
        }

        if (!empty($listAxesWithMembersNotLinkedToNarrower)) {
            $message = new stdClass();
            $message->control = __('Classification', 'control', 'memberWithNoDirectChild');
            $message->occurences = '';
            foreach ($listAxesWithMembersNotLinkedToNarrower as $axisId => $narrowerAxesMembers) {
                $axis = Axis::load($axisId);
                $message->occurences .= $this->translator->get($axis->getLabel()) . ' : { ';
                foreach ($narrowerAxesMembers as $narrowerAxisId => $refMembers) {
                    $narrowerAxis = Axis::load($narrowerAxisId);
                    $message->occurences .= $this->translator->get($narrowerAxis->getLabel())
                        . ' : [' . implode(', ', $refMembers) . '], ';
                }
                $message->occurences = substr($message->occurences, 0, -2);
                $message->occurences .= ' }, ';
            }
            $message->occurences = substr($message->occurences, 0, -2);
            $messages[] = $message;
        }

        if (!empty($listAxesWithMembersNotLinkedToBroader)) {
            $message = new stdClass();
            $message->control = __('Classification', 'control', 'memberWithMissingDirectParent');
            $message->occurences = '';
            foreach ($listAxesWithMembersNotLinkedToBroader as $axisId => $narrowerAxesMembers) {
                $axis = Axis::load($axisId);
                $message->occurences .= $this->translator->get($axis->getLabel()) . ' : { ';
                foreach ($narrowerAxesMembers as $broaderAxisId => $refMembers) {
                    $broaderAxis = Axis::load($broaderAxisId);
                    $message->occurences .= $this->translator->get($broaderAxis->getLabel())
                        . ' : [' . implode(', ', $refMembers) . '], ';
                }
                $message->occurences = substr($message->occurences, 0, -2);
                $message->occurences .= ' }, ';
            }
            if (strlen($message->occurences) > 0) {
                $message->occurences = substr($message->occurences, 0, -2);
            }
            $messages[] = $message;
        }

        if (!empty($listContextIndicatorsWithLinkedAxes)) {
            $message = new stdClass();
            $message->control = __('Classification', 'control', 'contextIndicatorsWithLinkedAxes');
            $message->occurences = '';
            foreach ($listContextIndicatorsWithLinkedAxes as $contextIndicatorArray) {
                $message->occurences .= $this->translator->get($contextIndicatorArray['contextIndicator']->getContext()->getLabel()) . ' - ' .
                    $this->translator->get($contextIndicatorArray['contextIndicator']->getIndicator()->getLabel()) .
                    ' : { ' . implode(', ', $contextIndicatorArray['axes']) . ' }, ';
            }
            if (strlen($message->occurences) > 0) {
                $message->occurences = substr($message->occurences, 0, -2);
            }
            $messages[] = $message;
        }

        $this->sendJsonResponse(['messages' => $messages]);
    }

    /**
     * @Secure("viewClassificationLibrary")
     */
    public function exportAction()
    {
        session_write_close();
        set_time_limit(0);
        PHPExcel_Settings::setCacheStorageMethod(PHPExcel_CachedObjectStorageFactory::cache_in_memory_gzip);

        /** @var $library ClassificationLibrary */
        $library = ClassificationLibrary::load($this->getParam('id'));

        $date = date(str_replace('&nbsp;', '', __('DW', 'export', 'dateFormat')));
        $filename = $date . '_' . __('Classification', 'classification', 'classification') . '.xls';

        header('Content-type: application/vnd.ms-excel');
        header('Content-Disposition: attachement;filename='.$filename);
        header('Cache-Control: max-age=0');

        Zend_Layout::getMvcInstance()->disableLayout();
        Zend_Controller_Front::getInstance()->setParam('noViewRenderer', true);

        $this->exportService->stream($library, 'xls');
    }
}
