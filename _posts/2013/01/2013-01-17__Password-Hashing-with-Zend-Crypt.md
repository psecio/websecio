---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Password Hashing with Zend\Crypt
tags: encrption,bcrypt,password,hash
summary: The Zend\Crypt component of the Zend Framework makes bcrypting your passwords simple.
---

Password Hashing with Zend\Crypt
--------------

{{ byline }}

One of the recent trends in web application development security has been the boom of 
recommendations about using `bcrypt` (along with salts) to protect the accounts of your
users. There's more and more tools showing up that make use of it to automatically 
generate the hashes from a users password. In this article I'm going to look at a 
part of the [Zend Framework v2](http://framework.zend.com/) - `Zend\Crypt` - that provides 
an easy interface to `bcrypt` functionality you can quickly get set up and working in your 
application. For those that might be familiar with the Zend Framework in its previous 
forms, you might be wondering if you have to install the entire framework just to get 
this small part of it. Thankfully, the project has fully embraced the Composer 
installation tool and have made the various parts of the framework available independently.

We'll get to that in just a bit, though - right now I want to talk a bit about `bcrypt`...

#### Why Bcrypt?

During the natural progress of enhancing security in web applications, there've been a lot 
of cryptographic algorithms that have come and gone. There's been several in just recent years
that have been rendered "unsafe" for effective usage because techniques have been found to 
break them and reverse engineer the value the hash came from. One example is the infamous
[rainbow table](http://en.wikipedia.org/wiki/Rainbow_table) that has helped to make using 
`md5` hashing almost a thing of the past.

Bcrypt is a relatively recent addition to the cryptographic family, coming around just
about the time the world was freaking out about Y2K. It has some unique features that 
help to make it stronger than some of its cousins:

- It incorporates a salt of its own to help aid in greater complexity of the end result
- It allows a user-defined "cost" they can use to generate stronger hashes

In general, the higher the "cost" the stronger the resulting hash turns out. It's really
a count of iterations used to generate the hash. Most tools, including `Zend/Crypt`, allow
for this to be specified during the generation.

Because of this "cost", `bcrypt` hashing tends to take a bit longer than some of the other
hashing techniques. It's based on the Blowfish cipher and has a positive side effect of 
being slower to compute - it makes it much more time consuming to try to brute-force
the hash generated with it.

#### Enough theory, let's get to work

So, I'm sure you're anxious to see just how easy it is to implement the `Zend\Crypt`
functionality into your application and get started enhancing the security of your apps. 
Well, to start, we need to get it installed. We're going to use the [Composer](http://getcomposer.org)
method here, just to keep things simple. In your `composer.json` file, you'll need the following:

`
{
    "repositories": [
        {
            "type":"composer",
            "url":"https://packages.zendframework.com/"
        }
    ],
    "require": {
       "zendframework/zend-crypt":"2.0.*",
    }
}
`

If you've used Composer in the past, you'll notice something a little different about this
configuration. The Zend Framework project has set up their own package server to share out 
their own components. Since it's not related to the main Composer repository (on Packagist.org), 
you have to tell Composer where to find it. The `repositoriess` section above points to the 
project's "packages" location and sets a type of "composer". Then, when the `composer.phar install`
command is run, it can correctly pull in the `Zend\Crypt` library automatically. 

There are  few dependencies that come with it, but they're pretty minimal - `Zend\Math`, 
`Zend\ServiceManager` and `Zend\Stdlib`. They're used by different parts of `Zend\Crypt`
and don't bring much overhead with them at all.

Now, for the good stuff - let's see how to make a simple `bcrypt` hash from a given string:

`
<?php
require_once 'vendor/autoload.php';

use Zend\Crypt\Password\Bcrypt;

$bcrypt = new Bcrypt();
$password = $bcrypt->create($input);
?>
`

The resulting hash is stored in `$password`. By default, `Zend\Crypt` uses a "cost" value
of "14" iterations. As a part of the bcrypt functionality, in the `Zend\Crypt`, it will 
automatically generate a salt for you as a part of the hashing process. I've seen 
implementations of user management systems that store this salt along with the hashed 
password result. The component provides a method for you to use if you'd like to extract it:

`
<?php
$bcrypt = new Zend\Crypt\Password\Bcrypt();
$password = $bcrypt->create($input);
$salt = $bcrypt->getSalt();
?>
`

Similarly, you can use the `setSalt` method on the object if you'd like to always use the
same salt value each time (or one you've generated yourself).

Remember how I mentioned that you could manually increase the complexity of the resulting 
hash by changing the "cost" of the generation? Well, there's a method in `Zend\Crypt` 
for that too: `setCost`:

`
<?php
$bcrypt = new Zend\Crypt\Password\Bcrypt();
$bcrypt->setCost(20);
$password = $bcrypt->create($input);
?>
`

There's a requirement that the "cost" value has to be between 4 and 31. Obviously, you
can use a higher cost value to make things a bit stronger, but it comes with a big price. 
When you start getting too much higher than their default 14, things start to slow down
pretty quickly. Bcrypting is a powerful method of hashing, but no one out there is going
to say its the fastest.

#### Verifying the hash

There's also a handy method included in the class that makes it simple to take the 
password a user gives you and compare it against the hash you provide for validity:

`
<?php
$bcrypt = new Zend\Crypt\Password\Bcrypt();
if ($bcrypt->verify($password, $hash)) {
    echo "And there was much rejoicing";
} else {
    echo "No shrubbery for you";
}
?>
`

#### PHP Compatibility

One thing to note, `Zend\Crypt` checks the PHP version that its being run with at the 
time of execution. There was [an issue](http://us3.php.net/security/crypt_blowfish.php)
that was discovered with how PHP implemented the Blowfish cipher into its processing. As
of PHP 5.3.7, though, they corrected things. Unfortunately, this also changed the format
of the resulting hash - previous versions started with "$2a$" and 5.3.7 and following use
"$2y2$" (called the "prefix"). The `Zend\Crypt` library looks for this and uses the 
appropriate one for the PHP version you're currently using.

This, along with the salt, user input and the cost, are passed into PHP's [crypt](http://php.net/crypt)
function for evaluation and processing.

#### That's it?

Yep, that's all you'll need to do to get started hashing your passwords with bcrypt. Of
course, you don't necessarily need a library (or three) to get this kind of functionality into
your app, but it's a nice, easy drop-in solution that can get you going quickly. Plus, you
have the added benefit of it being a part of a framework with some great backing, both
corporate and community.

#### Resources

- [Zend\Crypt Documentation](http://framework.zend.com/apidoc/2.0/namespaces/Zend.Crypt.html)
- [Cryptography made easy with Zend Framework](http://www.zimuel.it/en/english-cryptography-made-easy-with-zend-framework/)
