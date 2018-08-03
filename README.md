# Fecon - External Cart API Module

## Main Extension GOAL 
The main goal of custom external cart is to:
  - Create cartId token (this generate the cart quote)
  - Add products into cart using the previous generated cartId (quote)
  - Redirect to Magento 2 **"Fecon"** site cart page to continue to checkout and buy items added into the cart.


## Authorization Access Token

You will need first generate the an API integration in Magento 2 site before you can be able to consume the endpoints, to do that follow this: https://devdocs.magento.com/guides/v2.0/get-started/create-integration.html

Then when you send the request you'll need to use the following header:
```
[{"key":"Authorization","value":"Bearer <access-token>","description":""}]
```

where **<access-token>** should be something similar to: **fd35xyhc2cun28w39prottpekbvrv12e**

If the above token is not sent, you will receive an **Exception** as output with following message: **Authorization required.**

## Available API Methods

You can easily check this in the **app/code/Serfe/ExternalCart/Api/CartInterface.php** file.

  * **GET: createCartToken():** Create and get new token of the created guest cart.
  * **GET: getCartToken():** Get the token of the recently created guest cart from session.
  * **GET: getCartInfo($cartId):** Get the cart information (items added, etc)
  * **POST: addProductIntoCart():** Function to add products into guest cart.
  * **GET: getCartUrl():** Function to get the cart url to access to the guest cart (with previously generated token).


## Available routes

  * **GET  /V1/external-cart/create-cart-token/**
  * **GET  /V1/external-cart/get-cart-token/**
  * **GET  /V1/external-cart/get-cart-info/:cartId**
  * **POST /V1/external-cart/add-product/**
  * **GET  /V1/external-cart/get-cart-url/**
  * **GET  /V1/external-cart/add-to-cart/**

## Usage Example (staging server)

### GET - Create Cart ID Token
``` php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://fecom.devphase.io/rest/V1/external-cart/create-cart-token",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <replace-with-access-token>",
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

```

### GET - Get Cart Information (cart quote and items added into cart) 

``` php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://fecom.devphase.io/rest/V1/external-cart/get-cart-info/{replace-this-with-previous-generated-cart-id}",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <replace-with-access-token>",
    "cache-control: no-cache",
    "cont: application/json"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}

```

### POST - Add items into cart (Add-Product)

You'll need to send the following POST data:


  * PARAM-KEY: **cartId**
  * PARAM-VALUE: **{cart-id}** should looks like the following token **7c6aa34c8ed9ccdb71f78f7b25d047b1** 

  * PARAM-KEY: **body**
  * PARAM-VALUE:

``` json

{
  "cartItem": 
  {
    "quoteId": "{cart-id}", 
    "sku": "BH-300",
    "qty": "1"
  }
}
```

### PHP - Curl Request

``` php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "https://fecom.devphase.io/rest/V1/external-cart/add-product",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"cartId\"\r\n\r\n7c6aa34c8ed9ccdb71f78f7b25d047b1\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"body\"\r\n\r\n{\"cartItem\": {\"quoteId\": \"7c6aa34c8ed9ccdb71f78f7b25d047b1\", \"sku\": \"BH-300\", \"qty\": \"1\"}}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <replace-with-access-token>",
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```

### POST - Add items into cart (Add-To-Cart)

**Note that this function doesn't need cartId param to be send, if doesn't exist is just created and then stored in session**


  * PARAM-KEY: **body**
  * PARAM-VALUE:

``` json

{
  "cartItem":
  { 
    "sku": "BH-300",
    "qty": "1"
  }
}

```

### PHP - Curl Request

``` php

<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_PORT => "8020",
  CURLOPT_URL => "http://development.fecon:8020/rest/V1/external-cart/add-to-cart/",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "POST",
  CURLOPT_POSTFIELDS => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"body\"\r\n\r\n{\"cartItem\": {\"sku\":\"BH-074\",\"qty\":\"1\"}}\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <replace-with-access-token>",
    "cache-control: no-cache",
    "content-type: multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```

### GET - Get Cart Url

``` php
<?php

$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => "http://fecom.devphase.io/rest/V1/external-cart/get-cart-url",
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => "",
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 30,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => "GET",
  CURLOPT_HTTPHEADER => array(
    "authorization: Bearer <replace-with-access-token>",
    "cache-control: no-cache"
  ),
));

$response = curl_exec($curl);
$err = curl_error($curl);

curl_close($curl);

if ($err) {
  echo "cURL Error #:" . $err;
} else {
  echo $response;
}
```
