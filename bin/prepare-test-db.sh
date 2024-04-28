#!/bin/bash

. .env

DB_DATABASE=${DB_DATABASE:-'aranguent__test'}
DB_ENDPOINT=${DB_ENDPOINT:-'http://localhost:8529'}

echo Creating database: $DB_DATABASE

curl -X POST -u root: --header 'accept: application/json' --data-binary @- --dump - $DB_ENDPOINT/_api/database \
<<EOF
{
  "name" : "$DB_DATABASE"
}
EOF

./vendor/bin/testbench migrate:install
./vendor/bin/testbench convert:migrations

exit 0

