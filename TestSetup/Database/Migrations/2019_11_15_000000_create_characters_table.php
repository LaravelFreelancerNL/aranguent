<?php

namespace TestSetup\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use LaravelFreelancerNL\Aranguent\Facades\Schema;
use LaravelFreelancerNL\Aranguent\Schema\Blueprint;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'characters',
            function (Blueprint $collection) {},
            [
                'computedValues' => [
                    [
                        'name' => 'full_name',
                        'expression' => "RETURN CONCAT_SEPARATOR(' ', @doc.name, @doc.surname)",
                        'overwrite' => true,
                        'computeOn' => ["insert"],
                        'failOnWarning' => false,
                        'keepNull' => true,
                    ],
                ],
            ],
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('characters');
    }
};
