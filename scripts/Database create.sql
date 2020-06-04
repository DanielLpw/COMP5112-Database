-- drop database 19089654g;
create database 19089654g;
use 19089654g;
CREATE TABLE USER(
username VARCHAR(100) NOT NULL,
password VARCHAR(100) NOT NULL,
salt varchar(100),
First_name VARCHAR(100),
Last_name VARCHAR(100),
PRIMARY KEY (username)
);

-- use the MD5 to random generate a 32-bit string 
set @salt = MD5(RAND() * 10000);
set @salt2 = MD5(RAND() * 10000);
set @salt3 = MD5(RAND() * 10000);
-- concat the random string(salt) with the password, and then sha2 it.
INSERT INTO USER (username, password,salt,First_name,Last_name) 
	VALUES ('Bob', sha2(concat('123',@salt),256), @salt,'admin','hahaha');
INSERT INTO USER (username, password,salt,First_name,Last_name) 
	VALUES ('Alice',sha2(concat('135',@salt2),256) , @salt2,'admin','hahaha');
INSERT INTO USER (username, password,salt) 
	VALUES ('Jobs',sha2(concat('testing',@salt3),256),@salt3);
select * from user;

-- when login,compare it
set @usersalt = (select salt from USER where username = 'Bob');
select sha2(concat('123',@usersalt),256);
SELECT USERNAME,Password FROM USER WHERE username = 'Bob' 
	and password = sha2(concat('123',@usersalt),256);

CREATE TABLE NOTE(
note_id INT NOT NULL AUTO_INCREMENT,
title VARCHAR(100) NOT NULL,
content VARCHAR(10000) NOT NULL,
username varchar(100) NOT NULL,
encrypted bit NOT NULL DEFAULT 0,
init_vector blob(16),
create_time timestamp DEFAULT CURRENT_TIMESTAMP,
PRIMARY KEY (NOTE_ID),
FOREIGN KEY (username) REFERENCES USER(username)
);
-- insert note which do not encrypt
INSERT INTO NOTE(title,content,username) VALUES('Day21','云淡风轻1','Bob');
INSERT INTO NOTE(title,content,username) VALUES('note2-bob','lalala','Bob');
INSERT INTO NOTE(title,content,username) VALUES('note1-alice','lalala','Alice');
INSERT INTO NOTE(title,content,username) VALUES('great','lalala','Alice');

-- insert note which is encrypt with CBC mode
SET block_encryption_mode = 'aes-128-cbc';
SET @key_str = 'testing';
SET @init_vector = RANDOM_BYTES(16);
INSERT INTO NOTE(title,content,username,encrypted,init_vector) 
	VALUES('encrypt-note-alice',HEX(AES_ENCRYPT(@key_str,'Alice',@init_vector)),'Alice',1,@init_vector);

SET @key_str = 'Bob-test';
SET @init_vector = RANDOM_BYTES(16);
INSERT INTO NOTE(title,content,username,encrypted,init_vector) 
	VALUES('encrypt-note-Bob',HEX(AES_ENCRYPT(@key_str,'Bob',@init_vector)),'Bob',1,@init_vector);

SET @key_str = 'Bob-test2222第二个';
SET @init_vector = RANDOM_BYTES(16);
INSERT INTO NOTE(title,content,username,encrypted,init_vector) 
	VALUES('encrypt-Bob222',HEX(AES_ENCRYPT(@key_str,'Bob-test',@init_vector)),'Bob',1,@init_vector);
select * from note;

--   decrypted 
SET @note_id = '7';
SET @MIWEN = (select note.content from note where note_id = @note_id);  
SET @init_vector = (select note.init_vector from note where note_id = @note_id);
SELECT CONVERT (AES_DECRYPT(UNHEX(@MIWEN),'Bob-test',@init_vector) USING utf8)
	WHERE CONVERT (AES_DECRYPT(UNHEX(@MIWEN),'Bob-test',@init_vector) USING utf8) like '%%';





