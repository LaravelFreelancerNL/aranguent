#!/usr/bin/env bash
echo "Fix coding style"
./vendor/bin/pint

echo "Run PHPMD"
./vendor/bin/phpmd src/ text phpmd-ruleset.xml

echo "Run PHPStan"
./vendor/bin/phpstan analyse -c phpstan.neon

echo "Test package from within phpunit"
./vendor/bin/testbench convert:migrations
./vendor/bin/testbench migrate:fresh --path=TestSetup/Database/Migrations --path=vendor/orchestra/testbench-core/laravel/migrations/ --realpath --seed
./vendor/bin/testbench package:test --coverage --min=80 tests

