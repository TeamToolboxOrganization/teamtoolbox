create table office
(
    id               integer not null
        constraint office_pk
            primary key,
    import_from_rhpi integer default 0
);

create unique index office_id_uindex
    on office (id);

