create table user
(
    id                  INTEGER                not null
        primary key,
    full_name           VARCHAR(255)           not null,
    username            VARCHAR(255)           not null,
    email               VARCHAR(255)           not null,
    password            VARCHAR(255),
    roles               CLOB                   not null,
    manager_id          INTEGER,
    birthday            DATETIME,
    squad               INTEGER,
    picture             VARCHAR(255),
    idts                int,
    employeeid          int,
    idjira              VARCHAR(255),
    apikeyjira          VARCHAR(255),
    apikeyazdo          VARCHAR(255),
    sharedata           INTEGER(1)   default 1,
    mstokenid           int,
    analytics           INTEGER(1)   default 1,
    wizard              INTEGER      default 1 not null,
    default_desk_id     integer,
    default_activity_id VARCHAR(255) default '03. Development',
    default_product     VARCHAR(255) default '781361'
);

create unique index UNIQ_8FB094A1E7927C74
    on user (email);

create unique index UNIQ_8FB094A1F85E0677
    on user (username);

INSERT INTO user (id, full_name, username, email, password, roles, manager_id, birthday, squad, picture, idts, employeeid, idjira, apikeyjira, apikeyazdo, sharedata, mstokenid, analytics, wizard, default_desk_id, default_activity_id, default_product) VALUES (1, 'Administrateur', 'admin', 'admin@admin.com', '$2y$13$e8wCwya4AfRldk/lUQ75AOjVTzT9MZXt/3y9W6t8n357WtT9/jmka', '["ROLE_ADMIN"]', null, '1987-03-16 00:00:00', null, 'avatar.jpg', 35005, null, null, null, null, 1, null, 1, 0, null, '2', '1');
INSERT INTO user (id, full_name, username, email, password, roles, manager_id, birthday, squad, picture, idts, employeeid, idjira, apikeyjira, apikeyazdo, sharedata, mstokenid, analytics, wizard, default_desk_id, default_activity_id, default_product) VALUES (2, 'Dev Loppeur', 'dev', 'dev@dev.com', '$2y$13$I25YK54u/HlCDrmBsvdRs.PS32ecmVF.S3bkEvh6vkgVpcpi1YFwq', '[]', 1, '2023-06-29 00:00:00', 1, null, null, null, null, null, null, 1, null, 1, 1, null, null, '1');
