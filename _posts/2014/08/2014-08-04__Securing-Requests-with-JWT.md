---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Securing Requests with JWT (JSON Web Tokens)
tags: jwt,jwe,json,web,token
summary: JWTs can provide an extra layer of validation and protecton for you requests.
---

Securing Requests with JWT (JSON Web Tokens)
--------------

{{ byline }}

There's a technology that can help secure your applications that doesn't get a lot of press in the PHP community. When most people think of creating unique links for things like password reset emails, they immediately jump to the "make a hash and store it in the database" kind of solution. Usually this will come with the addition of a timestamp to ensure the hash isn't too old or when it was created. What if I told you that you could create something just as unique for your links and have it provide more context without much more overhead?

#### What they are

There's a standard out there that defines tokens that can be encoded (and even encrypted) to give context and rules to a processing engine for how that data should be handled. The [JSON Web Token (JWT)](http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html) specification defines a structure of "claims" or metadata about the JWT object. These claims can either be accompanied by custom claim data not defined in the spec. Let's look at an example of one in it's native JSON format:

`
{
	"iss":"http://example.org"
    "aud":"http://example.com"
    "iat":"1356999524
    "nbf":"1357000000
    "exp":"1405810922
    "jti":"id123456
    "typ":"https://example.com/register"
}
`

This might look at little confusing if you're not familiar with the claim types. Here's what each of these mean:

- **iss**: The issuer of the token
- **aud**: The audience that the JWT is intended for
- **iat**: The timestamp when the JWT was created
- **nbf**: A "not process before" timestamp defining an allowed start time for processing
- **exp**: A timestamp defining an expiration time (end time) for the token
- **jti**: Some kind of unique ID for the token
- **typ**: A "type" of token. In this case it's URL but it could be a media type like [these](https://www.iana.org/assignments/media-types/media-types.xhtml)

There's other claim types besides these (see the spec for those) that can be used to customize the claims made by the JWT. It's not limited to these claims, though. These claims are all "public claims", that is, they're known as a part of the common structure of a JWT. There's also "private claims" that you can include as a part of your claim set. These are usually things that are a bit more specific to the application you're connecting to. For example, in Google's [OAuth handling](https://developers.google.com/accounts/docs/OAuth2ServiceAccount) they define a "scope" claim containing the list of permissions the application creating the JWT wants to allow.

Some of the claim types, like "issuer" and "audience", can be used at face value, some of the other time-related ones require a little more processing. Any good JWT handler needs to check four things:

1. That the timestamp for the "created at" (iat) is *prior* to the current time and is valid.
2. That the "not process before" claim time *has* passed.
3. That the "not process before" time *has* passed.
4. That the "expires" time *has not* passed.

Unfortunately all four of these claims are *optional* so may not exist for validation. My personal suggestion is that, if they don't exist, kick back with an error.

#### How they're created

So, how does a JWT get from the JSON structure you see above to a string like:

<pre>
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsImV4cCI6MTQwNzAxOTYyOSwianRpIjoiaWQxMjM0NTYiLCJ0eXAiOiJodHRwczpcL1wvZXhhbXBsZS5jb21cL3JlZ2lzdGVyIiwidGVzdC10eXBlIjoiZm9vIn0.UGLFIRACaHpGGIDEEv-4IIdLfCGXT62X1vYx7keNMyc
</pre>

Let's break it down. First, we'll work backwards from this string into it's parts and how each is created.

First off, if you look closely you'll see that there's three special characters that are repeated: periods. In order to be a valid JWT result, there needs to be three of these. They split out the three parts of a JWT encoded result:

1. The header
2. The claims content itself
3. The signature created from data from both the header and claims

In our example above, the **header** is

<pre>
eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9
</pre>

The main **claims body** is in

<pre>
eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsImV4cCI6MTQwNTgxMDkyMiwianRpIjoiaWQxMjM0NTYiLCJ0eXAiOiJodHRwczpcL1wvZXhhbXBsZS5jb21cL3JlZ2lzdGVyIiwidGVzdC10eXBlIjoiZm9vIn0
</pre>

and the **signature** is

<pre>UGLFIRACaHpGGIDEEv-4IIdLfCGXT62X1vYx7keNMyc</pre>

So, what's the magic formula to come up with these strings? Keeping with the "working backwards" theme, lets revert these strings:

##### Break up the string

First we need to start with the full string and break it up into it's parts:

`
<?php
$jwtString = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC9leGFtcGxlLm9yZyIsImF1ZCI6Imh0dHA6XC9cL2V4YW1wbGUuY29tIiwiaWF0IjoxMzU2OTk5NTI0LCJuYmYiOjEzNTcwMDAwMDAsImV4cCI6MTQwNzAxOTYyOSwianRpIjoiaWQxMjM0NTYiLCJ0eXAiOiJodHRwczpcL1wvZXhhbXBsZS5jb21cL3JlZ2lzdGVyIiwidGVzdC10eXBlIjoiZm9vIn0.UGLFIRACaHpGGIDEEv-4IIdLfCGXT62X1vYx7keNMyc';

$parts = explode('.', $jwtString);
?>
`

##### Revert the base64

Once we have these, we need to revert them from their "base64" encoded versions. I put "base64" in quotes because it's not really a true version of the result PHP's [base64_encode](http://php.net/base64_encode). In this case, we're only replacing certain things. We also have to pad it out a bit so PHP's base64 handling can decode it:

`
<?php
function base64Decode($string)
{
    $decoded = str_pad($data,4 - (strlen($data) % 4),'=');
    return base64_decode(strtr($decoded, '-_', '+/'));
}

$header = base64Decode($parts[0]);
$body = base64Decode($parts[1]);
$signature = base64Decode($parts[2]);
?>
`

According to the bas64 encoding, the string has to be padded out with "=" (equals) to a multiple of four. This function is then applied to each section of the JWT string to revert it back into something we can use.

> **NOTE**: In using some of the other libraries out there to parse and generate the JWT strings, I noticed that it's not a true URL-encode and base64-encode on the data. There's a slight difference in the spec that modifies it slightly. If you look at the [psecio/jwt](https://github.com/psecio/jwt) library source you'll see what I mean.

Now that we have the translated versions of the various sections, we can start working with them. At this point, you should have some (hopefully valid) JSON strings to work with. In PHP, this means the super-handy [json_decode](http://php.net/json_decode) function can come into play:

`
<?php
$headerDecoded = json_decode($header);
$bodyDecoded = json_decode($body);
$sigDecoded = json_decode($signature);
?>
`

The result should be objects that look like our JSON example in the beginning:

<pre>
// The header
{
    "typ":"JWT",
    "alg":"HS256"
}

// The body
{
    "iss":"http:\/\/example.org",
    "aud":"http:\/\/example.com",
    "iat":1356999524,
    "nbf":1357000000,
    "exp":1407019793,
    "jti":"id123456",
    "typ":"https:\/\/example.com\/register",
    "test-type":"foo"
}
</pre>

##### Handling the claims

Sounds pretty simple, right? Well, this is where the fun comes in. See, there's some of the claims in there that you need to pay attention to when processing the JWT. For example, there's an "exipres" timestamp (in "exp") that you should never process it after. There's also a the "not before" that tells your handler when it's okay to start processing the string. You also need to be sure to validate the signature once you have the JSON content.

##### The signature

How is this signature created? Glad you asked....it's one of the keys to a functional JWT implementation, at least one that follows the spec. In our case, we're fortunate enough that PHP comes with some HMAC hashing functionality built in. If you'll notice in the `header` section above, it defines an "alg" value of "HS256". In JWT-speak, this translates into the `SHA-256` hashing algorithm. Creating the signature with this hash is simple with PHP's [hash_hmac](http://php.net/hash_hmac) function:

`
<?php
$signWith = implode('.', array($header, $body))
$signature = hash_hmac('SHA256', $signWith, $key, true);
?>
`

This code takes in the `$header` and `$body` JSON strings (not the objects, the strings), concatenates them with a period (".") and passes them in to the `hash_hmac` function as the data. A key is also passed in to use in encoding the result. The resulting hash is then passed back, base64 encoded and appended to the rest of the string as a signature. This ensures that the message wasn't tampered with. To verify there wasn't any tampering of the token, this signature should be recreated on the decoding side and validated for correctness.

> **NOTE:** While there's a temptation to use the JWT (with possible private/custom claims) as a part of a trust decision, *do not use it* unless the contents were encrypted and protected from plain-text deciphering.

#### Using the library

Handling all of this manually is entirely possible, but why worry about that when there's libraries out there for it. One such library is the [Psecio JWT library](https://github.com/psecio/jwt) posted over on GitHub. This library handles some of the most common functions of creating and decoding JWTs. It has a set of functions you can use to define the claims and `encode`/`decode` methods to create and parse the results.

##### Encoding

First off, we need to get the latest version, so let's install it via [Composer](http://getcomposer.org)
`
{
	"require": {
		"psecio/jwt": "1.*"
	}
}
`

Let's bring back the example JWT content from the start of the article. We'll show how to use the JWT library to create it:

`
{
    "iss":"http://example.org"
    "aud":"http://example.com"
    "iat":"1356999524
    "nbf":"1357000000
    "exp":"1405810922
    "jti":"id123456
    "typ":"https://example.com/register"
}
`

I'm going to fudge a little here since those timestamps are in the past. I'm actually going to replace them with [time](http://php.net/time) calls that will be a bit more correct. You can see there's seven different claims in our set including a "not before", "JWT ID" and "Issuer". The JWT library has methods that let you add all of these to an object easily. First, though, we need to make a header. We'll stick with the SHA-256 hashing for our resulting JWT string, so we make the header:

`
<?php
$hashKey = 'this-is-a-hash-key';
$header = new \Psecio\Jwt\Header($key, 'HS256');
?>
`

The library actually defaults to SHA-256, but this gives you an idea of where you can define the hashing algorithm. This "HS256" string is one of a few defined in the [JWT spec](http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html) for the various allowed hashing types. Good, now we have the header ready...let's make the JWT and add those claims:

`
<?php
$jwt = new \Psecio\Jwt\Jwt($header);

$jwt
    ->issuer('http://example.org')
    ->audience('http://example.com')
    ->issuedAt(time()-60)
    ->notBefore(time())
    ->expireTime(time()+3600)
    ->jwtId('id123456')
    ->type('https://example.com/register');
?>
`

Each of the claim types can be added through a method by the same name and passing in its value. This adds them to the set of claims internal to the JWT object. Then there's just one final (and really easy) step to getting our resulting JWT string: calling `encode`:

`
<?php
echo "JWT String: ".$jwt->encode()."\n";
?>
`

This grabs the hashing type from the header (the one we created and gave it in `$header`), transforms everything into the base64-encoded JSON and generates the signature. The parts are then all appended back together and returned as a string. Simple, right? Well, there's one last thing before we get to decoding - custom claims.

##### Custom Claims

It's possible that sometimes you want to carry along more information than just the normal claims (the "public" claims) can provide. In this case, you'd be wanting to add a custom, or "private", claim. These claims are ones that aren't a part of the specification and are agreed upon by both sides as data that's understood or required. The JWT library makes adding these simple too via a "custom" claim type:

`
<?php
$jwt = new \Psecio\Jwt\Jwt($header);
$jwt->custom('my-custom-claim', 'custom-value');
?>
`

This claim then gets added in to the set and is translated and signed just like any other piece of data.

##### Decoding

Decoding reverts the whole process when given a valid JWT string. The library makes this a single call with an existing JWT object:

`
<?php
$hashKey = 'this-is-a-hash-key';
$header = new \Psecio\Jwt\Header($key, 'HS256');
$jwt = new \Psecio\Jwt\Jwt($header);

echo "decoded result: ".var_export($jwt->decode($inputString), true)."\n";
?>
`

The result of this call is an object (like from `json_decode`) that has values for each of the claims as properties on the object. That's all there is to it.

#### Encryption and JWTs

If you've been paying attention, you've noticed that everything we've been working with up until now has been centered around plain-text information. With enough work and time (or the right tools) someone could grab the JWT information, decode it and maybe even alter the information for their benefit. Obviously this means that any important decisions (like the trust decision i mentioned earlier) shouldn't be made based on the JWT information. Fortunately, the library also provides a way to integrate this encryption into the flow and guard the claim data with another, more secure level of protection.

The library uses the standard OpenSSL functionality to encrypt the claim data with the given algorithm, key and IV. You're forced to give these three pieces of information as a part of the `encrypt` call, so it's highly recommended they've been generated from something pretty strong for better protection. Here's an example of it in use. This is using the same `$jwt` object we made before with all the claims attached:

`
<?php
$encryptKey = 'make-your-key-better-than-this';
$result = $jwt->encrypt('AES-256-CBC', '1234567812345678', $encryptKey);
echo 'ENCRYPTED: '.var_export($result, true)."\n";
?>
`

The result will look pretty similar to the `encoded` version of the string, but the contents will have been encrypted and then signed, providing that extra layer. Decrypting is as easy as decoding - it's just providing the same information to the `decrypt` function:

`
<?php
echo "DECRYPTED: ".var_export($jwt->decrypt($result, 'AES-256-CBC', '1234567812345678', $encryptKey), true)."\n";
?>
`

This will result in the same type of object as was given before based on the claims JSON.

Hopefully I've given you some good background and ideas on what JWTs are and how they can be used to help secure and provide a bit more context around the requests made to your application. The library that I've mentioned here is only one of the offerings out there too. Other people have written JWT handlers in multiple languages. If you're interested in other PHP implementations, I'd check out [the list on Packagist](https://packagist.org/search/?q=jwt)

#### Resources

- [JSON Web Token (JWT) IETF Documentation](http://self-issued.info/docs/draft-ietf-oauth-json-web-token.html)
- [Psecio JWT Implementation](https://github.com/psecio/jwt)

