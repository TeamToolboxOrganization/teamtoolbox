create table user_date
(
    id        INTEGER
        constraint user_date_pk
            primary key autoincrement,
    collab_id INTEGER,
    type      VARCHAR(100),
    start_at  DATETIME,
    end_at    DATETIME,
    am_pm     int
);

INSERT INTO user_date (id, collab_id, type, start_at, end_at, am_pm) VALUES (5905, null, 'mep', '2023-06-12 14:06:58', null, null);
