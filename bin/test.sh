#!/usr/bin/env bash
printf "\nRun tests\n"
./vendor/bin/testbench migrate:fresh --path=TestSetup/Database/Migrations --path=vendor/orchestra/testbench-core/laravel/migrations/ --realpath  --seed
./vendor/bin/testbench package:test tests
