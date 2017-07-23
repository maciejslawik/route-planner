<?php
/**
 * File: ShortestPathMethodInteface.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Api;

/**
 * Interface ShortestPathMethodInteface
 *
 * @package MSlwk\RoutePlanner\Api
 */
interface ShortestPathMethodInteface
{
    /**
     * @param string $startVertex
     * @param string $finishVertex
     * @param string $vehicle
     * @return array
     */
    public function getRoute(string $startVertex, string $finishVertex, string $vehicle): array;

    /**
     * @param bool $inludeAdditionalCosts
     * @return null
     */
    public function setIncludeAdditionalCosts(bool $inludeAdditionalCosts);

    /**
     * @return bool
     */
    public function getIncludeAdditionalCosts(): bool;
}
