<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use LaravelFreelancerNL\Aranguent\Auth\User;

class UsersSeeder extends Seeder
{
    /**
     * Run the database Seeds.
     *
     * @return void
     */
    public function run()
    {
        $users = '[
           {
              "_key":"LyannaStark",
              "username":"Lyanna Stark",
              "email":"l.stark@windsofwinter.com"
           }
        ]';

        $users = json_decode($users, JSON_OBJECT_AS_ARRAY);

        foreach ($users as $user) {
            User::insertOrIgnore($user);
        }
    }
}
