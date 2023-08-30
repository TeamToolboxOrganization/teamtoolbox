create table project
(
    id      integer not null
        constraint project_pk
            primary key autoincrement,
    name    VARCHAR(50),
    comment VARCHAR(4000)
);

create unique index project_id_uindex
    on project (id);

