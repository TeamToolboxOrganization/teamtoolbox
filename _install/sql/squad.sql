create table squad
(
    id      INTEGER not null
        constraint squad_pk
            primary key,
    name    VARCHAR(50),
    picture VARCHAR(255)
);

create unique index squad_id_uindex
    on squad (id);

INSERT INTO squad (id, name, picture) VALUES (1, 'Squad 1', null);
