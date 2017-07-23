<?php
/**
 * File: classes_costs.php
 *
 * @author      Maciej Sławik <maciekslawik@gmail.com>
 * Github:      https://github.com/maciejslawik
 */

require_once(__DIR__ . '/../../../../autoload.php');

use Symfony\Component\Yaml\Yaml;

$db_config = Yaml::parse(file_get_contents(__DIR__ . '/../../configs/config.yml'));

$configs = [
    'motorcycle' => json_decode(file_get_contents(__DIR__ . '/../../configs/motorcycle_costs.json')),
    'bicycle' => json_decode(file_get_contents(__DIR__ . '/../../configs/bicycle_costs.json')),
    'scooter' => json_decode(file_get_contents(__DIR__ . '/../../configs/scooter_costs.json')),
];

$routing_db_connection = pg_connect(
    "
            host={$db_config['db']['host']} 
            dbname={$db_config['db']['routing_db']} 
            user={$db_config['db']['user']} 
            password={$db_config['db']['password']}
            "
);

foreach ($configs as $vehicle => $costs) {
    $sql = '';
    /**
     * Updates classes table with costs for each vehicle from parsed .ymls
     */
    foreach ($costs as $cost) {
        $sql .= "UPDATE classes SET {$vehicle}_cost = {$cost->cost} WHERE id = {$cost->id};";
    }
    pg_query($routing_db_connection, $sql);

    /**
     * Stores calculated product of costs in database for
     * speed optimization
     */
    $sql = "CREATE TEMPORARY TABLE {$vehicle}_costs AS
      SELECT gid, EXP(SUM(LN(c.{$vehicle}_cost))) as product 
      FROM ways w JOIN way_tag t ON (w.gid = t.way_id) JOIN classes c ON (t.class_id = c.id) 
      GROUP BY gid;

      UPDATE ways SET {$vehicle}_cost = product FROM {$vehicle}_costs WHERE {$vehicle}_costs.gid = ways.gid";

    pg_query($routing_db_connection, $sql);
}
