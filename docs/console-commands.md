# Console commands

## db:wipe
db:wipe lets you clear all tables within current database. 
In addition, you have the option to clear the following in ArangoDB:

* --drop-analyzers: clear all custom analyzers, predefined system analyzers remain
* --drop-views: drop all views
* --drop-graphs: drop all named graphs
* --drop-all: drop all of the above: tables, analyzers, views and graphs

## db:show
db:show gives you an overview of the current database and its tables.
In addition to the default Laravel options, you have the following:

* --analyzers: show a list of available analyzers
* --views: show a list of available views
* --graphs: show a list of available named graphs
* --system: include system tables in the table list

## db:table
db:table gives you an overview of the selected table. With ArangoDB specific information.

The new --system option allows you to select a system table as well.

## Migrations
_**Migrations for ArangoDB use a different Schema blueprint. Therefore, you either need  to run the convert:migrations
command first, or convert them manually**_

The other migration commands work as expected with a few added features detailed below.

### convert:migrations 
`php artisan convert:migrations` converts all available migrations to their ArangoDB counterpart.
After this you use migrations as normal. 

If you are using a multi database setup with migrations for each, you'll want it convert them manually.
Which means importing the Blueprint and Facade from this package.

Replace:
```
Illuminate\Database\Schema\Blueprint;
Illuminate\Support\Facades\Schema;
```
for:
```
use LaravelFreelancerNL\Aranguent\Schema\Blueprint;
use LaravelFreelancerNL\Aranguent\Facades\Schema;
```

### migrate:fresh
`php artisan migrate:fresh` uses db:wipe under the hood and can use the same --drop-{feature} options.

## make:migration
`php artisan make:migration` gives you the additional option to create an edge table. This presets
the proper collection type within the migration file.

# make:model
`php artisan make:model` uses stubs with some preset docblock properties. In addition, you can
select the following stubs:

```
      --edge-pivot        The generated model uses a custom intermediate edge-collection model for ArangoDB
      --edge-morph-pivot  The generated model uses a custom polymorphic intermediate edge-collection model for ArangoDB
 ```