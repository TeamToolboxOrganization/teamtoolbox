create table product
(
    id          integer not null
        constraint product_pk
            primary key autoincrement,
    name        VARCHAR(50),
    description VARCHAR(4000)
);

create unique index product_id_uindex
    on product (id);

