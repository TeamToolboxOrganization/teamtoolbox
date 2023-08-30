create table gantt_link
(
    id     INTEGER    not null
        constraint gantt_links_pk
            primary key,
    source INTEGER    not null,
    target INTEGER    not null,
    type   VARCHAR(1) not null
);

