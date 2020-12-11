CREATE TABLE users (
    id SERIAL,
    email VARCHAR(50) UNIQUE NOT NULL,
    pswd VARCHAR(60) NOT NULL,/*size of encrypted password*/
    phone VARCHAR(15) NOT NULL,
    firstName VARCHAR(50) NOT NULL,
    lastName VARCHAR(50) NOT NULL,
    isApproved SMALLINT DEFAULT 0,
    lastLogin VARCHAR(20),
    userRole VARCHAR(20) NOT NULL,/*Correspond au utilisateur, validateur, annotateur*/
    PRIMARY KEY(id)
);
CREATE TABLE genome (
    id SERIAL,
    chromID VARCHAR(10) UNIQUE NOT NULL,
    loc VARCHAR(20) NOT NULL,
    sequence VARCHAR,
    name VARCHAR(70) UNIQUE NOT NULL,
    PRIMARY KEY(id)
);
CREATE TABLE cds (
    id SERIAL,
    cdsId VARCHAR(8) UNIQUE NOT NULL, 
    chromId VARCHAR(10) NOT NULL,
    location VARCHAR(20) NOT NULL, 
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(chromId) REFERENCES genome(chromId)
);
CREATE TABLE pep (
    id SERIAL,
    pepId VARCHAR(8) UNIQUE NOT NULL,
    chromId VARCHAR(10) NOT NULL,
    location VARCHAR(20) NOT NULL, 
    sequence VARCHAR,
    PRIMARY KEY(id),
    FOREIGN KEY(chromID) REFERENCES genome(chromID),
    FOREIGN KEY(pepId) REFERENCES cds(cdsID)

);
CREATE table annot(
    id SERIAL,
    annotID VARCHAR(8) UNIQUE NOT NULL,
    geneID VARCHAR(8),
    transcript VARCHAR(8),
    geneType VARCHAR(15), 
    transcryptType VARCHAR, 
    symbol VARCHAR(15),
    description VARCHAR(200),
    validated SMALLINT,/*validate the annotations*/
    upreview SMALLINT,/*Indicate the annotations are ready to be reviewed*/
    annotator INTEGER,
    PRIMARY KEY(id),
    FOREIGN KEY(annotID) REFERENCES pep(pepId),
    FOREIGN KEY(annotator) REFERENCES users(id)
);
