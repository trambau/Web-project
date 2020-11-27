/*
sudo -i -u postgres
CREATE DATABASE db;
sudo -u postgres psql
psql=# alter user <username> with encrypted password '<password>';
*/
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
/*
ALTER TABLE users
ADD COLUMN isApproved SMALLINT DEFAULT 0;
*/
/*export data */
\copy (select sequence, id from pep) to '/home/trambaud/Documents/test.csv' with csv delimiter ';' header;

/* create the first administrator and validate the account*/
UPDATE users
SET userrole='admin', isApproved=1
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

 SELECT annotid, name, geneid, transcript, genetype, transcrypttype, symbol, description, email 
			FROM annot, pep, genome, users 
			WHERE annotid=pepid AND pep.chromid=genome.chromid AND validated=0 AND isAnnotated=0 AND users.id=annotator;

SELECT pep.id FROM genome, pep WHERE genome.chromid=pep.chromid AND name ILIKE '%new%'
        UNION
        SELECT pep.id FROM pep WHERE pepid ILIKE '%new%'
        UNION
        SELECT pep.id FROM pep WHERE pep.chromid ILIKE '%new%'
        UNION
        SELECT pep.id FROM pep WHERE location ILIKE '%new%'
        UNION
        SELECT pep.id FROM pep, annot WHERE geneid ILIKE '%new%' AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE transcript ILIKE '%new%' AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE genetype ILIKE '%new%' AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE transcrypttype ILIKE '%new%' AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE symbol ILIKE '%new%' AND annotid=pepid
        UNION
        SELECT pep.id FROM pep, annot WHERE description ILIKE '%new%' AND annotid=pepid
        ;

//commande blast
bin/makeblastdb -in *fasta -dbtype "prot" -out dbdir/DB
bin/blastp -query file.fa -db dbdir/DB("db created previously")
ncbi-blast-2.10.1+/bin/blastp -query 2lines -db balstDb/new_DB -outfmt '7 qseqid sseqid pident length mismatch gapopen qstart qend sstart send evalue bitscore qlen slen gaps'

SELECT pepid, cdsid, pep.location, pep.sequence, cds.sequence, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description FROM pep, genome, annot, cds WHERE pepid=annotid AND pep.chromid=genome.chromid and pepid=cdsid AND to_tsvector('english', pep.chromid ||' '|| name ||' '|| pep.location ||' '||pepid||' '||geneid||' '||transcript||' '||genetype||' '||transcrypttype||' '||symbol||' '||description) @@ plainto_tsquery('c5491')
                    INTERSECT SELECT pepid, cdsid, pep.location, pep.sequence, cds.sequence, name, pep.chromid, annot.geneID, annot.transcript, annot.transcryptType, annot.geneType, annot.symbol, description  FROM pep, cds, genome, annot WHERE pep.chromid=genome.chromid AND cds.sequence ILIKE '%%' AND cdsid=pepid and pepid=annotid ;

                     /*
                // Add Genes
                <?php
                while($row=$genes->fetch()){
                    $loc=explode(" ", $row['location']);
                    $size=$loc[1]-$loc[0]+1;
                    if($loc[2]=="1"){
                        $or="+";
                    }else{
                        $or="-";
                    }
                
                ?>
                var size= <?php echo $size;?>;
                var loc= <?php echo $loc[0];?>;
                var or= <?php echo $or;?>;
				gene1 = chart.addGene( parseInt(loc),parseInt(size) , or);
                <?php
                }
                ?>*/
BEGIN TRANSACTION;
update annot set annotator=NULL where annotator=4;
delete from users where users.id=4; 
COMMIT;
END TRANSACTION;