<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TestSetup\Models\Event;

class EventsSeeder extends Seeder
{
    /**
     * Run the database Seeds.
     *
     * @return void
     */
    public function run()
    {
        $events = '[
    {
        "_key": "first-men-invase-westeros",
        "name": "First men invade Westeros",
        "type": "invasion",
        "age": "The Dawn Age",
        "timeline": {
            "starts_at": -12000.0,
            "ends_at": -10000.0
        }
    },
    {
        "_key": "the-long-night",
        "name": "The Long Night",
        "type": "war",
        "age": "The Age of Heroes",
        "timeline": {
            "starts_at": -8000.0,
            "ends_at": -7900.0
        }
    },
    {
        "_key": "aegon-the-conqueror-invades-westeros",
        "name": "Aegon the Conqueror invades Westeros",
        "type": "invasion",
        "age": "The Targaryen Conquest",
        "timeline": {
            "starts_at": -2.0,
            "ends_at": 0.0
        }
    }
]';

        $events = json_decode($events, JSON_OBJECT_AS_ARRAY);

        foreach ($events as $event) {
            Event::insertOrIgnore($event);
        }
    }
}
