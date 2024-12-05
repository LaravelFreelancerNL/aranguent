# Migrations
You can use the regular migration Artisan commands.

## Blueprint execution
Blueprint commands are executed sequentially and non-transactional(!)

## tables
You can add ArangoDB's collection config options to the create method.

```php
Schema::create('posts', function (Blueprint $table) {
    //
}, [
        'type' => 3,            // 2 -> normal collection, 3 -> edge-collection</li>
        'waitForSync' => true,  // if set to true, then all removal operations will instantly be synchronised to disk / If this is not specified, then the collection's default sync behavior will be applied.</li>
]);
 ```
See the [ArangoDB Documentation for all options](https://docs.arangodb.com/3.3/HTTP/Collection/Creating.html)

## Indexes
Within the collection blueprint you can create indexes.
This following indexes are supported:

Type       | Purpose                  | Blueprint Method 
---------- |--------------------------| ----------------
Persistent       | Ranged matching          | `$table->index($columns = null, $name = null, $algorithm = null, $indexOptions = [])`
Primary *        | Unique ranged matching   | `$table->primary($columns = null, $name = null, $indexOptions = [])`
Unique           | Unique ranged matching   | `$table->unique($attributes, $indexOptions = [])`
Geo              | Location matching        | `$table->spatialIndex($columns, $name = null, $indexOptions = [])`
TTL              | Auto-expiring documents  | `$table->ttlIndex($columns, $expireAfter, $name = null, $indexOptions = [])`
Inverted         | Fast full text searching | `$table->invertedIndex($columns = null, $name = null, $indexOptions = [])`
multiDimensional | 2D+ numeric search       | `$table->multiDimensionalIndex($columns = null, $name = null, $indexOptions = [], $type = 'mdi')`

* the primary method is supported for composite keys. ArangoDB already sets a primary index on the _key property.

See the [ArangoDB Documentation for more information](https://docs.arangodb.com/stable/HTTP/Indexes/)

### Dropping Indexes
Use `$table->dropIndex($attributes, $type)` to drop an index from within a Blueprint.
Every index type has a related drop method. You can use any one of:
dropPrimary / dropUnique / dropIndex / dropSpatialIndex / dropInvertedIndex / dropTtlIndex.

## Attributes
ArangoDB is schemaless so you can't create attributes. However you can perform some operations on 
attributes in existing documents.

`dropAttribute($attributes)` deletes the attribute(s) in all documents within the collection.

`hasAttribute($attribute)` Checks for the usage (! null) of the the attribute(s) in any document within the collection.
 
`renameAttribute($from, $to)` renames the attribute in all documents within the collection.


## Views (ArangoSearch)
You can create, edit or delete an ArangoDB view.

### New view
```php
Schema::createView($viewName, $options);
``` 

#### Example search-alias view creation
```php 
        Schema::createView(
            'house_search_alias_view',
            [
                "indexes" => [
                    [
                        "collection" => "houses",
                        "index" => "en-inv-index"
                    ]
                ],
            ],
            'search-alias'
        );
```

### Edit view
```php
Schema::editView($viewName, $options);
```

### Delete view
```php
Schema::dropView($viewName);
```

## Analyzers (ArangoSearch)
You can create, edit or delete an ArangoDB Analyzer.

### New analyzer
```php
Schema::createAnalyzer($name, $type, $properties, $features);
``` 

### Replace analyzer
```php
Schema::replaceAnalyzer($name, $type, $properties, $features);
```

### Delete analyzer
```php
Schema::dropAnalyzer($name);
```

### Delete all analyzers
```php
Schema::dropAnalyzers($name);
```

## Named Graphs
Named graphs are predefined managed graphs which feature integrity checks
compared to anonymous graphs.

You can perform basic CRUD operations through the schema builder to handle named graphs.

### Create a new graph
```php
Schema::createGraph($name, $properties, $waitForSync);
``` 

### Check for graph existence
```php
Schema::hasGraph($name);
``` 

### Get data of existing graph
```php
Schema::getGraph($name);
``` 

### Get all graphs
```php
Schema::getGraphs();
``` 

### Delete a graph
```php
Schema::dropGraph($name);
``` 

### Delete a graph if it exists
```php
Schema::dropGraphIfExists($name);
``` 

### Delete all graphs
```php
Schema::dropGraphs();
``` 