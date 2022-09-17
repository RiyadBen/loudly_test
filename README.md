# Loudly Assessment Test
Without the use of any REST Bundles like `FriendsOfSymfony/FOSRestBundle`
This is an implementation of Symfony 4 REST API using JWT (JSON Web Token). It is created with best REST API practices in mind. 


Regarding project itself. It was built with this in mind:
- Thin-controller 
- TDD approach (this project was mainly created by first creating tests and then actual code using
  red-green-refactor technique).
- SOLID principles.
- Speaking names and other good design.
- Most business logic is moved from controllers to corresponding services.

## What this REST API is doing?

This is a simple invitation system, which is implemented as REST API which uses 
JWT (JSON Web Token) tokens to access various endpoints. 
You can create a User, send and invite to another user containing a message.The sender can cancel the invite
the receiver can either reject the invite or accept it. This is a simple project which is used to demonstrate 
how to create REST API services and secure access to endpoints using JWT tokens. 
See "Usage/testing" section.

### Business Login
- A User Can Signup
- A User Can Login
- A User Can Create an Invite to Another User
- A Sender (User) can Cancel a sent invite.
- A Receiver (User) can Reject Or Accept a received Invite.
- A Sender (User) Cannot Accept or Reject Their Own Invite.
- A Receiver (User) cannot Cancel a received Invite
- A User can View their invitations to other users.
- A User can View their received invitations from other user.
## Technical details / Requirements:
- Current project is built using Symfony 4.1 framework
- It is based on microservice/API symfony project (symfony/skeleton)
	- https://symfony.com/download
- PHPUnit is used for tests	
	* Note: it is better to run built-in PHPUnit (vendor/bin/phpunit), not the global one you have on your system, 
			  because different versions of PHPUnit expect different syntax.		 
- PHP 7.4.29 is used so you will need something similar available on your system (there are many options to install it: Docker/XAMPP/standalone version etc.)
- MariaDB (MySQL) is required (10.1.33-MariaDB was used during development)
- Guzzle composer package is used to test REST API endpoints

## Installation:
    - git clone https://github.com/RiyadBen/loudly_test.git

    - go to project directory and run: composer install
    
    * at this point make sure MySQL is installed and is running	
    - open .env filde in project directory (copy .env.dist and create .env file out of it (in same location) if not exists)
    
    - configure DATATABSE_URL
        - This is example of how my .env config entry looks: DATABASE_URL=mysql://root:@127.0.0.1:3306/invites # user "root", no db pass

    - go to project directory and run following commands to create database using Doctrine:
        - php bin/console doctrine:database:create (to create database called `invites`, it will figure out db name based on your DATABASE_URL config)		
        - php bin/console doctrine:schema:update --force (executes queries to create/update all Entities in the database in accordance to latest code)
	- start the server with php bin/console server:run

## Implementation details:
- In terms of workflow the following interaction is used: to get the job done for any given request usually something like this is happening: Controller uses Service (which uses Service) which uses Repository which uses Entity. This way we have a good thin controller along with practices like Separation of Concerns, Single responsibility principle etc.
- App\EventSubscriber\ExceptionSubscriber is used to process all Symfony-thrown exceptions and turn them into nice REST-API compatible JSON response (instead of HTML error pages shown by default in case of exception like 404 (Not Found) or 500 (Internal Server Error))
- App\Service\ResponseErrorDecoratorService is a simple helper to prepare error responses and to make this process consistent along the framework. It is used every time error response (such as status 400 or 404) is returned.
- In order to make any controller JWT secured (to make every action of it accessible only to authenticated users).
- All application code is in /src folder
- All tests are located in /tests folder

## Usage/testing:
### Note 1: Please Make Sure Server is Running
### Note 2: Tests Use by default port 8000 , if you need to specify another port please check `BaseTestCase.php`
We can use POSTMAN to access all endpoints:
    
    1) Create API user to work with:
    
    method: POST
    url: http://localhost:8000/users/create
    Body (select raw) and add this line: {"email": "sender@api.com", "password": "sender"}
    
    you should get the following response on successful user creation:
    
    {
        "data": {
            "email": "sender@api.com"
        }
    }
    
    or 
    
    {
        "error": {
            "code": 400,
            "message": "User with given email already exists"
        }
    }
    
    if user with given email already exists.
    
    2) Authenticate (acquire JWT token) for just created user to be able to make REST API calls: 
    
    method: POST
    url: http://localhost:8000/api/authenticate
    Authorization type: Basic Auth
    username: sender@api.com
    password: sender
    
    {
        "data": {
            "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOiJzZW5kZXJAYXBpLmNvbSIsImVtYWlsIjoic2VuZGVyQGFwaS5jb20iLCJpYXQiOjE2NjM0MjU1ODEsImV4cCI6MTY2MzQyOTE4MX0.ZYq9MHSeP726uAmlwnuUjLEqXIdRCsBBNYc0VJ2IXTo"
        }
    }
    
    copy JWT token you got (without quotes)	to clipboard
    
    3) Use REST API using your JWT token
    
    - Create an invite:
    method: POST
    url: http://localhost:8000/api/invites
    Body (select raw) and add this line: 
    
    {"receiver": "receiver@api.com","message":"You're invited!"}
    
    Header:
    - Add header Key called "Authorization"
    - Add value: Bearer <your_jwt_token_value> (note there is space between "Bearer" and your JWT)
    
    Response should look similar to this:
    
    {
		"data": {
			"id": 2,
			"sender": "sender@api.com",
			"receiver": "receiver@test.com",
			"message": "You're invited!",
			"status": "Waiting"
		}
    }
    
    - Change Invite Status ("Accepted","Rejected","Cancelled")
    
    method: POST
    url: http://localhost:8000/api/invites/{id} (where {id} is id of existing invite you want to modify, for example http://localhost:8000/api/invites/2)
    Body (select raw) and add this line: 
    {"status": "Accepted"}
    
    Header:
    - Add header Key called "Authorization"
    - Add value: Bearer <your_jwt_token_value> (note there is space between "Bearer" and your JWT)	
    
    Response should look similar to this:
    
    {
        "data": {
			"id": 2,
			"sender": "sender@api.com",
			"receiver": "receiver@api.com",
			"status": "Accepted",
			"message": "You're Invited!"
        }
    }
    
    -