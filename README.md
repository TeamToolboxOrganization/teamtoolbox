Team Toolbox Application
========================

Requirements
------------

  * PHP 8.2 or higher;
  * PDO-SQLite PHP extension enabled;
  * and the [usual Symfony application requirements][2].
  * NPM 19.9.0
  * Composer 2.5.8

Installation
------------

```bash
$ cd my_project/
$ composer install
$ npm install
$ npm run build
```

Usage
-----

There's no need to configure anything to run the application. If you have
[installed Symfony][4] binary, run this command:

```bash
$ cd my_project/
$ symfony serve
```

Then access the application in your browser at the given URL (<https://localhost:8000> by default).

If you don't have the Symfony binary installed, run `php -S localhost:8000 -t public/`
to use the built-in PHP web server or [configure a web server][3] like Nginx or
Apache to run the application.

Connect to application using admin account.
Login : admin
Password : admin

Tests
-----

Execute this command to run tests:

```bash
$ cd my_project/
$ ./bin/phpunit
```

Deploy with Docker Compose
--------------------------

A `docker-compose.yml` file is provided to simplify Docker deployment.

Start the application:

```bash
$ docker compose up -d --build
```

Stop the application:

```bash
$ docker compose down
```

The application is available at <http://localhost:8080> and the SQLite data
is persisted in the Docker volume `teamtoolbox_data` (pre-seeded from the image
on first start, which keeps the default `admin/admin` account available).


If needed, reset persisted data with:

```bash
$ docker compose down -v
```


If you migrated from a previous Compose setup and still hit a login error, recreate
volumes so the writable database is re-initialized:

```bash
$ docker compose down -v
$ docker compose up -d --build
```

View logs from inside the container
-----------------------------------

To inspect application logs (e.g. to debug a 500 error), connect to the running
container and read the Symfony log files:

```bash
# Open a shell in the container
$ docker compose exec app bash

# Then inside the container, view the latest logs
$ tail -f /var/www/html/var/log/prod.log
# or for a one-shot view
$ cat /var/www/html/var/log/prod.log
```

To run a single command without entering the container:

```bash
$ docker compose exec app tail -100 /var/www/html/var/log/prod.log
```
