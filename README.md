#API Symfony

###Requierments

- Make sure you have PHP 7 and composer installed on your machine.
- Make sure you have a Mysql server running.
- Set up or override the *.env* file as *.env.local* with your database configuration.
- Install the vendor :
>composer install
- Create the database :
>php bin/console doctrine:database:create
- Create the tables
>php bin/console doctrine:schema:update --force

###To start server

>php bin/console server:run

The server runs at the URL *http://127.0.0.1:8000*


###TODO

- Populate database
- CRUD for Categories
- CRUD for Articles
- CRUD for Authors
- CRUD for Comments
- Secure with JWT