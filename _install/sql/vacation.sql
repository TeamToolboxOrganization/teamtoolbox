create table vacation
(
    id        INTEGER not null
        constraint vacation_pk
            primary key,
    collab_id INTEGER,
    type      VARCHAR(100),
    value     FLOAT,
    start_at  DATETIME,
    end_at    DATETIME,
    state     VARCHAR(100)
);

