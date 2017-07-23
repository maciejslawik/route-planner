<?php
/**
 * File: AstarAlgorithm.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Model\Algorithm;

use MSlwk\RoutePlanner\Api\ShortestPathMethodInteface;

/**
 * Class AstarAlgorithm
 *
 * @package MSlwk\RoutePlanner\Model\Algorithm
 */
class AstarAlgorithm extends AbstractAlgorithm implements ShortestPathMethodInteface
{
    /**
     * @var bool
     */
    protected $includeAdditionalCosts;

    /**
     * @param bool $inludeAdditionalCosts
     * @return null
     */
    public function setIncludeAdditionalCosts(bool $inludeAdditionalCosts)
    {
        $this->includeAdditionalCosts = $inludeAdditionalCosts;
    }

    /**
     * @return bool
     */
    public function getIncludeAdditionalCosts(): bool
    {
        return $this->includeAdditionalCosts;
    }

    /**
     * @param string $startVertex
     * @param string $finishVertex
     * @param string $vehicle
     * @return array
     */
    public function getRoute(string $startVertex, string $finishVertex, string $vehicle): array
    {
        $query = $this->getQuery($startingVertex, $finishVertex, $vehicle);
        $result = pg_query($this->dbConnection, $query);
        return pg_fetch_all($result);
    }

    /**
     * @param string $startingVertex
     * @param string $finishVertex
     * @param string $vehicle
     * @return string
     */
    protected function getQuery(string $startingVertex, string $finishVertex, string $vehicle)
    {
        $additional = '';
        if ($this->getIncludeAdditionalCosts()) {
            $additional = '*additional_cost';
        }

        return "
            SELECT seq, id1 AS node, id2 AS edge, cost
            FROM pgr_astar(
                'SELECT 
                    gid as id,
                    source,
                    target,
                    ({$vehicle}_cost*to_cost{$additional}) as cost,
                    x1,
                    y1,
                    x2,
                    y2, 
                    ({$vehicle}_cost*reverse_cost{$additional}) as reverse_cost
                FROM ways',
                {$startingVertex}, {$finishVertex}, true, true
            );
        ";
    }
}
