create table configuration
(
    id    integer     not null
        constraint table_name_pk
            primary key autoincrement,
    key   VARCHAR(50) not null,
    value VARCHAR(4000)
);

create index table_name_key_index
    on configuration (key);

INSERT INTO configuration (id, key, value) VALUES (1, 'media_content_url', 'https://youtu.be/iWNIxGQtg2k');
