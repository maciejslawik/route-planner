[![Latest Stable Version](https://poser.pugx.org/mslwk/route-planner/v/stable)](https://packagist.org/packages/mslwk/route-planner)
[![Latest Unstable Version](https://poser.pugx.org/mslwk/route-planner/v/unstable)](https://packagist.org/packages/mslwk/route-planner)
[![License](https://poser.pugx.org/mslwk/route-planner/license)](https://packagist.org/packages/mslwk/route-planner)

# Profiled route planner #

The project finds a route well-suited for the requested type of vehicle in an urban environment.
It uses PostgreSQL with additional extensions.
##### Supported vehicles #####
* motorcycle
* scooter
* bicycle


## System requirements ##
* PostgreSQL 9.6
* PostGIS 2.2
* pgRouting 2.2
* osm2pgrouting 2.0
* osm2pgsql 0.8
* PHP 7.1 with pgsql

## Installation ##

1. Use composer to include the library in your project.

```
composer require mslwk/route-planner
```

2. After cloning the repository download a city extract in .osm file and place it in 
``
vendor/mslwk/route-planner/maps/map.osm
``

3. Create configs/config.yml file based on configs/config.example.yml
4. Run deployment/deploy.sh script

## Detailed description ##

The library utilises two PostgreSQL databases to find the best route between two points.
One database is a complete set of information from OSM and is used e.g. to find 
streets which with trams. The second one is pgRouting database which represents a city
 as a weighted graph and calculates the route.
 
 Nominatim API is used to find the coordinates between the given addresses. The coordinates
 are used to find the graph vertices that are closest to the addresses.
 
 The library supports two shortest-path algorithms (Dijkstra/A*).
 
 The route is returned as an array of coordinates.
 
 The data calculated is a json object.
 
 ##### Example of a successfully calculated route (from JSON API) #####
 
 ![Alt text](docs/success.png?raw=true "Route found")
 
 ##### Example of a failure (from JSON API) #####
 
 ![Alt text](docs/failure.png?raw=true "Route found")
  
  
## Live examples ##

The examples show a route calculated using the library and displayed using
Google Maps. 

#### Motorcycle ####

 ![Alt text](docs/example_motorcycle.png?raw=true)

#### Bicycle ####

 ![Alt text](docs/example_bicycle.png?raw=true)
 
#### Scooter ####
 
 ![Alt text](docs/example_scooter.png?raw=true)