# Key generator options
Tables in ArangoDB can be created using one of the available key generators. You can read up about them
[here](https://docs.arangodb.com/stable/concepts/data-structure/documents/#document-keys) 
and [here](https://docs.arangodb.com/stable/develop/http-api/collections/#create-a-collection_body_keyOptions).

The following assumes you have knowledge about ArangoDB keys as can be obtained through the above links.

## Column defined key generators
Laravel has several column methods which can be used to set a primary key. If the given field is equal to:
'id', '_key' or '_id', the key generator will be set according to the mapping below.

If these column methods are not found, or not called on these fields, the configured default generator is used.
You can also ignore the column methods by setting the config value 
'arangodb.schema.key_handling.prioritize_configured_key_type' to true.

By default, we map the key methods to the following ArangoDB key generators:

| Laravel column method | ArangoDB key generator |
|:----------------------|:-----------------------|
| autoIncrement()       | traditional            |
| id()				     | traditional            |
| increments('id')	     | traditional            |
| smallIncrements	     | traditional            |
| bigIncrements		 | traditional            |
| mediumIncrements	     | traditional            |
| uuid(id)              | uuid                   |
| ulid(id)              | _n/a_                  |

## Traditional vs autoincrement key generators
Even though ArangoDB has an autoincrement key generator we don't use it by default as it is not cluster safe.
The traditional key generator is similar to autoincrement: it is cluster safe although there may be gaps between
the _key increases.

If you want the column methods to set the generator to autoincrement you can override the default behaviour by setting
the config value 'arangodb.schema.key_handling.use_traditional_over_autoincrement' to false.
In which case any given offset in the 'from' method is also used.

## ulid
There is no ulid key generator in ArangoDB. The 'padded' generator may be used if you want
a lexigraphical sort order. You can do so by setting it in the config as the default key, and using configured keys only.
Or by setting it within the migration in the table options.

## Table option key generators
You can set the key options for the table in the migration. This overrides both the default key options and the one defined by column methods.

```
        Schema::create('taggables', function (Blueprint $collection) {
            //
        }, [
            'keyOptions' => [
                'type' => 'padded',
            ],
        ]);
 ```

