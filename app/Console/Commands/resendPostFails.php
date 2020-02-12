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

class resendPostFails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    private $quantity=0;
    protected $signature = 'request:resendData  {quantity=100}';

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
        $this->fn->setTypeRequest(Fn::RE_SENDREQUEST);
        $quantity = $this->argument('quantity');

        $count_to_send=(int)FailedRequests::GetCountFailedRequest();
        if($count_to_send>$quantity)
        {
            $this->line("\n");
            $count_bar=(int)FailedRequests::GetCountFailedRequest()/(int)$quantity;
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

        while(FailedRequests::GetCountFailedRequest()>0)
        {
            $this->line("      SEND  $quantity FAIL REQUEST MORE\n");
            $this->fn->setTypeRequest(Fn::RE_SENDREQUEST);
            $bar->advance();
            $data=[];    
            $data = FailedRequests::GetFailedRequest()->toArray();
            $this->fn->sendPoolRequest($data,$quantity);
        }
        $bar->finish();
        $this->line("\n");
    }
}
