<?php
/**
 * @package Social
 */

/**
 * @author  joseph.rouffet
 * @author  matthieu.napoli
 * @package Social
 */
class Social_Model_Comment extends Social_Model_Text
{

    /**
     * {@inheritdoc}
     * @param User_Model_User $author Auteur (obligatoire)
     */
    public function __construct(User_Model_User $author)
    {
        parent::__construct($author);
    }

}
