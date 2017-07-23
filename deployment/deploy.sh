#!/bin/sh
#------------------------------------------------------------------------------------
# Shell script to automate the deployment of the project
#------------------------------------------------------------------------------------

# Function used to get config from .yml
parse_yaml() {
   local prefix=$2
   local s='[[:space:]]*' w='[a-zA-Z0-9_]*' fs=$(echo @|tr @ '\034')
   sed -ne "s|^\($s\)\($w\)$s:$s\"\(.*\)\"$s\$|\1$fs\2$fs\3|p" \
        -e "s|^\($s\)\($w\)$s:$s\(.*\)$s\$|\1$fs\2$fs\3|p"  $1 |
   awk -F$fs '{
      indent = length($1)/2;
      vname[indent] = $2;
      for (i in vname) {if (i > indent) {delete vname[i]}}
      if (length($3) > 0) {
         vn=""; for (i=0; i<indent; i++) {vn=(vn)(vname[i])("_")}
         printf("%s%s%s=\"%s\"\n", "'$prefix'",vn, $2, $3);
      }
   }'
}

START_COMMENT = "\n\n***** ";
STOP_COMMENT = " *****\n\n";


# Absolute path of current script
CURRENT_DIR=$(dirname $0);

# Generates config variables from yaml
eval $(parse_yaml $CURRENT_DIR/../configs/config.yml "config_");

if [ "$config_db_password" != "" ]
    then
        export PGPASSWORD=$config_db_password
fi


# Deploy base routing database
echo "$START_COMMENT Creating routing database $STOP_COMMENT";
psql -U $config_db_user -H $config_db_host -d postgres -c "DROP DATABASE IF EXISTS $config_db_routing_db;";
psql -U $config_db_user -H $config_db_host -d postgres -c "CREATE DATABASE $config_db_routing_db";
psql -U $config_db_user -H $config_db_host -d $config_db_routing_db -c "CREATE EXTENSION postgis;CREATE EXTENSION pgrouting;";
osm2pgrouting -file $CURRENT_DIR/../maps/map.osm -host $config_db_host -user $config_db_user -dbname $config_db_routing_db -conf $CURRENT_DIR/../configs/mapconfig.xml;

echo "$START_COMMENT Running additional routing database scripts $STOP_COMMENT";
psql -U $config_db_user -d $config_db_routing_db -a -f $CURRENT_DIR/scripts/routing_db_script.sql;

# Deploy full OSM database
echo "$START_COMMENT Creating full OSM database $STOP_COMMENT";
psql -U $config_db_user -d postgres -c "DROP DATABASE IF EXISTS $config_db_full_db;";
psql -U $config_db_user -d postgres -c "CREATE DATABASE $config_db_full_db";
psql -U $config_db_user -d $config_db_full_db -c "CREATE EXTENSION postgis;CREATE EXTENSION postgis_topology;CREATE EXTENSION hstore;";
osm2pgsql -U $config_db_user -d $config_db_full_db -H $config_db_host -k -l $CURRENT_DIR/../maps/map.osm
echo "$START_COMMENT Running additional full OSM database scripts $STOP_COMMENT";
psql -U $config_db_user -d $config_db_full_db -a -f $CURRENT_DIR/scripts/full_db_script.sql;

# Update additional tracks cost
echo "$START_COMMENT Generating additional costs for ways with tracks $STOP_COMMENT";
php $CURRENT_DIR/scripts/tracks_costs.php
# Calculate ways costs
echo "$START_COMMENT Calculating ways costs for each vehicle $STOP_COMMENT";
php $CURRENT_DIR/scripts/classes_costs.php

echo "$START_COMMENT Databases were successfully deployed $STOP_COMMENT";
