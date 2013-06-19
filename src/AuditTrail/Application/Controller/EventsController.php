<?php
/**
 * @author  matthieu.napoli
 * @author  hugo.charbonnier
 * @author  yoann.croizer
 * @package AF
 */

use AuditTrail\Domain\EntryRepository;
use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * Countroleur des AF
 * @package AF
 */
class AuditTrail_EventsController extends Core_Controller
{
    /**
     * @Inject
     * @var EntryRepository
     */
    private $entryRepository;

    /**
     * Recent events
     * @Secure("public")
     */
    public function recentAction()
    {
        $entries = $this->entryRepository->findAll();

        $this->view->assign('entries', $entries);
    }
}
