<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class FailedRequests extends Model
{

    const MAX_RETRIES = 5;

    const TO_SEND = 0;
    const SEND_SUCCESSFULL = 1;
    const SEND_FAILED = 2;

    protected $table = 'failed_requests';
    protected $primaryKey = 'id';
    protected $fillable = array(
        'response',
        'code',
        'retries',
        'request_id',
    );

     

    public function scopeGetFailedRequest($query) {

        $data = $query->join('requests', 'requests.id', '=', 'failed_requests.request_id')
            ->select(DB::raw("requests.url as url ,failed_requests.id as failId,requests.id as idbd,requests.body as data"))
            ->where('proccess', '=', false)
            ->where('successful',"!=","1")
            ->where('failed_requests.retries',"<",FailedRequests::MAX_RETRIES)
            ->get();
        
        return $data;
    }

    public function scopeGetCountFailedRequest($query) {
        $data = $query->join('requests', 'requests.id', '=', 'failed_requests.request_id')
                ->select('requests.*')
                ->where('proccess', '=', false)->where('successful',"!=","".FailedRequests::SEND_SUCCESSFULL."")
                ->where('failed_requests.retries',"<",FailedRequests::MAX_RETRIES)
                ->count();

        return $data;
    }

    public static function updateStateRetries($id)
    {
         $request = FailedRequests::find($id);
         $request->retries = (int)$request->retries + (int)1;
         $request->updated_at=now();
         return $request->save();
    }


    public static function updateStateSuccesfull($id)
    {
         $request = FailedRequests::find($id);
         $request->updated_at=now();
         $request->code=200;
         return $request->save();
    }


    public static function insertRequestFail($request_id,$response="",$code=500)
    {
        $request = new FailedRequests();
        $request->response=0;
        $request->retries=0;
        $request->request_id=$request_id;
        $request->updated_at=now();
        $request->code=$code;
        $request->save();
    }

}

//hacer variables globales