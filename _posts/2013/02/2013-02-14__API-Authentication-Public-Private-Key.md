---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: API Authentication: HMAC with Public/Private Hashes
tags: api,authentication,publickey,privatekey,hmac
summary: Implementing a public/private HMAC hashing layer to your API helps authenticate and validate the request.
---

API Authentication: HMAC with Public/Private Hahes
--------------

{{ byline }}

> This is the first part of a series of posts on authentication methods you can
> use with your APIs. This article will cover the use of public/private key pairings
> to validate the request.

Dealing with authentication with APIs is a bit different than how you handle authenticating
a frontend user. With a web-based login, you can ask the user for a username and
password (or some other identifier) and validate those on submission. With an API,
though, you can't really get that level of interaction. You need a way to be able to
validate not only the user making the request but also the contents of the request
itself.

#### Public and Private Hashes

In this first part of the series, we're going to look at a pretty simple method of
validating both of these things - using the pairing of a public and private key
along with [HMAC hashing](http://en.wikipedia.org/wiki/Hash-based_message_authentication_code)
to perform the validation. Let's start with a look at these public and private hashes, though.

I call them "hashes" but in reality you probably want to use something that's a bit
more difficult to figure out than just a user-defined set of strings (in fact, I'd
**strongly** advise you not allow the user to have any input into the key creation).
Since these values are only used for the hashing and validation of the content, they
don't necessarily have to be related to anything about the user. In some of the
implementations I've seen they just generate a random value and make a hash out of
it. With PHP, you can do something like this:

`
<?php
$hash = hash('sha256', openssl_random_pseudo_bytes(32));
?>
`
This method uses the OpenSSL extension functionality (not usually included by default in
most PHP distributions, so you'd need to add it in) to generate a randomized value
and then passes it into the `hash` function to be made into a `sha256` hash. You
can use this same method to generate both hashes and then store them in your database
related to the user.

If you don't have the OpenSSL extension installed or can't because you're on something
like shared hosting, you can fall back on something like [mt_rand](http://php.net/mt_rand),
though the "randomness" won't be quite as good:

`
<?php
$hash = hash('sha256', mt_rand());
?>
`

That's really all there is to it. Of course, you can always add in your own salts
or other strings into the hash generation, but for the HMAC method, it's not really
required that it relates to the user.

#### The HMAC Hash

Now that we have some hashes generated for our users, lets take a look at HMAC hashing
and what it's for. The idea behind HMAC (hash-based message authentication code)
hashing is pretty simple:

1. Using a "private key" only known to the user (and the system), they create a hash based on the contents of the request
2. This "content hash" is sent along with another value (in our case, a "public key") to the server
3. The server then takes the "public" hash and locates the user it matches and pulls their "private" key
4. The software then uses this "private" key to hash the message sent and tries to match that against
    the "content hash" that was sent with the request

There's two benefits of this that I've already hinted at - the first is that, by using
the "public" hash as a unique identifier, we can validate that the user is in our system
and is allowed access. The second comes after the hashes are compared. This ensures that
the content of the request that the server sees is the same content that was originally
sent from the client, preventing things like Man-in-the-Middle attacks or any other kind
of possible message modification.

PHP has a handy function to help you generate these HMAC hashes too - [hash_hmac](http://php.net/hash_hmac).
It takes in three parameters: the hash algorithm to use (like `sha256`), the contents
of the request and the "private" key to use to generate the hash. Signing requests
like this can use just about any hash type you choose, it just needs to be the same
on both sides. This makes this method language-independent as well - just because
our examples are written in PHP, it doesn't mean you have to use it to make the request.
Most other languages have functionality for generating HMAC hashes (including Python, Ruby and
Java) or libraries/packages can be found to drop in and help out.

#### An Example in PHP

Time for the fun part - putting these ideas into practice. Below is an example of
both the client and server (REST API) side of the request so you can get a more
complete picture of what's happening where. The example uses the [curl](http://php.net/curl)
functionality that comes with most PHP installs, but you could always use a more
low level [socket request](http://php.net/fsockopen) if you'd like.

First up is the code for the client - it takes the public/private hashes for the user,
makes the hash for the value in `$content` and adds that hash to the HTTP headers
it sends for the request.

`
<?php
$publicKey  = '3441df0babc2a2dda551d7cd39fb235bc4e09cd1e4556bf261bb49188f548348';
$privateKey = 'e249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e';
$content    = json_encode(array(
    'test' => 'content'
));

$hash = hash_hmac('sha256', $content, $privateKey);

$headers = array(
    'X-Public: '.$publicKey,
    'X-Hash: '.$hash
);

$ch = curl_init('http://test.localhost:8080/api-test/');
curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$content);

$result = curl_exec($ch);
curl_close($ch);

echo "RESULT\n======\n".print_r($result, true)."\n\n";
?>
`

If you take a quick glance further down the article, you'll see the server side code
that uses Slim to handle the request. If the hashes match, it outputs "match" so
our `$result` should contain that string. If it doesn't, something is up...check the values
of the hashes you're using and be sure your content is making it into the `hash_hmac` function
call.

If you're making a request to a RESTful API (as we are in this case) and you want to check and be
sure it's a successful response, you can parse the header information for a `200` response code:

`
<?php
// add this to the curl options to get the headers in the response
curl_setopt($ch,CURLOPT_HEADER,true);

// then you can get the headers and check
list($headers, $content) = explode("\r\n\r\n", $result);
$headers = explode("\r\n", $headers);

if (strpos($headers[0], '200') !== false){
    echo 'Successful request!';
}
?>
`

We've looked at the client side of things so far - let's look on the server side.
I'm going to use the [Slim microframework](http://www.slimframework.com/)
to keep things simple. It's a great framework for small applications, but I wouldn't
recommend it if you need something a bit more "super powered" for your application.
For our needs, though, it makes prototyping out the REST API easy.

To get it installed, you can add this to your `composer.json` file:

`
{
    "require": {
        "slim/slim":"2.*"
    }
}
`

This will pull the Slim's files into your `vendor/` directory, ready for use.

> For more information on using Composer, see [getcomposer.org](http://getcomposer.org).

Our client is sending over a few things to the server - the `X-Public` header we can
use to locate the user (in this case, it'd be stored in a database related to the user),
the `X-Hash` header that's the result of the client-side hashing with the private
key and the actual content for the request (our `json` string).

In out example, we've hard-coded the `privateKey` value, but you'd want to find the user
in your system and pull out the private key associated with them to use it in re-hashing
the content.

`
<?php
require_once 'vendor/autoload.php';

$app = new \Slim\Slim();
$app->post('/', function() use ($app){

    $request = $app->request();

    $publicHash  = $request->headers('X-Public');
    $contentHash = $request->headers('X-Hash');
    $privateKey  = 'e249c439ed7697df2a4b045d97d4b9b7e1854c3ff8dd668c779013653913572e';
    $content     = $request->getBody();

    $hash = hash_hmac('sha256', $content, $privateKey);

    if ($hash == $contentHash){
        echo "match!\n";
    }
});
?>
`

If all goes well, you should get back a `200` response code from the request and see
the string `"match"` in the return from the curl request.

#### Challenges of HMAC

HMAC is a pretty simple kind of authentication and message signing to implement in your
API. It doesn't require much processing overhead and doesn't rely too much on interacting
with outside authentication mechanisms (like a [two-factor system](/tagged/twofactor) might).
There is a challenge with using the system though - the key handling.

Since the user (or client system) needs to know both their public and private hashes, they
have to be stored somewhere that their system can access them. Usually this means hard-coding
them into a configuration file, either inside the application or as a server configuration
value somewhere. It's out of your control how they store it, so depending on their choices,
it could make your system less secure. They have the "keys to the kingdom" for their account
and if anyone can access them, that could leave you vulnerable.

There's also another smaller issue that really just boils down to personal preference. Other
authentication methods offer a bit easier interface for the end-user. It can be
a bit more difficult using a key pair like this also because it means sending this information
out to the user instead of them using something they already have (like an OAuth account)
to make the request...but that's a topic for another article.

Hopefully this has helped you get a grasp on what HMAC hashing is and how it might be used
in your API to validate users and their requests.

#### Resources

- [HMAC function in PHP](http://php.net/hash_hmac)
- [Wikipedia on HMAC](http://en.wikipedia.org/wiki/Hash-based_message_authentication_code)
- [HMAC hashing in detail](https://tools.ietf.org/html/rfc2104)


