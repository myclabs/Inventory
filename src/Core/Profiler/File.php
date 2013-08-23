<?php

use Psr\Log\LoggerInterface;

/**
 * Log des requêtes SQL dans un fichier
 *
 * @author     matthieu.napoli
 */
class Core_Profiler_File implements Doctrine\DBAL\Logging\SQLLogger
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    private $timeStart;


    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function startQuery($sql, array $params = null, array $types = null)
    {
        $this->logger->debug($sql);
        $this->logger->debug("\tParameters: " . json_encode($params) . " of types: " . json_encode($types));

        $this->timeStart = microtime(true);
    }

    /**
     * {@inheritdoc}
     */
    public function stopQuery()
    {
        $time = round((microtime(true) - $this->timeStart) * 1000., 2);

        $this->logger->debug("\tTime: $time ms" . PHP_EOL);

        if ($time > 100) {
            $this->logger->warning("WARNING: Query time over 100ms" . PHP_EOL);
        }
    }
}
