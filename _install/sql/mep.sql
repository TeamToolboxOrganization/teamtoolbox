create table mep
(
    id      integer not null
        constraint mep_data_pk
            primary key autoincrement,
    state   VARCHAR(50),
    version VARCHAR(10),
    comment VARCHAR(4000)
);

create unique index mep_data_id_uindex
    on mep (id);

INSERT INTO mep (id, state, version, comment) VALUES (5905, 'à confirmer', '1.0.0', 'Première mise en prod');
