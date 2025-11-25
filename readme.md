# Doctrine ORM: Deep Dive

Installation (development)
--------------------------

1. Install dependencies

    ```bash
   symfony composer install 
   ``` 

2. Start database

    ```bash
    docker-compose up -d
    ```

3. Run tests (also sets up fixtures!)

    ```bash
    php bin/phpunit
    ```

4. Start local web server

    ```bash
    symfony local:server:start --d
    ```

5. Open app in browser: localhost:8000

Known issues
------------

**Database port is already in use**

You can modify the assigned port by changing the `docker-compose.yaml` or
providing a `docker-compose.override.yaml` that will not be committed with
your remaining changes.

**Using a local database instead of a service**

When using a local database make sure to provide a proper `DATABASE_URL` env var or update the default value in
`.env`. You might also want to adjust the `config/packages/doctrine.yaml` in case you are using a different server
version, e.g. MySQL 5.7 or MariaDB.
