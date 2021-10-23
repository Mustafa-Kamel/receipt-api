# Receipt API

## Overview
This app presents the backend project for an invoice calculator API that can be used as a plugin in ecommerce app for handling the invoice issuing operation, it supports pricing a cart of multiple products from different countries, calculating subtotal, shipping fees, value added taxes and adding eligible discounts giving a total detailed invoice in USD.

## Description
We have a list of products each one has (title, price, weight, type/category, country) and each country has a different shipping rate for a weight of 100 grams.

Moreover the system should support handling offers that should apply discounts to some products/types on different conditions, these discounts have different values and different rules when they should be applied to the invoice e.g. they should be applied when buying a predefined number or minimum number of specific items or types, they may have a fixed value or may be a percentage of the price of one, many products or the shipping fees. the offers should have a date range within it they must be valid and expire after it.


## Requirements
Add an interface to price a cart of products from different countries, accept multiple products, combine offers, and display a total detailed invoice in USD as well.


## Proposed Solutions
-  **Command Line Interface**:
  A command that receives a list of products as args and prints the invoice out on the screen, but the problem with this solution that will be available just locally and will not provide a convenient way for interacting with the system by many users.

-  **RESTful API Endpoint**:
  Restful API endpoint that returns a detailed invoice for the products specified in the request payload. this assumption is more flexible, standart and provide availability for large number of users at the same time.


## Technical Design
To put things up together let's start from the business logic and how we should transfer this idea alive.

### Steps
- The app will have a single endpoint `POST: /api/cart/new` that receives a input in json format containing multiple items and their counts, the input should be in the following format:
```json
{
    "items": [
        {
            "id": 1,
            "count": 1
        },
        {
            "id": 2,
            "count": 2
        }
    ]
}
```

- Then data will be validated (format, correctness, item existence) and return validation errors if any.
- The data of the passed items will then be retrieved from the database.
- Check if we already have enough quantity from each item in the inventory.
- The invoice will be calculated including (subtotal, shipping fees, taxes).
- The cart will be checked if there any valid offers can be applied on it according to each offer rule.
- Discounts will be calculated if any and added to the invoice details.
- Return a json response with the items data and detailed receipt.
```json
{
    "items": [
        {
            "id": 1,
            "title": "T-shirt",
            "count": 1,
            "price": "30.99",
            "weight": 200,
            "type": "Tops",
            "country": "United States of America",
            "country_code": "US"
        },
        {
            "id": 2,
            "title": "Blouse",
            "count": 2,
            "price": "10.99",
            "weight": 300,
            "type": "Tops",
            "country": "United Kingdom",
            "country_code": "UK"
        }
    ],
    "total_items_count": 3,
    "receipt": {
        "Subtotal": "$52.97",
        "Shipping": "$13",
        "VAT": "$7.4158",
        "Discounts": {
            "$10 of shipping": "-$10"
        },
        "Total": "$63.3858"
    }
}
```

## Database Design
The app have the following datatypes (Products, Countries, Offers), each product may belong to a classification/category, so we will have the following tables:
- items (for storing products)
- itemtypes (for storing product categories)
- countries (for storing countries that items belong to)
- offers (for storing the offers, the rules when they apply and the discount of each one)

### Countries Table
| field       | datatype                 |
| ----------- | ------------------------ |
| id          | bigint auto increment pk |
| title       | varchar                  |
| code        | varchar                  |
| ship_rate   | decimal                  |
| ship_weight | float                    |
| created_at  | timestamp                |
| updated_at  | timestamp                |

### Itemtypes Table
| field      | datatype                 |
| ---------- | ------------------------ |
| id         | bigint auto increment pk |
| title      | varchar                  |
| created_at | timestamp                |
| updated_at | timestamp                |

### Items Table
| field      | datatype                 |
| ---------- | ------------------------ |
| id         | bigint auto increment pk |
| title      | varchar                  |
| price      | decimal                  |
| weight     | float                    |
| in_stock   | smallint                 |
| type_id    | bigint                   |
| country_id | bigint                   |
| created_at | timestamp                |
| updated_at | timestamp                |

### Offers Table
| field            | datatype                 |
| ---------------- | ------------------------ |
| id               | bigint auto increment pk |
| title            | varchar                  |
| applied_on_type  | varchar                  |
| applied_on_id    | bigint                   |
| count_range_min  | smallint                 |
| count_range_max  | smallint                 |
| discount_on_type | varchar                  |
| discount_on_id   | bigin                    |
| discount_type    | enum                     |
| discount_value   | decimal                  |
| valid_from       | timestamp                |
| expires_at       | timestamp                |
| created_at       | timestamp                |
| updated_at       | timestamp                |

### Database Relationships
- Countries and Items: OneToMany descripes the item belongs to which country.
- ItemTypes and Items: OneToMany descripes the item belongs to which itemtype/category.
- ItemTypes/Items and Offers OneToMany (Polymorphic) "applied_on" descripes the rule when an offer should apply on an item or itemtype.
- ItemTypes/Items and Offers OneToMany (Polymorphic) "discount_on" descripes which item/itemtype/shipping the discount should be deducted from.

**Notice:** I have decided to build this app using Laravel framework and mysql.

## Inatallation

- Clone this repo on you machine then cd in the project directory.
```bash
cd receipt-api
```
- Run the following command to get the dependencies installed.
```bash
composer update
```
- Create the environment file.
```bash
cp .env.example .env
```

- Create a mysql database and add its name and credentials to the `.env` file you just created in the app root directory, and feel free to add any custom configurations.

- *[optional]* Add the `VAT` to the .env file. 

- After you have created a new database and added its credentials to the app config run the app migrations.
```bash
php artisan migrate
```
- Seed the database with the test data.
```bash
php artisan db:seed
```
- Then run the following command to generate the app key.
```bash
php artisan key:generate
```
- Once its done go ahead and start the server
```bash
php artisan serve
```

Now the app is ready to use.

## Usage
You can use the app by sending POST requests to the `/api/cart/new` endpoint using a payload like the mentioned above.

### What happens when you send the request
After laravel bootstraps a new app it will redirect the request to CartController::new method and the request will be validated by the CartRequest and the validated items data will then be retrieved from the database using their ids if there are no duplicated ids, each item data will be then appended by its count received in the request.

The CartController::new method will then pass the collection to the ItemCollection to produce the output data but before it will do that it will create a new instance from the Receipt class which will do all the calculations behind the scenes using the collected items data. The data generated from the Receipt class will then be used as an output which the ItemCollection generates. ItemCollection will use the ItemResource class to format each item then return the overall data as a json response with 200 status code to the user.

**Receipt Class**
The Receipt class main job is to calculate generated the invoice details separating the complexity away from the CartController, it will calculate the subtotal, shipping fees, vat and total items count then it will retrieve the offers that can be applied on the cart depending on the items in the cart and apply the discount on its related item/items if the count of the bought items is greater than or equal to the the count_range_min field of the offer.

Note that some offers have count_range_min and max as the same value, in this case the offer maybe applied at the same cart multiple times if the count of items that's related to the offer rule is a multiple of its count_range_min while count_range_min == count_range_max.
The offers that must be apply one time should have the count_range_max as a very big value (e.g. prefeably 65535 the max that an unsigned smallint field can hold).


**Notic:** When you run the following command the database will be loaded by a preconfigured data saved at `storage/app/fixtures/` you can edit it if you want.
```bash
php artisan db:seed
```

## Testing
The project has api testing that covers the following cases:
- Testing the api with invalid data.
- Testing the api with items that are not eligible for any offers.
- Testing the api with items that are eligible for offers.
- Testing the api with items that are eligible for offers multiple times.

To run test use the following command:
```bash
php artisan test
```

## Enhancements
The following things I will implement if I spent more time on this project or in a real life project:

- Shipping rates should be isolated to a separate table and make two ManyToMany relationships between the countries and the shipping_rates "from" and "to", this is useful if any country have different rates when she ships to another country.
- The relationships betweent the items/itemtypes tables and the offers table maybe changed from OneToMany to ManyToMany this will give us the ability to add offers that apply on multiple items from different categories or make a discount on many of them.
- Check each product availability in inventory before proceeding with the request (for that I added the in_stock column to the items table)
- Add a column "priority" to the offers table, this will be used if the cart got multiple discounts on the same item and this is determined by the business logic more.
- Add a promo code support to the system by adding a new type of offers, this could be implemented by adding a new promo_code to the offers table and another offer_type columng and handle each one differently.
- Add more test coverage and unit tests.