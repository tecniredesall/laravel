<?php

namespace App\Console\Commands;

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

use App\Models\FailedRequests;
use App\Models\Requests;

use App\Common\Api\Functions as Fn;


class sendPostRequest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'request:sendData  {quantity=100}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(fn $fn,log $log)
    {
        $this->fn = $fn;
        $this->log = $log;
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {        

        $quantity = $this->argument('quantity');
        $count_to_send=(int)Requests::GetCountDataRequest();
        if($count_to_send>$quantity)
        {
            $this->line("\n");
            $count_bar=(int)Requests::GetCountDataRequest()/(int)$quantity;
            $bar = $this->output->createProgressBar($count_bar);
            $bar->start();
        }else
        {
            $this->line("\n");
            $bar = $this->output->createProgressBar($quantity);
            $bar->start();
            $bar->finish();
            $this->line("\n");
        }
        
        //$bar = $this->output->createProgressBar($quantity);
        
        
        $i=0;
        while(Requests::GetCountDataRequest()>0)
        {
            
            $this->fn->setTypeRequest(Fn::SENDREQUEST);
            $this->line("      SEND  $quantity REQUEST MORE\n");
            $bar->advance();
            $data=[];         
            $data = Requests::GetDataRequest()->toArray();
            $this->fn->sendPoolRequest($data,$quantity);
            $i=$i+1;


            if($i==3)
            {
                
                $this->fn->setTypeRequest(Fn::RE_SENDREQUEST);
                $time=(int)0;
                while((FailedRequests::GetCountFailedRequest()>0 && $time<3))
                {
                        $this->line("\nSEND  $quantity FAIL REQUEST\n");
                        $data=[];    
                        $data = FailedRequests::GetFailedRequest()->toArray();
                        $this->fn->sendPoolRequest($data,$quantity);
                        $time=$time+1;
                }
                $i=0;
                $time=0;
            }
        }
        $bar->finish();
        $this->line("\n");
    }
}


