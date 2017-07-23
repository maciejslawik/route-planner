<?php
/**
 * File: GeolocatorInterface.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Api;

/**
 * Interface GeolocatorInterface
 *
 * @package MSlwk\RoutePlanner\Api
 */
interface GeolocatorInterface
{
    /**
     * @param string $address
     * @return array
     */
    public function getCoordinates(string $address): array;
}
