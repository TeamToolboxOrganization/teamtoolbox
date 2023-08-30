create table mindset
(
    id        integer not null
        constraint mindset_pk
            primary key autoincrement,
    date      DATETIME,
    value     FLOAT,
    collab_id INTEGER not null,
    author_id INTEGER
);

create unique index mindset_id_uindex
    on mindset (id);

