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
            email VARCHAR(50),
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


CREATE TABLE genes (
    sequence VARCHAR()
);
/*
ALTER TABLE users
ADD COLUMN isApproved SMALLINT DEFAULT 0;
*/
INSERT INTO users
VALUES (DEFAULT, 'admin@email.com','pswd', '0123456789', 'admin', 'strator', 'ADMIN' );
/* create the first administrator */
UPDATE users 
SET usertype='admin' 
WHERE email='admin@test.com';

UPDATE users
SET usertype='admin' 
AND isApproved=1
WHERE email='ramelapierre@gmail.com';

*/
/*
user login check if credentials in the database
if yes redirect to page;
*/