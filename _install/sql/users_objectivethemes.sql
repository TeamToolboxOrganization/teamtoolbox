create table users_objectivethemes
(
    user_id            INTEGER
        references user,
    objective_theme_id INTEGER
        references objective_theme
);

