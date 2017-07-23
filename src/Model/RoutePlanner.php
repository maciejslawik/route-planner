<?php
/**
 * File: RoutePlanner.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

namespace MSlwk\RoutePlanner\Model;

use MSlwk\RoutePlanner\Api\GeolocatorInterface;
use MSlwk\RoutePlanner\Api\RoutePlannerInterface;
use MSlwk\RoutePlanner\Api\ShortestPathMethodInteface;
use MSlwk\RoutePlanner\Exception\MissingParametersException;
use MSlwk\RoutePlanner\Model\Algorithm\ShortestPathAlgorithmFactory;
use Symfony\Component\Yaml\Yaml;

/**
 * Class RoutePlanner
 *
 * @package MSlwk\RoutePlanner\Model
 */
class RoutePlanner implements RoutePlannerInterface
{
    private static $availableVehicles = [
        self::VEHICLE_MOTORCYCLE,
        self::VEHICLE_BICYCLE,
        self::VEHICLE_SCOOTER
    ];

    private $from;
    private $to;

    /**
     * @var string
     */
    private $vehicle = self::VEHICLE_MOTORCYCLE;

    /**
     * @var string
     */
    private $method;

    /**
     * @var bool
     */
    private $avoidTracks = true;

    /**
     * @var resource
     */
    private $dbConnection;

    /**
     * @var GeolocatorInterface
     */
    private $geolocator;


    /**
     * RoutePlanner constructor.
     *
     * @param GeolocatorInterface $geolocator
     * @throws MissingParametersException
     */
    public function __construct(GeolocatorInterface $geolocator)
    {
        $config = Yaml::parse(file_get_contents(__DIR__ . '/../../configs/config.yml'));
        if (!$config) {
            throw new MissingParametersException();
        }

        $this->dbConnection = pg_connect(
            "
            host={$config['db']['host']} 
            dbname={$config['db']['routing_db']} 
            user={$config['db']['user']} 
            password={$config['db']['password']}
            "
        );

        $this->geolocator = $geolocator;
    }

    /**
     * @return string
     */
    public function getVehicle(): string
    {
        return $this->vehicle;
    }

    /**
     * @param string $vehicle
     * @return null
     */
    public function setVehicle(string $vehicle)
    {
        if (in_array($vehicle, self::$availableVehicles)) {
            $this->vehicle = $vehicle;
        }
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     * @return null
     */
    public function setFrom(string $from)
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     * @return string
     */
    public function setTo(string $to)
    {
        $this->to = $to;
    }

    /**
     * @return bool
     */
    public function isAvoidTracks(): bool
    {
        return $this->avoidTracks;
    }

    /**
     * @param bool $avoidTracks
     * @return null
     */
    public function setAvoidTracks(bool $avoidTracks)
    {
        $this->avoidTracks = $avoidTracks;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string $method
     * @return null
     */
    public function setMethod(string $method)
    {
        $this->method = $method;
    }

    public function getRoute(): array
    {
        if (!$this->getVehicle() || !$this->getFrom() || !$this->getTo()) {
            throw new MissingParametersException();
        }

        $fromData = $this->geolocator->getCoordinates($this->getFrom());
        $toData = $this->geolocator->getCoordinates($this->getTo());

        $startVertex = $this->getClosestVertice($fromData['lat'], $fromData['lon']);
        $finishVertex = $this->getClosestVertice($toData['lat'], $toData['lon']);

        $shortestPathFinder = $this->getAlgorithmHandler();
        $routeAsIds = $shortestPathFinder->getRoute($startVertex, $finishVertex, $this->getVehicle());
        $routeAsCoordinates = $this->getRouteCoordinates($routeAsIds);
        $length = $this->getRouteLength($routeAsIds);
        $cost = $this->getRouteCost($routeAsIds);
        return [
            'from' => $this->getFrom(),
            'to' => $this->getTo(),
            'vehicle' => $this->getVehicle(),
            'length' => $length,
            'cost' => $cost,
            'route' => $routeAsCoordinates
        ];
    }

    /**
     * @return ShortestPathMethodInteface
     */
    protected function getAlgorithmHandler(): ShortestPathMethodInteface
    {
        return ShortestPathAlgorithmFactory::getAlgorithmHandler($this->method, $this->dbConnection);
    }

    /**
     * @param string $latitude
     * @param string $longitude
     * @return string
     */
    protected function getClosestVertice(string $latitude, string $longitude): string
    {
        $sql = "
            SELECT id
            FROM ways_vertices_pgr 
            ORDER BY the_geom <-> ST_GeometryFromText('POINT({$latitude} {$longitude})',4326) LIMIT 1;";

        $result = pg_query($this->dbConnection, $sql);
        return pg_fetch_all($result)[0]['id'];
    }

    /**
     * @param array $routeAsIds
     * @return array
     */
    protected function getRouteCoordinatesFromIds(array $routeAsIds): array
    {
        $route = [];
        foreach ($routeAsIds as $extract) {
            $sql = "SELECT x1, y1 FROM ways WHERE source = {$extract['node']} LIMIT 1";
            $result = pg_query($this->dbConnection, $sql);
            $result = pg_fetch_all($result);
            $route[] = [$result[0]['y1'], $result[0]['x1']];
        }
        return $route;
    }

    /**
     * @param array $routeAsIds
     * @return float
     */
    protected function getRouteLength(array $routeAsIds): float
    {
        $length = 0.0;
        foreach ($routeAsIds as $extract) {
            $sql = "SELECT length FROM ways WHERE source = {$extract['node']} LIMIT 1";
            $result = pg_query($this->dbConnection, $sql);
            $result = pg_fetch_all($result);
            $length += $result[0]['length'];
        }
        return $length;
    }

    /**
     * @param array $route
     * @return float
     */
    protected function getRouteCost(array $route): float
    {
        $cost = 0.0;
        foreach ($route as $extract) {
            $cost += $extract['cost'];
        }
        return $cost;
    }
}
