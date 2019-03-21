#API Symfony

###Requirements 

- Make sure you have a Mysql server running on your machine.
- Make sure you have PHP 7 installed on your machine.
- To create the database :
>php bin/console doctrine:database:create
- To create the tables :
>php bin/console doctrine:schema:update --force
- To populate the database :
>php bin/console doctrine:fixtures:load



###Server

>php bin/console server:run

The server runs at the URL *http://127.0.0.1:8000*


###TODO

- Populate database (DataFixture)
- CRUD for Categories
- CRUD for Articles
- CRUD for Authors
- CRUD for Comments
- Secure with JWT
- Documentation