<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PostRequestToSend extends Model
{
    protected $table = 'requests';
    protected $primaryKey = 'id';
    protected $fillable = array(
        'url',
        'method',
        'body',
        'code',
        'proccess',
        'send',
        'successful'
    );

     

    public function getRemainingData()
    {
        return PostRequestToSend::where('proccess', '>', false)->where('send',"=",false)->get();
    }



    public function getCountRemainingData()
    {
        return PostRequestToSend::where('proccess', '=', false)->where('send',"=",false)->count();
    }
}
