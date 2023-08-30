create table objective_theme
(
    id          INTEGER
        constraint objective_theme_pk
            primary key autoincrement,
    title       VARCHAR(255),
    description VARCHAR(255),
    progress    INTEGER
);

