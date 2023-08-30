create table desk
(
    id   integer not null
        constraint desk_pk
            primary key,
    x    INTEGER,
    y    INTEGER,
    name STRING
);

create unique index desk_id_uindex
    on desk (id);

INSERT INTO desk (id, x, y, name) VALUES (1, 510.25, 132, 'Bureau 1');
INSERT INTO desk (id, x, y, name) VALUES (2, 509.5, 159, 'Bureau 2');
