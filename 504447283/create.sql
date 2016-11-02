-- Movie table
CREATE TABLE Movie (
	id INT NOT NULL,
	title VARCHAR(100) NOT NULL,
	year INT NOT NULL,
	rating VARCHAR(10),
	company VARCHAR(50),
	PRIMARY KEY(id)
) ENGINE = INNODB;

-- Actor table
CREATE TABLE Actor (
	id INT NOT NULL,
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	sex VARCHAR(6) NOT NULL,
	dob DATE NOT NULL,
	dod DATE,
	PRIMARY KEY(id),
	CHECK((dod IS NOT NULL AND dob < dod)OR (dod IS NULL))
) ENGINE = INNODB;

-- Director table
CREATE TABLE Director(
	id INT NOT NULL, 
	last VARCHAR(20) NOT NULL,
	first VARCHAR(20) NOT NULL,
	dob DATE NOT NULL,
	dod DATE,
	PRIMARY KEY(id),
	CHECK((dod IS NOT NULL AND dob < dod)OR (dod IS NULL))
) ENGINE = INNODB;

-- MovieGenre table
CREATE TABLE MovieGenre(
	mid INT NOT NULL,
	genre VARCHAR(20) NOT NULL,
	UNIQUE(mid,genre),
	FOREIGN KEY(mid) references Movie(id)
) ENGINE = INNODB;

-- MovieDirector table
CREATE TABLE MovieDirector(
	mid INT NOT NULL, 
	did INT NOT NULL,
	FOREIGN KEY(mid) references Movie(id),
	FOREIGN KEY(did) references Director(id),
	UNIQUE(mid,did)
) ENGINE = INNODB;

-- MovieActor table
CREATE TABLE MovieActor(
	mid INT NOT NULL,
	aid INT NOT NULL,
	role VARCHAR(50) NOT NULL,
	FOREIGN KEY(mid) references Movie(id), 
	-- Each MovieActor MUST correspond to an extant movie.
	FOREIGN KEY(aid) references Actor(id),
	UNIQUE(mid,aid,role)
) ENGINE = INNODB;

-- Review table
CREATE TABLE Review(
	name VARCHAR(20) NOT NULL,
	time TIMESTAMP NOT NULL,
	mid INT NOT NULL, 
	rating INT,	
	comment VARCHAR(500),
	FOREIGN KEY(mid) references Movie(id),
	UNIQUE(name,mid),
	CHECK((rating IS NULL) OR (rating>=0 AND rating<=5)) 
	
) ENGINE = INNODB;

-- MaxPersonID table
CREATE TABLE MaxPersonID(
	id INT NOT NULL
) ENGINE = INNODB;

-- MaxMovieID table
CREATE TABLE MaxMovieID(
	id INT NOT NULL
) ENGINE = INNODB;