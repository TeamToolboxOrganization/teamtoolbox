create table gantt_task
(
    id         INTEGER      not null
        constraint gantt_tasks_pk
            primary key,
    text       VARCHAR(255) not null,
    start_date DATETIME     not null,
    duration   INTEGER      not null,
    progress   FLOAT        not null,
    parent     INTEGER,
    owner_id   INTEGER,
    type       VARCHAR(50),
    key        VARCHAR(50),
    sortorder  INTEGER,
    jira_type  VARCHAR(50),
    squad      VARCHAR(50),
    deadline   DATETIME,
    end_date   DATETIME
);

