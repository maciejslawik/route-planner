<?php
/**
 * File: tracks_costs.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */


require_once(__DIR__ . '/../../../../autoload.php');

use Symfony\Component\Yaml\Yaml;

/**
 * Additional cost of tracks in a street
 */
define('TRACKS_ADDITIONAL_COST', 2.0);


$config = Yaml::parse(file_get_contents(__DIR__ . '/../../configs/config.yml'));

$full_db_connection = pg_connect(
    "
            host={$config['db']['host']} 
            dbname={$config['db']['full_db']} 
            user={$config['db']['user']} 
            password={$config['db']['password']}
            "
);

$routing_db_connection = pg_connect(
    "
            host={$config['db']['host']} 
            dbname={$config['db']['routing_db']} 
            user={$config['db']['user']} 
            password={$config['db']['password']}
            "
);

$sql = 'SELECT osm_id FROM streets_with_tracks';
$result = pg_query($full_db_connection, $sql);
$result = pg_fetch_all($result);

$streetsWithTracks = '';
for ($i = 0; $i < count($result) - 2; $i++) {
    $streetsWithTracks .= $result[$i]['osm_id'] . ',';
}
$streetsWithTracks .= $result[count($result) - 1]['osm_id'];

$sql = "UPDATE ways SET additional_cost = ".TRACKS_ADDITIONAL_COST." WHERE osm_id IN ({$streetsWithTracks});";

pg_query($routing_db_connection, $sql);
