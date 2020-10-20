/*
sudo -i -u postgres
CREATE USER admin;
ALTER ROLE admin WITH ENCRYPTED PASSWORD 'adminpswd';
sudo -i -u postgres
dbname=db
user=postgres
pswd=postgres
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
    PRIMARY KEY(id)
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
    PRIMARY KEY(id) 
);

COPY genome(name, lo, test, sequence, tt, ttt) 
FROM '/home/trambaud/Documents/PW/Projet Web-20201011/data/Escherichia_coli_cft073.fa'
delimiter ':';


COPY genome 
FROM '/home/trambaud/Documents/PW/Projet Web-20201011/data/Escherichia_coli_cft073.fa '|STDIN;
/*
ALTER TABLE users
ADD COLUMN isApproved SMALLINT DEFAULT 0;
*/
INSERT INTO users
VALUES (DEFAULT, 'admin@email.com','pswd', '0123456789', 'admin', 'strator', 'ADMIN' );
/* create the first administrator */
UPDATE users 
SET usertype='admin' 
WHERE email='admin@email.com';

UPDATE users
SET usertype='admin', isApproved=1
WHERE email='admin@email.com';

/*
user login check if credentials in the database
if yes redirect to page;
*/