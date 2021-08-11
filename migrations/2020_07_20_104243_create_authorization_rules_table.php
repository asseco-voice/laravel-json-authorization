<?php

use Asseco\BlueprintAudit\App\MigrationMethodPicker;
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
            if (config('asseco-authorization.migrations.uuid')) {
                $table->uuid('id')->primary();
                $table->foreignUuid('authorizable_set_type_id')->constrained();
                $table->foreignUuid('authorizable_model_id')->constrained();
            } else {
                $table->id();
                $table->foreignId('authorizable_set_type_id')->constrained();
                $table->foreignId('authorizable_model_id')->constrained();
            }

            $table->string('authorizable_set_value');
            $table->json('rules');

            MigrationMethodPicker::pick($table, config('asseco-authorization.migrations.timestamps'));
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
