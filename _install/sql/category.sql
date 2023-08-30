create table category
(
    id            INTEGER not null
        primary key autoincrement,
    name          VARCHAR(255) default NULL,
    default_color VARCHAR(255) default NULL
);

INSERT INTO category (id, name, default_color) VALUES (1, 'Projet', '#d78aea');
INSERT INTO category (id, name, default_color) VALUES (2, 'Dev', '#FD819A');
