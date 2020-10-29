/*
sudo -i -u postgres
CREATE DATABASE db;
*/
CREATE TABLE users (
            id SERIAL,
            email VARCHAR(50) UNIQUE,
            pswd VARCHAR(500),/*for encrypted password*/
            phone VARCHAR(15),
            firstName VARCHAR(100),
            lastName VARCHAR(100),
            userType VARCHAR(15),
            isApproved SMALLINT DEFAULT 0,
            lastLogin VARCHAR(20),
            userRole VARCHAR(20),
            PRIMARY KEY(id)
        );
CREATE TABLE genome (
    id SERIAL,
    chromID VARCHAR UNIQUE,
    loc VARCHAR,
    sequence VARCHAR,
    isAnnotated SMALLINT DEFAULT 0,
    name VARCHAR(70),
    PRIMARY KEY(id)
);
CREATE TABLE cds (
    id SERIAL,
    cdsId VARCHAR UNIQUE, 
    chromId VARCHAR,
    location VARCHAR, 
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(chromId) REFERENCES genome(chromId)
);
CREATE TABLE pep (
    id SERIAL,
    pepId VARCHAR UNIQUE,
    chromId VARCHAR,
    location VARCHAR, 
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(chromID) REFERENCES genome(chromID),
    FOREIGN KEY(pepId) REFERENCES cds(cdsID)

);
CREATE table annot(
    id SERIAL,
    annotID VARCHAR,
    geneID VARCHAR,
    transcript VARCHAR,
    geneType VARCHAR, 
    transcryptType VARCHAR, 
    symbol VARCHAR,
    description VARCHAR,
    validated SMALLINT,
    annotator INTEGER,
    PRIMARY KEY(id),
    FOREIGN KEY(annotID) REFERENCES pep(pepId),
    FOREIGN KEY(annotator) REFERENCES users(id)
);
/*
ALTER TABLE users
ADD COLUMN isApproved SMALLINT DEFAULT 0;
*/
/*export data */
\copy (select sequence, id from pep) to '/home/trambaud/Documents/test.csv' with csv delimiter ';' header;

/* create the first administrator and validate the account*/
UPDATE users 
SET usertype='admin' 
WHERE email='admin@email.com';

UPDATE users
SET usertype='admin', isApproved=1
WHERE email='admin@email.com';

/*Query on text Search*/
select pepid from pep, annot where pepid=annotid and to_tsvector('english',  pepid) @@ to_tsquery('english', 'AAN78501');
select pepid from pep, annot where pepid=annotid and to_tsvector('english',  pepid||' '|| symbol) @@ to_tsquery('english', 'thrL & AAN78501');
SELECT id, chromid, name from genome where to_tsvector('english', chromid ||' '|| name ||' '|| loc) @@ plainto_tsquery();


SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome WHERE to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid) @@ plainto_tsquery('new')
INTERSECT 
SELECT pep.id, pep.chromid, name, location, pepid FROM pep, genome WHERE pep.sequence ILIKE '%%';

SELECT DISTINCT annotid, name, pep.id as pid, genome.id as gid, geneid, transcript, genetype, transcrypttype, symbol, description 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND annotator=80 AND validated=0;

 SELECT count(pep.id) FROM pep, genome WHERE pep.chromid=genome.chromid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| location ||' '||pepid) @@ plainto_tsquery('%new%');