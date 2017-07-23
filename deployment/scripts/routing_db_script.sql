UPDATE public.ways
SET 
    length = ST_Length(the_geom::geography),
    reverse_cost = CASE WHEN length >= reverse_cost THEN ST_Length(the_geom::geography) ELSE ST_Length(the_geom::geography) * 1000000 END,
    to_cost = CASE WHEN length <= reverse_cost THEN ST_Length(the_geom::geography) ELSE ST_Length(the_geom::geography) * 1000000 END;

ALTER TABLE public.ways
    ADD COLUMN additional_cost DOUBLE PRECISION DEFAULT 1.0,
    ADD COLUMN motorcycle_cost DOUBLE PRECISION DEFAULT 1.0,
    ADD COLUMN bicycle_cost DOUBLE PRECISION DEFAULT 1.0,
    ADD COLUMN scooter_cost DOUBLE PRECISION DEFAULT 1.0;

ALTER TABLE public.classes
    DROP COLUMN cost;

ALTER TABLE public.classes
    ADD COLUMN motorcycle_cost DOUBLE PRECISION,
    ADD COLUMN bicycle_cost DOUBLE PRECISION,
    ADD COLUMN scooter_cost DOUBLE PRECISION;
