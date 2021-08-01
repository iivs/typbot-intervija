# Typbot interview homework
Purpose of this project is to demonstrate basic knowledge of [Laravel](https://laravel.com/) framework. Project uses
[Laravel](https://laravel.com/) framework and [Laravel Sanctum](https://laravel.com/docs/8.x/sanctum) authentication.
You can create a user, log in, log out, create a product with attributes, show one product, list all products, update
a product and change product attributes or remove them, show attributes of one product and delete a product. Project
uses only API, so there are no web routes. Only the default [Laravel](https://laravel.com/) welcome page.

#
## Task list:
1. Create a [Laravel](https://laravel.com/) project;
2. implement authentication;
3. create a table "products" with columns: id, name, description, created_at, updated_at + soft deletes;
4. create a table "product_attributes" with columns: id, product_id, key, value, created_at, deleted_at. key field may
   contain attributes like "weight", "taste", "height", "length" etc;
5. create [CRUD](https://www.codecademy.com/articles/what-is-crud) for products;
6. create API which in [JSON](https://www.json.org/json-en.html) format which returns
   a) all products;
   b) specific product attributes.

#
## Set up
- Set up [PHP 7.4](https://www.php.net/releases/7_4_0.php) and database of choice. For testing purposes I used
  [MySQL 5.7](https://dev.mysql.com/downloads/mysql/5.7.html)
- Use your own web server or use `php artisan serve`. For the purpose of this example, let's use http://localhost:8000

#
## Usage
- The is no web interface. Only main default [Laravel](https://laravel.com/) welcome page. So no edit forms, just
  [API](https://www.ibm.com/cloud/learn/rest-apis) methods.
- There are public routes and protected routes. Only authenticated users, can create, update and delete products.
  Unauthenticated users can view all products, view one product and view attributes of one product.
- use, for example, [Postman](https://www.postman.com/) to send requests to
  [API](https://www.ibm.com/cloud/learn/rest-apis);
- if you get lost, list possible routes via `php artisan route:list` to know what methods and URIs are accepted.

#   
### Authentication
#### Create a user
- Create a [POST](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST) request to
  http://localhost:8000/api/register and enter `name`, `email`, `password` and `password_confirmation`;
- the e-mail should be in a valid format and unique;
- passwords (both fields `password` and `password_confirmation`) must match;
- in case wrong data is given, you will receive an error message;
- example of JSON request to create a new user:
```JSON
{
    "name": "User one",
    "email": "john@doe.com",
    "password": "abc123",
    "password_confirmation": "abc123"
}
```
- example output of successful registration:
```JSON
{
    "user": {
        "name": "User one",
        "email": "john@doe.com",
        "updated_at": "2021-08-01T11:41:56.000000Z",
        "created_at": "2021-08-01T11:41:56.000000Z",
        "id": 3
    },
    "token": "6|88ws1hoHt7eqiV471TNXmnv10dgGQd4KhZJidcLw"
}
```
- after successful registration a new user will be created and given a personal access token which then can be used to
  access protected routes. If using, for example, [Postman](https://www.postman.com/) then in the Authorization tab
  choose "Bearer Token" to use it in procted routes to create, update and delete products;

#### Log in
- If you have just created a user, you will be automatically logged in;
- If at some point in time you have logged out, you can log in with your e-mail and password using a
  [POST](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST) request to http://localhost:8000/api/register
  and provide `email` and `password`;
- in case a non-existent e-mail was given or incorrect password, you will receive an error message;
- after successful log in you will again be given a personal access token which then can be used to
  access protected routes.

#### Log out
- If you are logged in, you can log out;
- if you are not logged in, and you will try to log out, you will receive an error message;
- to log out use a [POST](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST) request to
  http://localhost:8000/api/logout and use your personal access token.
- if successfully logged out, you will receive a success message.

#
### Products and attributes
#### Create a product
- In order to create a product, you have to be logged in. Otherwise you will receive and error message;
- log in first using e-mail and passwod;
- to create a product make a [POST](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST) request to
  http://localhost:8000/api/products
- allowed product parameters:
  - `name` (string, uninque, required)
  - `desciption` (string, optional)
  - `attributes` (array of objects, optional)
- allowed attribute parameters:
  - `key` (string, distinct, required)
  - `value` (string, optional)
- product name must be unique. It is not allowed to create a product with same name. Product names are case-insensitive.
  If a product with such name exists, you will be receive an error message. Attribute `key` parameter must be
  distinct.
- example of JSON request to create a product without attributes and just product properties:
```JSON
{
    "name": "My product one",
    "description": "This is the description of product one"
}
```
- example of JSON request with attributes:
```JSON
{
    "name": "My product two with attributes",
    "attributes": [
        {
            "key": "height"
        },
        {
            "key": "length",
            "value": "3"
        },
        {
            "key": "weight",
            "value": "10kg"
        }
    ]
}
``` 
- if everything was correct, you will be able to see product and attributes (if given) in the response:
```JSON
{
    "name": "My product two with attributes",
    "updated_at": "2021-08-01T11:15:51.000000Z",
    "created_at": "2021-08-01T11:15:51.000000Z",
    "id": 46,
    "attributes": [
        {
            "key": "height",
            "created_at": "2021-08-01T11:15:51.000000Z",
            "product_id": 46,
            "id": 37
        },
        {
            "key": "length",
            "value": "3",
            "created_at": "2021-08-01T11:15:51.000000Z",
            "product_id": 46,
            "id": 38
        },
        {
            "key": "weight",
            "value": "10kg",
            "created_at": "2021-08-01T11:15:51.000000Z",
            "product_id": 46,
            "id": 39
        }
    ]
}
```

#### List products
- You do not have to be logged in to list all products;
- to list all products use a [GET](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET) request to
  http://localhost:8000/api/products
- the product list does not include attributes of each product. According to task, they are requested separately.
- if request was success you will be able to see the list of products.

#### Updating product
- In order to update a product, you have to be logged in. Otherwise you will receive and error message;
- log in first using e-mail and passwod;
- To update a product use a [PUT](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT) request to
  http://localhost:8000/api/products/{id}
   where `{id}` is an ID of product;
- if product with such ID does not exist, you will be receive an error message;
- allowed product parameters:
  - `name` (string, optional)
  - `desciption` (string, optional)
  - `attributes` (array of objects, optional)
- allowed attribute parameters:
  - `key` (string, distinct, required)
  - `value` (string, optional)
- product mame must be unique. This means that if you are updating product name and the name exists for another product,
  you receive an error message;
   
- example of JSON request without attributes, just product properties:
```JSON
{
    "description": "This is the new description of product"
}
```
- example of JSON request to change name and additionally remove attributes from product:
```JSON
{
    "name": "New product name",
    "attributes" : []
}
```
- example of JSON request to update attributes from product:
```JSON
{
    "attributes": [
        {
            "key": "height",
            "value": "3cm"
        },
        {
            "key": "length",
            "value": "30cm"
        }
    ]
}
```
- the above example will replace the existing attributes with new ones.

#### Show one product
- You do not have to be logged in to show one product;
- to show one product use a [GET](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET) request to
http://localhost:8000/api/products/{id} where `{id}` is an ID of product;
- if product with such ID does not exist, you will receive an error message;
- if product is found, product properties will be returned;
- attributes are returned separately using a different route.

#### Show attributes of a product
- You do not have to be logged in to show attributes one product;
- to dhow attributes of a single product use a [GET](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET)
request to http://localhost:8000/api/products/{id}/attributes where `{id}` is an ID of product;
- if product with such ID does not exist, you will receive an error message;
- if product is found, product attributes will be returned;

#### Delete a product
- In order to delete a product, you have to be logged in. Otherwise you will receive and error message;
- log in first using e-mail and passwod;
- to delete a product with all of its attributes use a
  [DELETE](https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE) request to
  http://localhost:8000/api/products/{id} where `{id}` is an ID of product;
- if product with such ID does not exist, you will receive an error message;
- if product is found and deleted, you will receive a success message.

#
## Generating test data
To generate products, attributes and users, you may use the database seeder:
```
php artisan db:seed --class=DatabaseSeeder
```
This will generate fake products, attributes and users. The users will have constant password "password". This will not
generate tokens and log them it. You can log in aftearwards using an e-mail that was generated and password "password".

#
## What is not included or could be improved.
- Missing custom message for `attributes.*.key`.
- since project is missing `updated_at` column for attribues, there is no reason to create a separate method to update
specific attributes, since we will not know when or if they were updated;
- date format in not user friendly and can be improved in responses.