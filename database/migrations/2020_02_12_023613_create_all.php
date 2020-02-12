<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAll extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('requests');
        Schema::create('requests', function (Blueprint $table) {
            $table->increments('id');
            $table->string('url', 40)->default('https://atomic.incfile.com/fakepost')->nullable(false);
            $table->string('method', 40)->default('POST')->nullable(false);
            $table->json('body')->nullable(false);
            $table->integer('code')->default(200)->unsigned()->nullable(false);
            $table->boolean('proccess')->default(false);
            $table->boolean('send')->default(false);
            $table->enum('successful', [0,1,2])->default(0)->comment('1');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            
        });
        Schema::dropIfExists('failed_requests');
        Schema::create('failed_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id')->unsigned()->nullable(false);
            $table->string('response', 250)->nullable(false);
            $table->integer('code')->unsigned()->nullable(false);
            $table->integer('retries')->default(0)->unsigned()->nullable(false);
            
            $table->foreign('request_id')
                    ->references('id')
                    ->on('requests')
                    ->onDelete('cascade');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });
        Schema::dropIfExists('configuration');
        Schema::create('configuration', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('max_retries')->unsigned()->nullable(false)
                    ->comment('number of attempts per request');
            $table->integer('max_executions')->unsigned()->nullable(false)
                    ->comment('number of executions per pool');
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request');
        Schema::dropIfExists('failed_requests');
        Schema::dropIfExists('configuration');
    }
}
