DROP DATABASE IF EXISTS etest;
CREATE DATABASE etest;
USE etest;

DROP TABLE IF EXISTS user,
    section_item, data_item, image_size,
    container, container_image_size;



CREATE TABLE user
(
    id         INT(11)                 AUTO_INCREMENT PRIMARY KEY,
    email      VARCHAR(128) NOT NULL,
    auth       TINYINT(1)              NOT NULL DEFAULT 0,
    username   VARCHAR(32)  NOT NULL,
    password   VARCHAR(128) NOT NULL,
    reg_code   VARCHAR(32) NULL,
    active     TINYINT(1)              NOT NULL DEFAULT 0,
    registered TIMESTAMP NULL,
    activated  TIMESTAMP NULL,
    fullname   VARCHAR(128) NOT NULL,
    picture    VARCHAR(128) NULL,
    about      TEXT NULL
) ENGINE=InnoDB;


CREATE TABLE section_item
(
    id           INT(11)					AUTO_INCREMENT PRIMARY KEY,
    section_id   INT(11)					NOT NULL DEFAULT 0,
    label        VARCHAR(128) NOT NULL,
    friendly_url VARCHAR(128) NULL,
    template     CHAR(1)      NOT NULL DEFAULT 'A',
    position     INT(11)					NOT NULL DEFAULT 0,
    hidden       BINARY(1)				NOT NULL DEFAULT 0,
    stamp        TIMESTAMP    NOT NULL,
    meta         TEXT NULL,
    user_id      INT(11)					NULL
);

CREATE TABLE data_item
(
    id           INT(11)					AUTO_INCREMENT PRIMARY KEY,
    data_item_id INT(11)					NULL,
    section_id   INT(11)					NULL,
    container_id INT(11)					NULL,
    label        VARCHAR(128) NOT NULL,
    heading      VARCHAR(128) NULL,
    body         TEXT NULL,
    image        VARCHAR(128) NULL,
    image_width  INT(11)           NULL,
    image_height INT(11)          NULL,
    video        VARCHAR(128) NULL,
    audio        VARCHAR(128) NULL,
    user_id      INT(11)					NULL,
    position     INT(11)					NOT NULL DEFAULT 0,
    hidden       BINARY(1)				NOT NULL DEFAULT 0,
    meta         TEXT NULL,
    stamp        TIMESTAMP NULL
);

CREATE TABLE image_size
(
    id     INT(11)					AUTO_INCREMENT PRIMARY KEY,
    name   VARCHAR(128) NULL,
    prefix VARCHAR(64) NULL,
    width  INT(11)					NOT NULL,
    height INT(11)					NOT NULL
);


CREATE TABLE container
(
    id          INT(11)					AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(128) NOT NULL,
    description TEXT NULL
);


CREATE TABLE container_image_size
(
    container_id  INT(11)					NOT NULL,
    image_size_id INT(11)					NOT NULL,
    PRIMARY KEY (container_id, image_size_id)
);



