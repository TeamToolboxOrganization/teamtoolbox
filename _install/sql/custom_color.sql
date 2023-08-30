create table custom_color
(
    id           INTEGER      not null
        primary key autoincrement,
    user_id      INTEGER      not null,
    category_id  INTEGER      not null,
    custom_color VARCHAR(255) not null
);

