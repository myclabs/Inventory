<?php
/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */

use Core\Annotation\Secure;
use DI\Annotation\Inject;

/**
 * @package Social
 */
class Social_Datagrid_Message_OutboxController extends UI_Controller_Datagrid
{

    /**
     * @Inject
     * @var Social_Service_Message
     */
    private $messageService;

    /**
     * {@inheritdoc}
     * @Secure("loggedIn")
     */
    public function getelementsAction()
    {
        $currentUser = $this->_helper->auth();

        $messages = $this->messageService->getUserOutbox($currentUser, $this->request->totalElements);
        $this->totalElements = $this->messageService->getUserOutboxSize($currentUser);

        foreach ($messages as $message) {
            $data = [];

            $data['author'] = $this->cellText($message->getAuthor()->getName());

            // Message
            $title = $message->getTitle();
            if (strlen($title) == 0) {
                $title = __('Social', 'datagrids', 'noObject');
            } else {
                $title = Core_Tools::truncateString($title, 30);
            }
            $data['message'] = $this->cellPopup('social/message/view/id/' . $message->getId(), $title, 'zoom-in');

            // Destinataire
            $userRecipients = $message->getUserRecipients();
            if (count($userRecipients) > 0) {
                $recipient = $userRecipients[0];
            } else {
                $groupRecipients = $message->getGroupRecipients();
                if (count($groupRecipients) > 0) {
                    $recipient = $groupRecipients[0];
                }
            }
            if ($recipient instanceof Social_Model_UserGroup) {
                $urlPopup = 'social/user-group/details/id/' . $recipient->getId();
                $data['recipient'] = $this->cellPopup($urlPopup, $recipient->getLabel(), 'zoom-in');
            } else {
                $urlPopup = 'user/profile/see/id/' . $recipient->getId();
                $data['recipient'] = $this->cellPopup($urlPopup, $recipient->getName(), 'zoom-in');
            }

            $creationDate = $message->getCreationDate();
            $data['date'] = $this->cellDate($creationDate, $creationDate->format('d M Y - H:m'));

            $this->addLine($data);
        }

        $this->send();
    }

    /**
     * {@inheritdoc}
     */
    public function addelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

    /**
     * {@inheritdoc}
     */
    public function updateelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

    /**
     * {@inheritdoc}
     */
    public function deleteelementAction()
    {
        throw new Core_Exception_InvalidHTTPQuery('action impossible');
    }

}
