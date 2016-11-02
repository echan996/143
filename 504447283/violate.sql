
-- PRIMARY KEY CONSTRAINTS

-- Movie must have non null Primary ID.
INSERT INTO Movie
	VALUES(NULL, 'Silence of the Jays', 2016, 'R', 'Dreamworks');
-- ERROR 1048 (23000): Column 'id' cannot be null

-- Movie must have unique Primary ID.
INSERT INTO Movie
	VALUES(1, 'Silence of the Jays', 2016, 'R', 'Dreamworks');
-- ERROR 1062 (2300): Duplicate entry '1' for key 'PRIMARY'
	
-- Actor must have unique primary key. 
INSERT INTO Actor
	VALUES(1, 'for your sins', 'Harambe died ', 'male', 2004, 2016);
-- ERROR 1062 (2300): Duplicate entry '1' for key 'PRIMARY'
	
-- Actor must have non null Primary ID.
INSERT INTO Actor
	VALUES(NULL, 'for your sins', 'Harambe died ', 'male', 2004, 2016);
-- ERROR 1048 (23000): Column 'id' cannot be null
	
-- All other tables have similar checks, as well as checks on fields within the table to ensure data integrity. 


-- REFERENTIAL INTEGRITY

-- mid must exist in Movie table.
INSERT INTO MovieGenre
	VALUES(0, 'Horror');
-- ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`TEST`.`MovieGenre`, CONSTRAINT `MovieGenre_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- mid must exist in Movie table.
INSERT INTO MovieDirector
	VALUES(0,1);
-- ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`TEST`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- did must exist in Director table.
INSERT INTO MovieDirector
	VALUES(1,0);
-- ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`TEST`.`MovieDirector`, CONSTRAINT `MovieDirector_ibfk_2` FOREIGN KEY (`did`) REFERENCES `Director` (`id`))


-- mid must exist in Movie table.
INSERT INTO MovieActor
	VALUES(0,1);
-- ERROR 1136 (21S01): Column count doesn't match value count at row 1

-- aid must exist in Actor table.
INSERT INTO MovieActor
	VALUES(1,0);
-- ERROR 1136 (21S01): Column count doesn't match value count at row 1

-- mid must exist in Movie table.
INSERT INTO Review
	VALUES('Eric Chan', 1, 0, 1, '7.8/10 too much water.');
-- ERROR 1452 (23000): Cannot add or update a child row: a foreign key constraint fails (`TEST`.`Review`, CONSTRAINT `Review_ibfk_1` FOREIGN KEY (`mid`) REFERENCES `Movie` (`id`))

-- CHECK CONSTRAINTS

-- actor can either be alive or must have died after DOB.
INSERT INTO Actor
	VALUES(1, 'for your sins', 'Harambe died ', 'male', 2016, 0);

-- Director can either be alive or must have died after DOB.
INSERT INTO Director
	VALUES(1, 'Spielberg', 'Steven', 1990, 0);
	
-- Review must have either no rating or between the preset bounds of 0 and 5.
INSERT INTO Review
	VALUES("Eric Chan", 1, 1, 7, '7.8/10 too much water.');
	