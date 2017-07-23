<?php
/**
 * File: RoutePlannerInterface.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Api;

/**
 * Interface RoutePlannerInterface
 *
 * @package MSlwk\RoutePlanner\Api
 *
 */
interface RoutePlannerInterface
{
    const VEHICLE_MOTORCYCLE = 'motorcycle';
    const VEHICLE_BICYCLE = 'bicycle';
    const VEHICLE_SCOOTER = 'scooter';

    /**
     * @return string
     */
    public function getVehicle(): string;

    /**
     * @param string $vehicle
     * @return null
     */
    public function setVehicle(string $vehicle);

    /**
     * @return string
     */
    public function getFrom(): string;

    /**
     * @param string $from
     * @return null
     */
    public function setFrom(string $from);

    /**
     * @return string
     */
    public function getTo(): string;

    /**
     * @param string $to
     * @return null
     */
    public function setTo(string $to);

    /**
     * @return boolean
     */
    public function isAvoidTracks(): bool;

    /**
     * @param bool $avoidTracks
     * @return null
     */
    public function setAvoidTracks(bool $avoidTracks);

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string $method
     * @return null
     */
    public function setMethod(string $method);

    /**
     * @return array
     */
    public function getRoute(): array;
}
