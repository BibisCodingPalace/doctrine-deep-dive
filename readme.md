# Doctrine ORM: Deep Dive

This project runs entirely in Docker. The app runs in a
[FrankenPHP](https://frankenphp.dev) container (PHP 8.4 + Caddy, the reference
setup used by the Symfony community), the database runs in a Postgres container.
The application code targets **Symfony 8.0** (requires PHP 8.4+).

Requirements
------------

- [Docker](https://docs.docker.com/get-docker/) incl. Docker Compose v2
- `make` (optional, but recommended; run `make` targets from the project root on the host, not inside the app container)

Installation
------------

1. Build the image and start the stack

   ```bash
   make build
   make up
   ```

2. Install PHP dependencies inside the container

   ```bash
   make install
   ```

3. Create the database schema

   ```bash
   make migrate
   ```

4. Open the app in your browser: <http://localhost:8000>

Running tests
-------------

```bash
make test                  # recreates the test DB and runs the full suite
make run-stateless-tests   # only tests that do not need a DB reset
```

Useful targets
--------------

| Target                | Description                                   |
| --------------------- | --------------------------------------------- |
| `make up`             | Start the stack in the background             |
| `make down`           | Stop and remove the stack                     |
| `make logs`           | Follow container logs                         |
| `make shell`          | Open a shell inside the app container         |
| `make install`        | Run `composer install` inside the container   |
| `make migrate`        | Run Doctrine migrations                       |
| `make compile-assets` | Compile the asset map for prod                |

Anything else you would normally run directly goes through
`docker compose exec app …`, for example:

```bash
docker compose exec app bin/console doctrine:schema:validate
docker compose exec app composer require some/package
```
