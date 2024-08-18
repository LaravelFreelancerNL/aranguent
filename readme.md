Aranguent
---------
<p align="center">

![Github CI tests](https://github.com/LaravelFreelancerNL/laravel-arangodb/workflows/CI%20tests/badge.svg?branch=next)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/LaravelFreelancerNL/laravel-arangodb/badges/quality-score.png?b=next)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/laravel-arangodb/?branch=next)
[![Code Coverage](https://scrutinizer-ci.com/g/LaravelFreelancerNL/laravel-arangodb/badges/coverage.png?b=next)](https://scrutinizer-ci.com/g/LaravelFreelancerNL/laravel-arangodb/?b=next)
<a href="https://packagist.org/packages/laravel-freelancer-nl/aranguent"><img src="https://poser.pugx.org/laravel-freelancer-nl/aranguent/v/unstable" alt="Latest Version"></a>
<a href="https://packagist.org/packages/laravel-freelancer-nl/aranguent"><img src="https://poser.pugx.org/laravel-freelancer-nl/aranguent/downloads" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel-freelancer-nl/aranguent"><img src="https://poser.pugx.org/laravel-freelancer-nl/aranguent/license" alt="License"></a>

[ArangoDB](https://www.arangodb.com) driver for [Laravel](https://laravel.com)  
<sub>The unguent between the ArangoDB and Laravel</sub>
</p>

The goal is to create a drop-in ArangoDB replacement for Laravel's database, migrations and model handling.

**This package is in development; use at your own peril.**

## Installation
You may use composer to install Aranguent:

``` composer require laravel-freelancer-nl/aranguent ```

While this driver is in the beta stage, changes are you will get a type error upon installation.

composer.json will probably not list a specific version:
```"laravel-freelancer-nl/aranguent": "*"```

If so, either set the minimum-stability level to 'dev' or install the latest version:
```
composer require laravel-freelancer-nl/aranguent:v1.0.0-beta.8 laravel-freelancer-nl/fluentaql:2.1.1
```
This updates the package to the latest beta, and properly installs the fluentaql package as well.


### Version compatibility
| Laravel       | ArangoDB | PHP  | Aranguent |
|:--------------|:---------|:-----|:----------|
| ^8.0 and ^9.0 | ^3.7     | ^8.0 | ^0.13     |
| ^11.0         | ^3.11    | ^8.2 | ^1.0.0    |

## Documentation
1) [Connect to ArangoDB](docs/connect-to-arangodb.md): set up a connection
2) [Converting from SQL databases to ArangoDB](docs/from-sql-to-arangodb.md):
3) [Migrations](docs/migrations.md): migration conversion and commands 
4) [Eloquent relationships](docs/eloquent-relationships.md): supported relationships 
5) [Query Builder](docs/query-functions.md): supported functions
6) [Selecting JSON data](docs/selecting-json-data.md): how to select subsets of documents.
7) [ArangoSearch](docs/arangosearch.md): searching views
8) [Transactions](docs/transactions.md): how to set up ArangoDB transactions
9) [FluentAQL](docs/fluent-aql.md): Use the AQL query builder directly
10) [Testing](docs/testing.md): testing your project with Aranguent.
11) [Compatibility list](docs/compatibility-list.md): overview of DB related compatible methods.
11) [Secondary database](docs/arangodb-as-secondary-db.md): using ArangoDB as your secondary database.

## Related packages
* [ArangoDB PHP client](https://github.com/LaravelFreelancerNL/arangodb-php-client)
* [FluentAQL - AQL Query Builder](https://github.com/LaravelFreelancerNL/fluentaql)
