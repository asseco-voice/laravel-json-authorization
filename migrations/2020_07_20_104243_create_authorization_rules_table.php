<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizationRulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorization_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('authorizable_set_type_id')->constrained();
            $table->string('authorizable_set_value');
            $table->foreignId('authorizable_model_id')->constrained();
            $table->json('rules');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('authorization_rules');
    }
}
