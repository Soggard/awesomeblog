#API Symfony

###Requierments

- Make sure you have PHP 7 and composer installed on your machine.
- Make sure you have a Mysql server running.
- Set up or override the *.env* file as *.env.local* with your database configuration.
- Install the vendor :
>composer install
- Create the database :
>php bin/console doctrine:database:create
- Create the tables :
>php bin/console doctrine:schema:update --force
- Populate the database :
>php bin/console doctrine:fixtures:load
- Generate public and private RSA keys
>mkdir -p config/jwt

>openssl genrsa -out config/jwt/private.pem -aes256 4096

>openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem
- Update the JWT_PASSPHRASE value in your *.env* or *.env.local* file (default value is "password").

###To start server

>php bin/console server:run

The server runs at the URL *http://127.0.0.1:8000*

###Authentication

To log in :

- Login : admin
- Password : admin


###Documentation

All available webservices 


###TODO

- Read documentation again
- Test everything again