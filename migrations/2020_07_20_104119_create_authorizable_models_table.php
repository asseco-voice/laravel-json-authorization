<?php

use Asseco\BlueprintAudit\App\MigrationMethodPicker;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAuthorizableModelsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('authorizable_models', function (Blueprint $table) {
            if (config('asseco-authorization.migrations.uuid')) {
                $table->uuid('id')->primary();
            } else {
                $table->id();
            }
            $table->string('name')->unique();

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
        Schema::dropIfExists('authorizable_models');
    }
}
