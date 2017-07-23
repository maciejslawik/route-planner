<?php
/**
 * File: ShortestPathAlgorithmFactory.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Model\Algorithm;

use MSlwk\RoutePlanner\Api\ShortestPathMethodInteface;
use MSlwk\RoutePlanner\Exception\AlgorithmNotSupportedException;

/**
 * Class ShortestPathAlgorithmFactory
 *
 * @package MSlwk\RoutePlanner\Model\Algorithm
 */
class ShortestPathAlgorithmFactory
{
    const DIJKSTRA = 'dijkstra';
    const ASTAR = 'astar';

    /**
     * @param string $algorithm
     * @param resource $dbConnection
     * @return ShortestPathMethodInteface
     * @throws AlgorithmNotSupportedException
     */
    public static function getAlgorithmHandler(string $algorithm, resource $dbConnection): ShortestPathMethodInteface
    {
        switch ($algorithm) {
            case self::DIJKSTRA:
                return new DijkstraAlgorithm($dbConnection);
            case self::ASTAR:
                return new AstarAlgorithm($dbConnection);
            default:
                throw new AlgorithmNotSupportedException();
        }
    }
}
