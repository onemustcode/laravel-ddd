# Laravel Domain Driven Design package
Package to simply setup a Domain Driven Desgin Application in Laravel 5.x.

## Installation
Require the Query library trough composer.
```
composer require onemustcode/laravel-ddd
```

After updating composer, add the ServiceProvider to the providers array in config/app.php
```
OneMustCode\LaravelDDD\ServiceProvider::class,
```

## Creating a project
To create a project, simply run the following command and follow the instructions.

```
php artisan ddd:project
```

## Creating a new entity, repository and service
To create a entity, simply run the following command and follow the instructions.

```
php artisan ddd:create
```

