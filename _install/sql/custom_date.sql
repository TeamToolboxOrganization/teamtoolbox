create table custom_date
(
    id      integer not null
        constraint custom_date_pk
            primary key,
    name    VARCHAR(50),
    comment VARCHAR(4000)
);

create unique index custom_date_id_uindex
    on custom_date (id);

