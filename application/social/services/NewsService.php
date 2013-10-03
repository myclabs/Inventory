<?php

/**
 * Service d'accès au news.
 *
 * @author matthieu.napoli
 */
class Social_Service_NewsService
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
