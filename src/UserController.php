<?php
namespace App;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Query\Builder;
use Firebase\JWT\JWT;
use Tuupola\Base62;
use Datetime;

class UserController
{
    /**
     * UserController constructor.
     */
    protected $table_users;
    public function __construct(
        Builder $table_users,
    ) {
        $this->table_users = $table_users;
    }

    
    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function register(Request $request,Response $response, array $args): Response
    {   
        $name = $request->getParsedBody()['name'];
        $email = $request->getParsedBody()['email'];
        //search if email already exist
        $user = $this->table_users->where('email',$email)->first();
        if(isset($user)) {
            $data["status"] = "error";
            $data["message"] = 'Email already exist';
            $response->withHeader("Content-Type", "application/json")
                ->getBody()
                ->write(json_encode($data, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            return $response;
        }   
        //makeToken
        $expiration = new DateTime("+5000 minutes"); //all timing just for test
        $token = $this->makeToken($email,$expiration);
        //insert into users
        $this->table_users->insert([
            'name' => $name,
            'email' => $email,
            'token' => $token,
            'created_at' => new Datetime()
        ]);
    
        $data["token"] = $token;
        $data["expires"] = $expiration->getTimeStamp();
        $response->getBody()->write(json_encode($data,JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
        return $response;
    }
         /**
     * @param String $email
     * @param Datetime $expiration
     * @return String $token
     */
    private function makeToken($email,$expiration): String{
        $now = new DateTime();
        $jti = (new Base62)->encode(random_bytes(16));
        $payload = [
            "iat" => $now->getTimeStamp(),
            "exp" => $expiration->getTimeStamp(),
            "jti" => $jti,
            "sub" => $email
        ];
        $secret =  $_ENV['JWT_SECRET_KEY'];
        $token = JWT::encode($payload, $secret, "HS256");
        return $token;
    }
}
