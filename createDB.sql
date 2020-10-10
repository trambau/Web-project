/*
sudo -i -u postgres
CREATE USER admin;
ALTER ROLE admin WIENCRYPTED PASSWORD 'adminpswd';
*/
CREATE TABLE users(
            email VARCHAR(50),
            pswd VARCHAR(50),
            phone VARCHAR(15),
            PRIMARY KEY(email)
        );