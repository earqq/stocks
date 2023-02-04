<?php

namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Query\Builder;
use Swift_Mailer;
use Datetime;
class StocksController
{
    /**
     * StocksController constructor.
     */
    protected $table_users;
    protected $table_records;
    protected $mailer;
    public function __construct(
        Builder $table_records,
        Builder $table_users,
        Swift_Mailer $mailer,
    ) {
        $this->table_records = $table_records;
        $this->table_users = $table_users;
        $this->mailer = $mailer;
    }

    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function records(Request $request,Response $response): Response{
        //get users authenticated 
        $token = $request->getAttribute("token");
        $email = $token['sub'];
        $user = $this->table_users
        ->where('email',$email)
        ->first();
        
        //get before records for the user
        $records = $this->table_records->where('user_id',$user->id)
        ->select(['created_at as date','name','symbol','open','high','low','close'])
        ->OrderBy('created_at','desc')
        ->get();
        $response->getBody()->write(json_encode($records,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $response;
    }
     /**
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    public function stockValue(Request $request, Response $response): Response
    {
        // get authenticated user
        $token = $request->getAttribute("token");
        $email = $token['sub'];
        $user = $this->table_users->where('email',$email)->first();
        
        // get stock value data
        $stock_code = $request->getQueryParams()['code'];
        $stock_data = $this->getFromStooqAPI($stock_code);
        
      
        $string_exploded = explode(',',$stock_data);
        // If stock code dont exist return err
        if($string_exploded[11]=='N/D'){
            $message["status"] = "error";
            $message["message"] = 'Code dont exist';
            $response->withHeader("Content-Type", "application/json")
                ->getBody()
                ->write(json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $response;

        }
        //Insert record into Database
        $stockValueData = $this->saveRecord($string_exploded,$user->id);
        
        // send the data by mail
        try{
            $this->sendEmail($user->email,$stock_data);
        }catch(\Exception $e){
            $message["status"] = "error";
            $message["message"] = 'Error in mail sending: '.$e->getMessage();
            $message["data"] = $stockValueData;
            $response->withHeader("Content-Type", "application/json")
                ->getBody()
                ->write(json_encode($message, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $response;
        }
        $response->getBody()->write(json_encode($stockValueData,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $response;
    }
    /**
     * @param Array $data
     * @param Int $user_id
     * * @return Boolean $result
     */
    private function saveRecord($data,$user_id): Array{
        $dataFromAPI= [
            'name' => trim($data[16],"\r\n"),
            'symbol' => explode(PHP_EOL,$data[8])[1],
            'open' => $data[11],
            'high' => $data[12],
            'low' => $data[13],
            'close' => $data[14],           
        ];
        $dataToReturn = $dataFromAPI;
        $dataFromAPI[ 'volume'] = $data[15]; 
        $dataFromAPI[ 'date'] = $data[9].' '.$data[10]; 
        $dataFromAPI[ 'user_id'] = $user_id; 
        $dataFromAPI[ 'created_at'] = new Datetime(); 

        $this->table_records->insert([
            $dataFromAPI
        ]);
        return $dataToReturn;
    }
    /**
     * @param String $stock_code
     * * @return String
     */
    private function getFromStooqAPI($stock_code): String{
        $url = 'https://stooq.com/q/l/?s='.$stock_code.'&f=sd2t2ohlcvn&h&e=csv';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }
    /**
     * @param String $email
     * @param Array $stock_data
     * * @return Bool
     */
    private function sendEmail($email,$stock_data): Bool{
        $message = (new \Swift_Message('Hello from PHP Challenge'))
        ->setFrom(['earq14@gmail.com' => 'PHP Challenge'])
        ->setTo($email)
        ->setBody('SwiftMailer is awesome!. '."\n".$stock_data);

        $res = $this->mailer->send($message);
        return $res;
    }
}
