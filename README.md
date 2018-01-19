Image
====
...

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist plusser/yii2-image "*"
```

or add

```
"plusser/yii2-image": "*"
```

and run migration

```
php yii migrate/up --migrationPath="@vendor/plusser/yii2-image/migrations"
```

to the require section of your `composer.json` file.
