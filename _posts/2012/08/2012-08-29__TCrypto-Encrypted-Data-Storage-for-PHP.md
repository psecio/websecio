---
layout: default
author: Timo
email: timoh6@gmail.com
title: TCrypto: Encrypted data storage for PHP applications
summary: Learn how to use the TCrypto library to protect your data.
---

TCrypto: Encrypted data storage for PHP applications
--------------

{{ byline }}

Introduction
------------

TCrypto is a small key-value data storage library for PHP 5.3+. TCrypto can be used as a "high-level" library to perform secure data encryption. This means you do not have to worry about cryptographic nuances such as initializing vectors or modes of operation. Everything is hidden behind a simple API.

In this article, we will look at how to handle "session like" data stored into a client-side cookie using TCrypto.

TCrypto is not yet officially released, but it is considered to be stable, meaning you can safely use it right away.

Client-side cookie as a data storage backend
--------------------------------------------

You may have heard "if it is sensitive, do not place it into a cookie!". While this is probably a good idea in general, but it is also a bit of generalization. According to current public knowledge on the field, it is not feasible to break properly implemented data encryption, not even if the encrypted data is placed into a cookie (in the hands of a potential attacker).

"Properly implemented" is a broad topic of course, but TCrypto does its best to ensure the end user utilizing TCrypto library can benefit from a secure data handling using strong encryption.

Installation
------------

You can install TCrypto using Composer. Add something like `"tcrypto/tcrypto": "@dev"` to your composer.json.

You can also just simply download TCrypto from GitHub and extract it into your library folder. There are no additional dependencies of other PHP libraries, so you can start using it as is.

**Autoloading**

TCrypto follows the PSR-0 standard, which means you can use any compliant class loader of your choice. If you are not already using a class loader, you can use a bundled one:

`
<?php
require '/path/to/library/TCrypto/Loader.php';
$loader = new TCrypto\Loader();
$loader->register();
// Start using TCrypto...
?>
`

Encryption and authentication keys
----------------------------------

Before we start, we must create encryption and authentication keys. This is done using a small bundled command-line application. Open your command prompt and `$ cd /path/to/TCrypto/bin`. Now you can run `$ php keytool`. Default settings should be fine most of the time, but take a look at `keytool.config.php` (rename `keytool.config.dist.php` to `keytool.config.php` to make sure your custom settings won't get lost when you upgrade TCrypto) file and see if there is anything that needs to be adjusted.

Choose 1 to add new keys (to a default keyfile). If you need to change the default keyfile, choose 3 and enter the location of a new keyfile. If you specify `keyfile_location` in your `keytool.config.php` file, Keytool will automatically default using this keyfile.

Another important Keytool option is 2 (remove inactive keys). Keytool determines inactive keys by comparing the creation time of a key and the key lifetime setting of TCrypto. "Primary keys" are never removed.

To put it simple, assume we have 3 different keys in our keyfile. "Primary key" (the key that was most recently added, say, a minute ago), a key that was added 30 minutes ago, and a key that was added 2 hours ago. Now if you are going to remove inactive keys, Keytool will ask you the TCrypto "max_lifetime" setting. If we are using default settings, this setting will be 3600 seconds (one hour). After confirmation, Keytool will remove one key, the key that was added 2 hours ago. Primary keys won't be removed and the key that was added 30 minutes ago won't be removed either, because it could be still in active use. The key that was added 2 hours ago is not a primary key and it can not be active anymore, so it is safe to remove this key.

It is a good general advice to add new keys, say, once in a six months. And if you have created more than two keys, remember always to first remove inactive keys before adding new keys. Of course you could add new keys once in a month also, or so, it will not cause any drawbacks.

Keytool is not capable of removing primary keys or keys that are still active. This is important to note if your keyfile gets compromised. In such case, after you have recovered from the attack, you should create a totally new keyfile to hold your new keys. You could also manually empty the compromised keyfile and then create new keys to this same empty file.

It is also good to keep in mind that in this article we are concentrating to manage so called "short lived keys" or "session keys". And for example, we do not go into details of managing "legacy keys" etc.

Note, it is important to make sure you place your keyfile to a location where no one else can read it or access it. Obviously you should not place it under your www-root. However, if you can not access any folder outside your www-root, TCrypto uses an .htaccess file in the default Keystore folder to try to make sure this keyfile can not be downloaded using a web browser.

Data store for the stateless environments
-----------------------------------------

Traditional PHP sessions are a bit clumsy in that they store all the session data into a single place (file). If you look at it a bit closer, it doesn't probably really make sense to store shopping cart items to the same file where we have, for example, sort of unrelated (possibly immutable) content like username or user id or email address, or theme choice parameter. It is also a scaling problem as is (without database session store).

With TCrypto, we are going to create two TCrypto instances to hold the data in a relational manner.

Let's start by creating a store for our static content (username etc.). We create a TCrypto instance which does not utilize data encryption at all. This is just to save processing power and bandwidth. We assume all the content we are going to store to this TCrypto instance is not sensitive (it does not matter if the client can read this data by inspecting his own cookies).

`
<?php
// Create a so called "StorageHandler" first. This will store the data into a cookie.
// True is the default first argument for Cookie(), it forces us to use HTTPS connection.
// Second argument, 'tc_static', is a name for the cookie. If you do not supply a name,
// "my_cookie" will be used.
$storage = new TCrypto\StorageHandler\Cookie(true, 'tc_static');

// Create the actual TCrypto instance.
// We will pass our $storage object as a dependency.
// For the sake of example, we will pass all of the arguments.
$tc_static = new TCrypto\Crypto(null, $storage, null, null, array());
?>
`

Let's create a second TCrypto instance, this will hold our "shopping cart items" (for the sake of example).

`
<?php
// Use a different cookie:
$storage = new TCrypto\StorageHandler\Cookie(true, 'tc_cart');

// We will use OpenSSL functions to perform the actual data encryption/decryption.
// If your system does not support OpenSSL functions, you can use MCrypt functions:
// $crypto = new TCrypto\CryptoHandler\McryptAes128Cbc();
$crypto = new TCrypto\CryptoHandler\OpenSslAes128Cbc();
// Inject $storage and $crypto:
$tc_cart = new TCrypto\Crypto(null, $storage, null, $crypto, array());
?>
`

Now we have two TCrypto instances, one for static content and one for shopping cart items. A brief look at how to work with TCrypto instances:

`
<?php
// After logging in the user, let's set some user specific data (to our $tc_static instance):
$tc_static->setValue('username', 'Bill');
$tc_static->setValue('email', 'bill@bill.tld');
$tc_static->setValue('id', 2);
?>
`

Our user (Bill) could add items to his shopping cart, by clicking "Add to cart" button (with ajax for example):

`
<?php
// Let's set some shopping cart specific data (to our $tc_cart instance):
$tc_cart->setValue('cart_items', array('product_id' => 1, 'name' => 'The Beer Machine', 'quantity' => 1, 'unit_price' => 99.90, 'tax' => '23', 'currency' => 'EUR'));

// If Bill wants to erase his cart, we can remove 'cart_items' key:

// Just a quick example, we do not check CSRF tokens etc.
if (isset($_POST['empty_cart']))
{
    $tc_cart->removeValue('cart_items');
}
?>
`

We will save the data to "permanent storage" (into a cookie in our example):

`
<?php
// Save $tc_static:
// We cheat a little here, since we are dealing with "read only" data, we should not need to
// save the unchanged data all over again, but it is easier to make sure this way that our
// "read only" data does not expire. We could have also specified a longer expiration time,
// but in general, it tends to be simpler just to save the data over and over again.
// Always remember to call save() if your data changes (after setValue() and removeValue()).
$tc_static->save();

// Save $tc_cart:
$tc_cart->save();
?>
`

Next time when Bill loads a page, we can fetch the values we saved earlier:

`
<?php
$username = $tc_static->getValue('username');
$email = $tc_static->getValue('email');
$id = $tc_static->getValue('id');
$cart_items = $tc_cart->getValue('cart_items');

// Try to fetch a non-existent value:
$foo = $tc_cart->getValue('foo_does_not_exists'); // null
?>
`

Remember to handle those variable accordingly, for example before outputting to a HTML page etc.

Finally when Bill logs out, we will erase his data (both from cookies and memory):

`
<?php
$tc_static->destroy();
$tc_cart->destroy();
?>
`

**Further configuration**

There are many more aspects of configuring TCrypto, but for the sake of simplicity we do not go into full details. However, I'll show a few more examples:

`
<?php
// We can use another keyfile.
$keymanager = new TCrypto\KeyManager\Filesystem('/path/to/keyfile');

// Same as before.
$storage = new TCrypto\StorageHandler\Cookie(true, 'cookie_name');

// Use MCrypt and AES-256-CBC for demonstration purposes.
// Basically, TCrypto offers AES-128-CBC and AES-256-CBC encryption. Initial
// advice is to use AES-128-CBC, because it is a bit faster.
// MCrypt or OpenSSL can be used to perform the actual low-level data encryption.
// Data encrypted using McryptAesxxxCbc() is compatible with data encrypted using
// OpenSslAesxxxCbc() since they both use PKCS7 padding scheme.
$crypto = new TCrypto\CryptoHandler\McryptAes256Cbc();

// Initialize the default plugin (serialize/unserialize).
// Data is after all just a plain serialized PHP array.
$plugins = new TCrypto\PluginContainer();

// Attach an extra plugin (compress/uncompress).
// NOTE: TCrypto will not run compression plugins if data encryption is being used.

// This is because data compression may leak information about encrypted plain text. 
$plugins->attachPlugin(new TCrypto\Plugin\CompressPlugin());

// Specify some options.
// 'max_lifetime' is the maximum time (in seconds) the data stays active.
// 'entropy_pool' is an array containing extra "entropy sources". These variables
// are used to hash together with encryption/authentication keys. For example, a key
// could be tied to a specific IP ($_SERVER['REMOTE_ADDR']).
$options = array('max_lifetime' => 6400, 'entropy_pool' => array($_SERVER['REMOTE_ADDR']));

// Create a new Crypto instance and inject the dependencies.
$tcrypto = new TCrypto\Crypto($keymanager, $storage, $plugins, $crypto, $options);

// If you do not provide any of the dependencies, e.g. $tcrypto = new TCrypto\Crypto(),
// TCrypto will use default setting. It is important to note that those default settings
// does not provide encryption.

// See README for more.
?>
`

Session considerations
----------------------

TCrypto tries to deal with "session related" issues by setting certain cookie parameters. By default, cookies are marked as `secure`, which indicates the browser to send cookie data only over a secure HTTPS connection. TCrypto does not send cookies to client if there is no HTTPS connection.

`httponly` parameter is also set. This means the cookie is accessible only through the HTTP protocol (browser should not let JavaScript etc. access the cookie contents).

Traditional session fixation ("?PHPSESSID=123...", if `session.use_only_cookies` is not set) is not possible when the data is saved into a client cookie, since no such session identifiers (which refer to a server-side storage unit) are used. However, cookies could be stolen by other means and you should keep this in mind.

Under the hood
--------------

As you can see, it is fairly easy to utilize cryptography in your application. TCrypto silently takes care of feeding proper keys, key rotation, secure IV generation, data expiration, randomness generation, and most importantly data authentication. Which means our client (or an adversary) can not modify the data, no matter if the data is encrypted or not. It is easy to see how important it is to authenticate the data when using plaintext data (no encryption), but it is also as important when the data is encrypted. With "Encrypt-Then-MAC" composition we make sure it is not possible to feed malicious input to our decryption routine.

**The secure channel**

Even though TCrypto can encrypt the actual data, it can not guarantee that the data came back from the intended client. Nor the client can not make sure who is actually going to receive the cookie data when it is sent to the server. This is out of scope of TCrypto, and that is why TCrypto defaults using secure (HTTPS) connection for the cookie traffic.

Using a secure connection, we can relatively safely assume the cookie traffic can not be replayed by any third parties, and our client knows he is going to communicate with the intended server. And of course, any third parties can not read the plaintext traffic (even when you do not use encryption in TCrypto).

If it is not possible to use a secure connection, it is good to keep this in mind. Although, the situation is not much different if you used native PHP sessions instead of TCrypto.

**Overlong input data**

Currently TCrypto accepts all kind of data being input into the MAC check (as long as the data contains at least a certain amount of bytes). Invalid data is rejected if the MAC code does not match.

While HMAC operations are "fast and lightweight", it is still good to keep in mind that an adversary could send overlong data and try to cause DoS to our server. You may want to consider checking data length before inputting the data into TCrypto. And reject the data if it contains too many bytes. In case of cookies, it is probably reasonable to reject the cookie data if it contains more than, say, 4096 bytes.

**Extending TCrypto**

TCrypto is designed to be easy to extend. If you need to store the data using, say, Redis, it is enough just to create a suitable "StorageHandler" for Redis. From TCrypto's point of view, it is good as long as you code against `StorageInterface` interface. See [https://github.com/timoh6/TCrypto/blob/master/library/TCrypto/StorageHandler/StorageInterface.php](https://github.com/timoh6/TCrypto/blob/master/library/TCrypto/StorageHandler/StorageInterface.php) and [https://github.com/timoh6/TCrypto/blob/master/library/TCrypto/StorageHandler/Cookie.php](https://github.com/timoh6/TCrypto/blob/master/library/TCrypto/StorageHandler/Cookie.php)

Cookie replay
-------------

There is one important thing related to using cookies as a data storage backend. Your legitimate client can replay old TCrypto cookies within the expiration time range. This is something you need to carefully acknowledge before utilizing cookie based data storage.

Imagine a situation where our client was playing an online game and you store information about his credits into a TCrypto cookie. If the client loses, you decrease his credits (and save the current amount into a TCrypto cookie). It is not a big deal for our client to reject this new cookie and use an old cookie instead, which contains greater amount of credits.

It is not possible for a (default) TCrypto instance to know if the cookie received was replayed or not. And as the previous example shows, it is not feasible to correct this using shorter expiration times (`max_lifetime` setting in TCrypto).

Pros
----

There are quite a few benefits when you securely store your "session data" into a browser cookie. It is share nothing, scaling is no problem. You do not have to store session data in your servers (no need to perform session "garbage collection" etc.). There are no locks when session data is read (parallel requests are no problem since the request will not stall).

Cons
----

Besides cookie replay, it is worth to note that TCrypto cookies are a bit greater in size. It is because of the additional payload (key identifier, MAC value, timestamps, possible IV etc.). Encrypting the data also increases the size a bit.

You can not store more than a certain amount of data into TCrypto cookies. Naturally it takes more bandwidth. If your keyfile leaks, it could be a game over (however, if your keyfile leaks, it is quite possible that the adversary could also read your regular session files).

Future work
-----------

Currently TCrypto uses SHA-512 hashing, HMAC-SHA-256 MAC and AES encryption. These details are good, but as technology evolves, it probably makes sense to utilize the new technology in TCrypto at some point (upcoming SHA-3 standard). However this time is not just yet. An exception is if any of those cryptographic building blocks turns out to contain consequential weaknesses.

But for now, it seems simpler just to "hard code" using those cryptographic primitives (instead of including some sort of "protocol identifier" to identify which crypto primitives were used).

Links
-----

[TCrypto on Github](https://github.com/timoh6/TCrypto)
[timoh on Github](http://timoh6.github.com/)

