# Book Recommendation App
A simple app that demonstrates some features of PHP Laravel 11 framework. It offers a (first-party) API of user registration and authentication, and books recommendation system.

## How to Install and Run Locally
This app requires Docker to be installed locally. 

### Installation
1. Clone the repository and `cd` to its directory.
2. Run `cp .env.example .env` and set the local env variables. The most important ones (in Linux and mac host) are `WWWUSER` and `WWWGROUP`. Set them to the id and group of the current OS user to prevent file permission conflicts. You can find the user id's by running the command `id` in Linux. You can skip this step is Windows as Docker already runs as Admin.
3. Install `sail` and other dependencies by running the following command:
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```
4. Once finished, Run `sudo ./vendor/bin/sail up -d`.
5. Now, the docker containers are running, you only need to run the database migration `./vendor/bin/sail artisan migrate`.
6. Head to `http://localhost` to make sure the app is up and running. You should see the laravel version returned as JSON.

## API Reference
You can find the API documentation [on Postman](https://documenter.getpostman.com/view/2338062/2sA3JNbgBu). 

## Postman JSON Collection
You can all import the API Postman collection to test the API locally. Find the collection JSON in [the docs folder](./docs/Book%20Recommendation%20API.postman_collection.json)

## Unit/Feature Tests
To run the tests locally, please run: `./vendor/bin/sail test`.