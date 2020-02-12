<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class Requests extends Model
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
     protected $hidden = [
        'created_at', 'updated_at',
    ];

    public function scopeGetDataRequest($query) {

        return $data = $query->select(DB::raw("url ,id as idbd,body as data"))
        ->where('proccess', '=', false)->where('send',"=",false)->take(100)->get();
        
    }


    public function scopeGetCountDataRequest($query) {
        return  $query->where('proccess', '=', false)->where('send',"=",false)->count();
    }

    public static function updateStateSuccefully($id,$code=200)
    {

         $request = Requests::find($id);
         $request->code = $code;
         $request->proccess = FALSE;
         $request->send = TRUE;
         $request->successful = "1";
         return $request->save();
    }


    public static function updateStateFail($id,$code=500)
    {
         $request = Requests::find($id);
         $request->code = $code;
         $request->proccess = FALSE;
         $request->send = TRUE;
         $request->successful = "2";
         return $request->save();

    }

    public static function updateStateProcessing($id)
    {
         $request = Requests::find($id);
         $request->proccess = TRUE;
         $request->send = TRUE;
         $request->successful = '0';
         return $request->save();

    }



    




}
