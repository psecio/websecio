---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Input Filtering & Validation with Aura.Filter
tags: validation,library,filter,aura,framework
summary: The Aura.Filter component provides effective and easy to use data filtering & validation features.
---

Input Filtering & Validation with Aura.Filter
--------------

{{ byline }}

> **Please note:** This article and the code it references are **out of date**. The `Aura.Filter` project has changed dramatically and is now in the 2.0 world.

It's pretty obvious that one of the major security issues for web applications - in any language - is the effective filtering and validation of the data it's using from external sources. This could be coming from any number of places including database, outside APIs or, the worst of them all, your own users. Bad data could be just about anything. It can come in the form of badly formatted text someone copy and pasted all the way out to something malicious from a would-be attacker. Regardless of where it comes from or the intent, all data filtering and validation should be handled in roughly the same way. As I've mentioned [in other posts](/2013/04/01/Effective-Validation-with-Respect.html), filtering should be based on whitelisting, not a blacklist - and ensuring that the data you're using is what's expected and as "clean" as possible.

There's several PHP libraries out there that can help you solve this particular issue. The one I want to cover here is a library that's a part of a framework that's relatively new to the scene, the [Aura Framework](http://auraphp.com). This project, originally started by [Paul Jones](http://twitter.com/pmjones), has one main tenant:

> The primary goal of Aura is to provide high-quality, well-tested, standards-compliant, decoupled libraries that can be used in any code base. This means you can use as much or as little of the project as you like.

Other frameworks out there have adopted the component/modular mentality into their structure, but the Aura framework was built from the ground up this way. It aims to have reusable components that have the least amount of dependencies possible and can be used independently without having to do too much work. Since we're talking about data validation and filtering, we're going to focus in on one particular package - the [Aura.Filter](http://auraphp.com/packages/Aura.Filter). This package provides both filtering and validation (despite the name) and makes it simpler to check the data.

#### Getting it installed

Before we get too far into examples and some sample code, lets get it all installed. The Aura framework packages are a bit more on the bleeding edge of PHP development right now and **require at least PHP 5.4.x** because of some of the functionality it uses. We'll use the [Composer](http://getcomposer.org) method, but you can always download the latest release [from Github](https://github.com/auraphp/Aura.Filter):

`
{
    "require": {
        "aura/filter": "1.0.0"
    }
}
`

Then just run `composer.phar install` and you should be good to go. There's not any kind of configuration files you'll need to make or files to change - you can just get started using it.

> **Note:** the version of the library may have changed since the posting of this article, so be sure you up that release number in the `composer.json` file to the latest and greatest.

#### First steps for validation

Now, on to some simple usage of the component so you can get an idea of the flow. We're going to start with some simple string validation:

`
<?php
require_once 'vendor/autoload.php';

$filter = require_once 'vendor/aura/filter/scripts/instance.php';

$filter->addSoftRule('testing', $filter::IS, 'strlenMin', 3);
$filter->addHardRule('testing', $filter::IS, 'alnum');

$object = (object)array(
    'testing' => '#h'
);

if ($filter->values($object) !== true) {
    print_r($filter->getMessages());
} else {
    echo 'valid';
}

?>
`

In this example, we're setting up a `filter` object (using the handy `instance.php` script that comes with the package) and assigning some validation rules to it for the "testing" property of the object. These two rules check to ensure that the value is alphanumeric and that it's at least 3 characters long. Our string "#h" fails both of these checks. When the `values()` method is called on the object, the filter rules are applied and a `true` or `false` is returned for the overall status. If a `false` is returned because one or more of the checks failed, the resulting error messages can be fetched through the `getMessages()` method.

Two things to explain about this example really quickly. The first is the concept of the *rule types*. In our example, two "soft" rules have been defined. The "soft" rules are the most permissive and don't stop the processing of the rest of the filters. They still make the validation fail, but they don't break the flow. On the other end of the spectrum, there's the "stop" rules. These rules do exactly what they sound like - they stop the execution of the filters on failure and do no more filtering or validation. This is the most restrictive of the rule types. Right in the middle, though, is a third rule type - the "hard" rule. This rule will throw an issue like the others but only stops the flow for other filters/validation on that same field. That's why in our example above, we'd only get one error message, the one for the "alnum" check, as it's a "hard" filter.

The second is the constants that define what kind of rule check applies to the validation. For both of our examples, we've used the `RuleCollection::IS` check that's essentially an "equals" kind of match. For validation, there's two others that compliment it - `RuleCollection::IS_NOT` and `RuleCollection::IS_BLANK_OR`. For sanitization, there's two types - `RuleCollection::FIX` and `RuleCollection::FIX_BLANK_OR` that converts blank values into nulls. (Check out the docs to see the definition of "blank" or "empty" values.)

#### First steps for filtering

Speaking of filtering, let's give a similar example to the one above, but filter the content instead of validating it:

`
<?php
require_once 'vendor/autoload.php';

$filter = require_once 'vendor/aura/filter/scripts/instance.php';
$filter->addSoftRule('testing', $filter::FIX, 'alnum');

$object = (object)array(
    'testing' => 'this 1234 is %$#@ a test'
);

$filter->values($object);
echo 'new value: '.$object->testing; // has become "this1234isatest"

?>
`

We've used the `RuleCollection::FIX` type here to correct the string to only contain alpha-numeric characters. Our input string has some fun special characters and spaces it in so the filtering process strips those out, leaving only the letters and our numeric string. When the filtering runs, it updates the property directly on the object by reference. The `true`/`false` return of the `values()` method remains the same.

There's lots of different types of rules you can use for your filtering and validation including:

- between (numeric)
- creditCard
- equalToField/equalToValue
- inKeys/inValues
- ipv4
- regex
- url
- word
- regex

There's also a special kind of type that you can use if you need to do more complex validation than just the ones provided with the package using [closures](http://php.net/closures). Here's an example:

`
<?php
require_once 'vendor/autoload.php';

$filter = require_once 'vendor/aura/filter/scripts/instance.php';

// Hard-coding a return of false to make the rule fail
$filter->addSoftRule('testing', $filter::IS, 'closure', function() {

    echo 'The data is '.$this->getValue();
    $this->message_map['failure_is'] = "There was an error - d'oh!";
    return false;
});

$object = (object)array(
    'testing' => 'this 1234 is %$#@ is test'
);

if ($filter->values($object) !== true) {
    echo 'messages: '.print_r($filter->getMessages(), true);
} else {
    echo 'valid!';
}
?>
`

Much like the first example, we set up a soft rule that does a `RuleCollection::IS` check (essentially a `true`/`false`) with the "closure" type. The last parameter is the closure itself. The closure is bound to an instance of a `Rule` class and the data isn't passed in during execution. Instead you need to use the `getValue` method to grab the value of the property. In our case the return value is hard-coded as `false` to make the check fail and return the message as an error. The call inside the closure to `$this->message_map` sets the customized error message for the rule. This way you can set messages that have a bit more meaning to the actual problem (as more complex checks usually mean more than one possible kind of error).

#### Filtering versus Validating

While validating the data coming in is a relatively easy task (doesn't match what we want? kick it back!) filtering is a bit more tricky prospect. Filtering the data is basically akin to guessing, which is really never a good idea when it comes to the security of your application. For the most assurance, you'd want to combine the functionality something like Aura.Filter offers to do both at once - filter *and* validate.

For example, say we asked a user to give us a phone number, minus any dashes or other non-numeric characters (we're assuming a typical U.S. number with area code here for simplicity). In order to be sure we weren't given any extra data, we'd want to filter first then validate:

`
<?php
require_once 'vendor/autoload.php';

$filter = require_once 'vendor/aura/filter/scripts/instance.php';
$filter->addSoftRule('phone', $filter::FIX, 'int');
$filter->addHardRule('phone', $filter::IS, 'strlen', 10);

$user = (object)array(
    'phone' => $_POST['phone']
);

if ($filter->values($user) !== true) {
    echo 'messages: '.print_r($filter->getMessages(), true);
} else {
    echo 'valid user!';
}

?>
`

We combine the two methods - the `RuleCollection::FIX` and `RuleCllection::IS` on the string length to ensure that what we've been given is a ten digit number with no extra characters. Obviously if we were given bogus data or not enough of it, the validation would fail and we'd get an error message back. This is where the real power of a combined tool like Aura.Filter really starts to shine. You're not having to use two different libraries or one to validate and maybe something manual to filter. It's more of a "one stop" kind of solution.

#### A word of warning

One thing to remember in all of this - having a filtering and validation library at your disposal is a definite must for PHP-based applications as there's nothing built-in that handles it. This comes with a caveat, though. Remember that you should never let a package like this lull you into a false sense of safety. After all, software is written by humans and regardless of how many unit tests are in the suite, there's always assumptions made and edge cases that aren't caught.

More validation is always better than less and checks should **always** be made on any input. A good general rule of thumb is to think about the [trust](/2013/08/27/Core-Concepts-Trust-Boundaries.html) of the resource and the data it'll be feeding you. For example, if the data is coming from your highly secured database not available to the outside world, the chances of compromised information is lower, so the data can be awarded a bit higher level of trust. Data coming in from something like an external API or even your own users should *never* be left unfiltered and unvalidated. Malicious or not, this kind of data source can provide some really bad data and, since it's not under your control, the best thing to do is handle it as tainted.

#### Resources

- [Aura.Filter on Github](https://github.com/auraphp/Aura.Filter)
- [Effective Validation with Respect](/2013/04/01/Effective-Validation-with-Respect.html)
- [Aura Framework](http://auraphp.com)
- [Stack Overflow on Filtering in PHP](http://stackoverflow.com/questions/129677/whats-the-best-method-for-sanitizing-user-input-with-php)
- [PHP manual for filter_var](http://php.net/filter_var)
