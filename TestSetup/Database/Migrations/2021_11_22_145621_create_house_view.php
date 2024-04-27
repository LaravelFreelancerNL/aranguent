<?php

namespace TestSetup\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use LaravelFreelancerNL\Aranguent\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropViewIfExists('house_view');

        Schema::createView('house_view', [
            'links' => [
                'houses' => [
                    'fields' => [
                        'name' => [
                            'analyzers' => ['identity'],
                        ],
                        'en' => [
                            'analyzers' => ['text_en'],
                        ],
                    ],
                    'includeAllFields' => true,
                    'storeValues' => 'none',
                    'trackListPositions' => false,
                ],
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropViewIfExists('house_view');
    }
};
