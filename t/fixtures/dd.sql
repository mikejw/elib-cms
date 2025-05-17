

DROP DATABASE IF EXISTS project;
CREATE DATABASE project;
USE project;

DROP TABLE IF EXISTS 		user_profile, e_user,
							section_item, data_item, image_size,
							container, container_image_size;


CREATE TABLE user_profile(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
fullname                VARCHAR(128)            NOT NULL,
picture                 VARCHAR(128)            NULL,
about                   TEXT                    NULL) ENGINE=InnoDB;



CREATE TABLE e_user(
id                      INT(11)                 AUTO_INCREMENT PRIMARY KEY,
user_profile_id         INT(11)                 NOT NULL,
email                   VARCHAR(128)            NOT NULL,
auth                    TINYINT(1)              NOT NULL DEFAULT 0,
username                VARCHAR(32)              NOT NULL,
password                VARCHAR(32)             NOT NULL,
reg_code                VARCHAR(32)             NOT NULL,
active                  TINYINT(1)              NOT NULL DEFAULT 0,
registered              TIMESTAMP               NULL,
activated               TIMESTAMP               NULL,
FOREIGN KEY (user_profile_id) REFERENCES user_profile(id)) ENGINE=InnoDB;



CREATE TABLE 		section_item(
id			INT(11)					AUTO_INCREMENT PRIMARY KEY,
section_id		INT(11)					NOT NULL DEFAULT 0,
label			VARCHAR(128)				NOT NULL,
friendly_url		VARCHAR(128)				NULL,
template		CHAR(1)					NOT NULL DEFAULT 'A',
position		INT(11)					NOT NULL DEFAULT 0,
hidden			BINARY(1)				NOT NULL DEFAULT 0,
stamp			TIMESTAMP				NOT NULL,
meta			TEXT					NULL,
user_id			INT(11)					NULL);

CREATE TABLE 		data_item(
id			INT(11)					AUTO_INCREMENT PRIMARY KEY,
data_item_id		INT(11)					NULL,
section_id		INT(11)					NULL,
container_id		INT(11)					NULL,
label			VARCHAR(128)				NOT NULL,
heading			VARCHAR(128)				NULL,
body			TEXT					NULL,
image			VARCHAR(128)				NULL,
video			VARCHAR(128)				NULL,
user_id			INT(11)					NULL,
position		INT(11)					NOT NULL DEFAULT 0,
hidden			BINARY(1)				NOT NULL DEFAULT 0,
meta			TEXT					NULL,
stamp			TIMESTAMP				NULL);



