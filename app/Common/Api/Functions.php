<?php

namespace App\Common\Api;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;



use Illuminate\Console\Command;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Pool;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Support\Facades\Log as log;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Client\RequestExceptionInterface;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Hash;




//modelos
use App\Models\Requests;
use App\Models\FailedRequests;

class Functions
{
     /**
     * @var object
     */
    const SENDREQUEST = 1;
    const RE_SENDREQUEST = 0;
        

    public function __construct(log $log)
    {
        $this->log = $log;
        $this->clientGuzzle=new Client(['timeout'  => 2.0,'connect_timeout' => 3.0]);;
        $this->typeRequest=0;
    }

    public function _getRequest(){
        return Requests::GetDataRequest()->toArray();
    }

    public function makeRequestPost()
    {
        $client = $this->clientGuzzle;
        $typeRequest=$this->typeRequest;

        $requests = function (&$data) use ($client,$typeRequest) {
        $_dt=(array)$data;            
        //dd($this->fn->_getRequest());//example de clase implementacion

        //$this->line(count($_dt));    

            for ($i = 0; $i < count($_dt); $i++) {
                $item=$_dt[$i];
                yield function() use ($item,$client,$typeRequest) {
                    //$bar->advance();
                    $obj=json_decode(json_encode($item));
                    $_url=$obj->url;
                    $_data=$obj->data;
                    $_method='POST';
                    $_id=$obj->idbd;
                    
                    if($typeRequest==Functions::RE_SENDREQUEST)
                    {
                            $_failId=$obj->failId;
                            FailedRequests::updateStateRetries($_failId);
                    }
                    Requests::updateStateProcessing($_id); 
                    return $response = $client->requestAsync($_method,$_url, ['connect_timeout' => 5.0,
                        'multipart' => [
                           [
                                'name'     => "hola",
                                'contents' => 'data',
                                'headers'  => ['X-Baz' => 'bar']
                            ]
                           ],
                    ]);
                };
            }
        };
        return $requests;
    }

    public function setTypeRequest($typeRequest=0)
    {
        $this->typeRequest=$typeRequest;
    }

    public function getClientGuzzle()
    {
        return $this->clientGuzzle;
    }



    public function sendPoolRequest($data,$quantity)
    {
       $_dtCheck=(array)$data;
       $typeRequest=$this->typeRequest;
       $_request=$this->makeRequestPost();
        $pool = new Pool($this->getClientGuzzle(), $_request($data), [
                'concurrency' => $quantity,
                'fulfilled' => function (Response $response, $index) use ($_dtCheck,$typeRequest) {
                    if ($response->getStatusCode() == 200) 
                    {

                        
                        $item=$_dtCheck[$index];
                        $obj=json_decode(json_encode($item));
                        Requests::updateStateSuccefully($obj->idbd);  
                        $this->log::info("request succesfully to ".$obj->url." with reference ".$obj->idbd);
                        // if($typeRequest==Functions::RE_SENDREQUEST)
                        // {
                        //     FailedRequests::insertRequestFail($obj->failId);
                        // }    
                    }
                },
                'rejected' => function (RequestException $reason, $index) use ($_dtCheck,$typeRequest) {
                        
                        $item=$_dtCheck[$index];
                        $obj=json_decode(json_encode($item));
                        $this->log::info("request failed to ".$obj->url. "with reference".$obj->idbd);
                        if($typeRequest==Functions::SENDREQUEST)
                        {
                            FailedRequests::insertRequestFail($obj->idbd);
                        }
                        Requests::updateStateFail($obj->idbd);
                },
        ]);
        $promise = $pool->promise();
        $promise->wait();
    }



}