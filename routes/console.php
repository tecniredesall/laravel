<?php


use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Requests;
/*
|--------------------------------------------------------------------------
| Console Routes
|--------------------------------------------------------------------------
|
| This file is where you may define all of your Closure based console
| commands. Each Closure is bound to a command instance allowing a
| simple approach to interacting with each command's IO methods.
|
*/

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->describe('Display an inspiring quote');

Artisan::command('preload-data', function () {
    $this->comment('starting data insertion..');
    $max = 100000;//real 100000
    for ($i = 0; $i < $max; $i++){
            $requests = new Requests;
            $requests->body='{}';
            $requests->save();
    }
    $this->comment("finish data insertion..{$i}");

})->describe('insert data request');
