CREATE TEMPORARY TABLE tracks_polygons AS
  SELECT
  f.osm_id as osm_id,
  geometry(ST_Buffer(geography(f.way), 5)) as way
  FROM
  public.planet_osm_roads f WHERE railway = 'tram';

CREATE TABLE streets_with_tracks AS
SELECT a.osm_id as osm_id, a.way as way
  FROM planet_osm_roads AS a, tracks_polygons AS b
  WHERE st_intersects(a.way, b.way) AND a.railway IS NULL
  GROUP BY a.osm_id, a.way;