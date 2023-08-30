create table mstoken
(
    id           INTEGER
        constraint mstoken_pk
            primary key autoincrement,
    userId       INTEGER,
    accessToken  VARCHAR(200),
    refreshToken VARCHAR(200),
    tokenExpires VARCHAR(200),
    userName     VARCHAR(50),
    userEmail    VARCHAR(50),
    userTimeZone VARCHAR(50)
);

