-- 1. Get movieid from table movie
-- 2. Get all actorids from table movieactor
-- 3. Get first last from table actor
-- This is achieved by checking to make sure the title matches,
-- then by making sure the id's match, then by returning the
-- concatenated name

SELECT CONCAT(A.first, ' ', A.last)
FROM Actor A, MovieActor MA, Movie M
WHERE M.title = 'Die Another Day' AND MA.mid = M.id AND MA.aid = A.id;

-- Get the count of all of the actors 
-- minus the ones who have only acted once
SELECT COUNT(DISTINCT M1.aid)
FROM MovieActor M1, MovieActor M2
WHERE NOT (M1.aid<>M2.aid OR M1.mid=M2.mid);

-- Average age of all actors in the database who have died already
-- This was achieved by find the difference in years between the
-- death and birth date
SELECT AVG(YEAR(A.dod) - YEAR(A.dob))
FROM Actor A
WHERE A.dod IS NOT NULL;
