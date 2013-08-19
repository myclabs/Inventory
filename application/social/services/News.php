<?php
/**
 * @author matthieu.napoli
 * @package Social
 * @subpackage Service
 */

/**
 * @package Social
 * @subpackage Service
 */
class Social_Service_News
{

    /**
     * @param int $count
     * @return Social_Model_News[]
     */
    public function getLatestNews($count)
    {
        $query = new Core_Model_Query();
        $query->order->addOrder(Social_Model_News::QUERY_PUBLICATION_DATE, Core_Model_Order::ORDER_DESC);
        $query->totalElements = $count;
        $news = Social_Model_News::loadList($query);
        return $news;
    }

}
