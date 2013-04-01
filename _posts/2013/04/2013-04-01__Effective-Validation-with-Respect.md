---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Effective Validation with Respect
tags: validation,library,respect,filter
summary: The Respect Validation library helps protect @todo
---

Effective Validation with Respect
--------------

{{ byline }}

The [Respect Validation](https://github.com/Respect/Validation) library has become one of the de-factor standards in the PHP world for doing data validation. Using things like PHP's own [filter_var](http://php.net/filter_var) and various string methods (or *shudder* regular expressions) to check data is useful but can start getting more of a hassle as the validation needs get larger. There's common things that every codebase needs to do to check incoming data and the Respect library handles a lot of these needs out of the box. It includes tons of validation methods including things like:

- type checking (array, string, numeric, etc)
- comparing values
- validating numeric values
- checking the contents of strings, arrays and objects
- validating date and time
- file checking

In all, there's over 100 different validation methods to pick from, so chances are you'll find something in there that will fit your needs.

#### Using the library

As far as basic usage goes, it's pretty simple to use if you're familiar with installation via [Composer](http://getcomposer.org). Just drop this into your `composer.json` file:

`
{
    "require": {
        "Respect/Validation": "dev-develop"
    }
}
`

Run your `composer.phar update` (or `install` if it's a new run) and you'll get the files you'll need dropped into your `vendors/` directory. Now lets put it to use to create some basic filtering. One thing that the Validation library lets you do is chain together different validators. This is a lot easier than having to call an `is_*` or `filter_var` checks one at a time and can make for cleaner, easier to maintain code in the long run. Here's a few examples of validating data and what they'd return:

`
<?php
require_once 'vendor/autoload.php';

use Respect\Validation\Validator as v;

// Checking that a value is numeric
v::numeric()->validate(1234); // true

// Checking for alpha-numeric, no whitespace and that it's length is 1 - 15
v::alnum()->noWhitespace()->length(1, 15)->validate('1234abcd'); // true

// Checking for an all lowercase string that contains "test" and has a length between 1-10
v::lowercase()->contains('test')->length(1, 10)->validate('bad String'); //false

// Checking to see if the value is not an integer
v::not(v::int())->validate(10); // false
?>
`

In the above examples, we define the validation methods to run against the data then call the `validate` method on the object to get the pass/fail status of the checks. If any of the checks in the chain fail (not just the last one) you'll get a `false` back from the call. The first example shows just a single validator call, but the others show how to chain them together to make for more complex validation. The last example shows how to negate a check, making it easier to wrap only certain parts of the chain as needed.

This is all well and good, but what happens if you want to define your validation rules in one place and just want to reuse them? The Validation authors thought of that and include "named validator" functionality inside the tool. Here's an example using a modified example one of our same tests above:

`
<?php
$usernameValidator = v::alnum()->charset('ISO-8859-1')->noWhitespace()->length(1, 15);

// now we can reuse this validator in multple places:
$usernames = array('testuser1', 'testuser2', 'thisusernameiswaytoolongandwillfail');
foreach ($usernames as $username) {
    if ($usernameValidator->validate($username) === false) {
        echo 'Validation of username '.$username.' failed!';
    }
}
?>
`

This is a trivial example, but you can see how defining validation rules in one place (say, maybe in a set in a `User` object or something similar) can be handy.

#### Complex validation with callbacks

The Validation library has two pieces of functionality that make it easier to extend its own functionality - one that lets you use any PHP method for validation and another that gives you the ability to define your own validation methods as a part of the chain.

First, let's look at the PHP function callback method with an sample from their own [examples](https://github.com/Respect/Validation):

`
<?php
// Uses the PHP "is_int" method to validate the value
v::callback('is_int')->validate(10); // true

?>
`

This example takes the data and makes a call to the `is_int` method to check the value. This particular kind of check can be done internal to the tool with the `v::int` check, but this is a simple example of an external call. This also works with objects:

`
<?php
class Test
{
    public function customValidate($data)
    {
        return ($data === 'test') ? true : false;
    }
}

$result = v::callback(array('Test', 'cusotmValidate'))->validate('foo');
var_export($result); // false

?>
`

This allows you to define more customized, complex rules that need to do more in-depth things like checking database records or make calls to APIs for their validation.

The second kind of callback validation lets you define a custom validator that can be used in the normal chain with functions or methods that return more than just a `true` or `false`. We'll use another example from their own documentation to illustrate this. In the following code, you'll see a call to the [parse_url](http://php.net/parse_url) function that returns an array of data. There's checks included for each value in that return:

`
<?php

$url = 'http://websec.io/tagged/twofactor';

v::call(
    'parse_url',
    v::arr()->key('scheme', v::startsWith('http'))
        ->key('host',   v::domain())
        ->key('path',   v::string())
        ->key('query',  v::notEmpty());
)->validate($url);

/// The example returns "false" as there is no query string on the URL

?>
`

#### Other features

There's lots of other features that are included with Respect's [Validation](https://github.com/Respect/Validation) library that can make validating your user data easier. It can check properties on objects, allows for custom error message output, making named validators and can even call validators directly from Zend Framework or Symfony code if they're in the same project.

It's an easy, drop-in and extensible way to give you a bit more peace of mind about the data entering your system.

#### Resources

- [Respect Validation documentation](http://documentup.com/Respect/Validation/)
- PHP manual for [filter_var](http://php.net/filter_var)
- [Valitron](https://github.com/vlucas/valitron), another validation library
- the "PHP variable" library with validation - [Pv](http://github.com/enygma/pv)


