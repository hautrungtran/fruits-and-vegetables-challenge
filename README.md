# 🍎🥕 Fruits and Vegetables

## ✅ Prerequisites
* PHP 8.2+
* Composer
* SQLite (for local development)

## ⚙️ Setup
```bash
composer install
cp .env .env.local
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
php bin/console app:import-produce
symfony serve -d
```

## ✅ Run tests
```bash
vendor/bin/phpunit
```

## 🧹 Code quality
```bash
vendor/bin/php-cs-fixer fix
vendor/bin/rector process --dry-run
vendor/bin/phpstan analyse --memory-limit=512M
```

## 🔌 API
Base URL: `http://127.0.0.1:8000/api`

Endpoints:
- `GET /fruits` — list fruits, supports filters: `name`, `quantityFrom`, `quantityTo`, `unit`
- `GET /fruits/{id}` — fetch a single fruit
- `POST /fruits` — create a fruit
- `DELETE /fruits/{id}` — delete a fruit
- `GET /vegetables` — list vegetables, supports filters: `name`, `quantityFrom`, `quantityTo`, `unit`
- `GET /vegetables/{id}` — fetch a single vegetable
- `POST /vegetables` — create a vegetable
- `DELETE /vegetables/{id}` — delete a vegetable
- `POST /import` — import items from an uploaded JSON file

Note: use the `unit` query param (e.g., `?unit=kg`) to convert response quantities.

---

## 🎯 Goal
We want to build a service which will take a `request.json` and:
* Process the file and create two separate collections for `Fruits` and `Vegetables`
* Each collection has methods like `add()`, `remove()`, `list()`;
* Units have to be stored as grams;
* Store the collections in a storage engine of your choice. (e.g. Database, In-memory)
* Provide an API endpoint to query the collections. As a bonus, this endpoint can accept filters to be applied to the returning collection.
* Provide another API endpoint to add new items to the collections (i.e., your storage engine).
* As a bonus you might:
  * consider giving an option to decide which units are returned (kilograms/grams);
  * how to implement `search()` method collections;
  * use latest version of Symfony's to embed your logic 

### ✔️ How can I check if my code is working?
You have two ways of moving on:
* You call the Service from PHPUnit test like it's done in dummy test (just run `bin/phpunit` from the console)

or

* You create a Controller which will be calling the service with a json payload

## 💡 Hints before you start working on it
* Keep KISS, DRY, YAGNI, SOLID principles in mind
* We value a clean domain model, without unnecessary code duplication or complexity
* Think about how you will handle input validation
* Follow generally-accepted good practices, such as no logic in controllers, information hiding (see the first hint).
* Timebox your work - we expect that you would spend between 3 and 4 hours.
* Your code should be tested
* We don't care how you handle data persistence, no bonus points for having a complex method

## When you are finished
* Please upload your code to a public git repository (i.e. GitHub, Gitlab)
