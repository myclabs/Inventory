<?php
/**
 * @author  matthieu.napoli
 * @package Core
 */

/**
 * Représente une tâche abstraite
 *
 * @package Core
 */
abstract class Core_Work_Task
{
    /**
     * @var Core_Work_TaskContext|null
     */
    private $context;

    /**
     * @param Core_Work_TaskContext|null $context
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * @return Core_Work_TaskContext|null
     */
    public function getContext()
    {
        return $this->context;
    }
}
