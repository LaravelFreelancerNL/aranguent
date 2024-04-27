#!/usr/bin/env bash
printf "\nRun tests\n"
./vendor/bin/testbench migrate:fresh --seed
./vendor/bin/testbench package:test tests
