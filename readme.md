## PHP Challenge

This is a php challenge for jobsity project forked from https://git.jobsity.com/php-challenge that includes jwt auth , migrations,eloquent ORM and Monolog.

## Prepare:

1. `$ git clone https://git.jobsity.com/earqq/php-challenge`
2. `$ cd my-app`
3. Copy .env file `cp .env.sample .env`
4. Install dependencies `composer install`
5. Install mysql database and create a new database in mysql console  `create database {dbname};`
6. Change database and MailTrap settings in .env 
7. Run the migrations  `php vendor/bin/phoenix migrate`

### Run it:

1. `$ cd your-app`
2. Run the project  `composer start`
10. Browse to http://localhost:8080

## API endpoints

## GET
`get stock by code` [/stock] <br/>
`get history records by user` [/history] <br/>

## POST
`register a new user with auth jwt` [/register]<br/>


### POST /register
Register a new user for the next stocks querys, it use JWT auth for the login .

**Parameters**

|          Name | Required |  Type   | Description                                                                                                                                                           |
| -------------:|:--------:|:-------:| --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|     `email` | required | string  | Form Data Parameter,is the unique email for the user to register. <br/><br/>                                                                      |
|     `name` | required | string  | Form Data Parameter,is the name for user to register. <br/><br/> Not Default. <br/><br/>  

**Response**

```
// Email is already registered
{
    "status": "error",
    "message": "Email already exist"
}

or 

// Email is not register already
{
    "token":
    "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2NzM5MTE0MTAsImV4cCI6MTY3NDIxMTQxMCwianRpIjoiNlRQc01kTTg2NzhhMm1hQ0VSdUFCTCIsInN1YiI6ImVhcnExNEBnbWFpbC5jb20ifQ.05KbjmQv2N7vagbGDtvCIbr0mYAXsazZPhL0jBELZe0",
    "expires": 1674211410
}
```

### GET /stock
Get the value of the stock in the stock market, save the record into a sql database and send a mail to the auth user

**Headers**

|          Name | Required |  Type   | Description                                                                                                                                                           |
| -------------:|:--------:|:-------:| --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|     `Authorization` | required | Bearer {token}  | The user token for auth<br/><br/>   

**Parameters**

|          Name | Required |  Type   | Description                                                                                                                                                           |
| -------------:|:--------:|:-------:| --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|     `code` | required | string  | URL parameter, is the stock code to get the value from the stock market  (?code={stock_code}). <br/><br/>                                                                      |

**Response**

```
// Stock code don't exists
{
"status": "error",
"message": "Code dont exists"
}

or 

// The code exists, the stock value data was saved into database but there was an error with mail sending
{
"status": "error",
"message": "Error in mail sending: Failed to authenticate on SMTP server with username \"71e458f91ed03bs\" using 3
possible authenticators. Authenticator CRAM-MD5 returned Expected response code 235 but got code \"535\", with message
\"535 5.7.0 Invalid login or password\r\n\". Authenticator LOGIN returned Expected response code 250 but got an empty
response. Authenticator PLAIN returned Expected response code 250 but got an empty response.",
"data": {
"name": "APPLE",
"symbol": "AAPL.US",
"open": "132.03",
"high": "134.92",
"low": "131.66",
"close": "134.76"
}
}

or

// The code exists, the stock value data was save into database and mail was sended to the user
{
"name": "APPLE",
"symbol": "AAPL.US",
"open": "132.03",
"high": "134.92",
"low": "131.66",
"close": "134.76"
}

```

### GET /history
Get a history for the previous records for the auth users ordered by last to first

**Headers**

|          Name | Required |  Type   | Description                                                                                                                                                           |
| -------------:|:--------:|:-------:| --------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
|     `Authorization` | required | Bearer {token}  | The user token for auth<br/><br/>                                                                      |

**Response**

```
// The auth user don't have records
[]

or 

// The user have records 
[
{
"date": "2023-01-16 18:33:18",
"name": "APPLE",
"symbol": "AAPL.US",
"open": 132.03,
"high": 134.92,
"low": 131.66,
"close": 134.76
},
{
"date": "2023-01-16 18:31:53",
"name": "APPLE",
"symbol": "AAPL.US",
"open": 132.03,
"high": 134.92,
"low": 131.66,
"close": 134.76
},
{
"date": "2023-01-16 18:29:03",
"name": "APPLE",
"symbol": "AAPL.US",
"open": 132.03,
"high": 134.92,
"low": 131.66,
"close": 134.76
},
]

```