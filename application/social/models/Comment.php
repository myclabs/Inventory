<?php
use User\Domain\User;

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
     * @param User $author Auteur (obligatoire)
     */
    public function __construct(User $author)
    {
        parent::__construct($author);
    }
}
