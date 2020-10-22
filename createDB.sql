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




update genome
set isAnnotated=1
from genome, pep, annot
where genome.chromID=pep.chromID
and pep.pepid=annot.annotID
and annot.validated=1
;


/* old tables
CREATE TABLE users (
            id SERIAL,
            email VARCHAR(50) UNIQUE,
            pswd VARCHAR(500),
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
    GeneID VARCHAR UNIQUE,
    loc VARCHAR,
    sequence VARCHAR,
    PRIMARY KEY(id)
);
CREATE TABLE cds (
    id SERIAL,
    cdsId VARCHAR UNIQUE, 
    seqId VARCHAR,
    location VARCHAR, 
    geneId VARCHAR,
    geneType VARCHAR, 
    transcriptType VARCHAR, 
    symbol VARCHAR,
    description VARCHAR,
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(seqId) REFERENCES genome(GeneId)
);
CREATE TABLE pep (
    id SERIAL,
    pepId VARCHAR UNIQUE,
    seqId VARCHAR,
     location VARCHAR, 
    geneId VARCHAR,
    transcript VARCHAR,
    geneType VARCHAR, 
    transcryptType VARCHAR, 
    symbol VARCHAR,
    description VARCHAR,
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(seqID) REFERENCES genome(GeneID),
    FOREIGN KEY(geneId) REFERENCES cds(geneId),
    FOREIGN KEY(pepId) REFERENCES cds(cdsID)

);
*/
