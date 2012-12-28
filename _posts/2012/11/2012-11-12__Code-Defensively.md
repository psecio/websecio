---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Code Defensively
tags: code,bestpractice,tutorial
summary: Sometimes the best defense is a good offense - "think like an attacker" with these hints to prevent exploits.
---

Code Defensively
--------------

{{ byline }}

As a developer, it's very easy to concentrate on "making it work". You have the objective 
your code is supposed to meet and it's much easier to just follow that path, get things 
working and move along to the next objective. Unfortunately, programming like that is just 
asking for trouble. Users are unpredictable things and they'll always find ways of interacting
with your applications that are outside the "normal operating parameters" of what you'd expect.

So, what's a good developer to do about something like this? Well, if you think of coding the
"right path" as offense, then trying to code your application so it's more robust and can 
take a bit more abuse should be considered "defensive". This kind of development emphasizes 
looking at things from a "what could break" approach and try to prevent those things as much
as possible.

Here's a few tips you can apply to your own development to help you think and code defensively:

#### Expect the Expected

This is the obvious one - it's the "make things work" path where everything is working as it
should and the user's input is just what it's supposed to be. If everything was right with 
the world, this is as far as you'd need to go. Unfortunately, it's not, but there are some things
you can implement to help make the unexpected pop up a bit less.

##### Contracts

One of the best things you can do for your application is to specify "contracts" between the 
parts of your code, ensuring that things are what you expect. For example, if you have a method
that requires that you give it a specific kind of object, you can use PHP's type hinting to ensure 
it's the correct type:

`
<?php
function myAwsomeFunction(\MyObject $obj)
{
    // your code here
}
?>
`

"But I don't use custom types like that everywhere," you say. Well, there's another way to 
check your incoming values. Unfortunately, PHP doesn't have anything built into the language 
to automatically evaluate the incoming variables for their type. You *can* type hint for 
`arrays` but not for `integers` or `strings`. So, we have to take matters into our own hands:

`
<?php
function myNextFunction(array $arr, $myString)
{
    if (!is_string($myString)) {
        throw new \Exception('Datatype "string" expected, given '.gettype($myString));
    }
}

myNextFunction(array('test'), 'test'); // pass
myNextFunction(array('test'), 1234); // fail
?>
`

Throwing an exception like this is an example of the [fail fast](/2012/10/22/Fail-Fast-Securely.html)
method that breaks the execution as soon as something unexpected is it (and deals with the resulting 
output securely). One note on type checking like this - since PHP can juggle types around, be careful what
you're checking for. For example:

`
<?php
$value = '1234';
print_r(is_int($value)); //false
print_r(is_numeric($value)); //true
?>
`

##### Object Structure

One thing to remember when writing defensive code is that sometimes, just checking with type
hinting for the right kind of object isn't enough. You cannot assume that that `User` object
you just got passed in is correct and need to evaluate its properties before trying to use
them. This works similarly to the variable checking above, it's just slightly modified for
checking object properties:

`
<?php
$user = new \stdClass();
$user->properties = 'bad value, we want an array';

// good
if (is_array($user->properties)) {
    foreach ($user->properties as $property) {
        // ...
    }
}

//bad
foreach ($user->properties as $property) {
    // ...
}
?>
`

Fortunately, in a case like this PHP will spit back an error saying that it can't `foreach`
over the `properties` value (as it's a string) but we shouldn't rely on language-level 
errors like that to control the flow of the application.

##### The "Catch All"

One last thing to think about when it comes to contracts in your code - be sure to avoid
the "catch all" thinking and put in something that still executes "just in case". Sometimes
it's unavoidable, but if you do implement something like this, be sure it gets the least
amount of functionality if your code hits it. The best situation is if you can set the 
resulting value to a `null` or `false` instead of an actual value. This also has the fun
side effect of being easier to debug in the long run. It's much easier to pick out an empty
value that one that might have come out of another, unknown part of the system.


#### Expect the Unexpexcted

So far I've talked about setting things up for when they follow the straight and narrow 
execution path in your code. Sure, there's already been a few tips about validating input
and using things like code contracts to try to get things flowing smoothly. All of this 
has still been on the offensive side, though. You're trying to protect you application
from something malicious that comes in from the user (or another external data source).

The next logical step beyond that kind of checking is to write (or refactor) your code
so that it can handle bad data if it comes across it. Remember, just because someone 
gives you a `username` that's a `string` it doesn't mean it's in the character set you're
expecting or that it's safe from any kind of injection attack.


##### The Curse of Bad Data

No matter how much you're expecting to get a certain kind of data coming into a method,
there'll always be someone that figures out something that can get in. So, what do you
do when presented with this kind of data? Well, here's a few recommendations:

- **Validate, not filter:** It might sound a little controversial, but I'm becoming more
  and more of a fan of not trying to guess what the user was trying to give me and just
  flat out deny it if it's not what I'm expecting. Sure, you could try using things like
  [filter_var](http://php.net/filter_var) to extract out what you assume they meant, but 
  assumptions are what cause a lot of security issues in the first place. If it's not what 
  you're expecting, drop it and tell the user to try again, this time with valid data.

- **Convert character sets:** Chances are, unless you're running a site that's internationalized
  and has to work across multiple languages (my heart goes out to you if that's your 
  app), you know what character set is going to fit you and your content the best. Anything 
  the user gives you that's valid should, therefore, be in that character set. So, before
  you do any kind of filtering or validating, you should probably convert it over to
  the correct charset with something like [mb_convert_encoding](http://php.net/mb_convert_encoding).

- **Assume encoded strings:** Just because you use something like [htmlspecialchars](http://php.net/htmlspcialchars)
  to filter out HTML content in your user's input dosen't mean that the data that comes out
  the other side is safe from XSS attacks. In fact, that's a very, very small subset 
  of what would possibly be passed in to try to bypass your filters. So what other kind
  of data could come in? Check out [this impressive list](https://www.owasp.org/index.php/XSS_Filter_Evasion_Cheat_Sheet)
  of possible values from the OWASP project. You'll start to understand why I'm more
  of a fan of whitelisting instead of blacklisting...

#### Paranoid Programming

So, what's a developer to do in our "Can't Trust Users...Ever" world to help protect their
applications? Some of the things have already been mentioned, but I wanted to provide a sort
of checklist you could run through and be sure you're looking at the right topics:

- **Input validation:** Even the most basic validation is better than nothing (PHP's default
  unfortunately), but the more you can do the better. Validation should never be an after
  thought in your code, you should always be thinking about it as you're developing. Your
  code should be filled with checks to ensure what you're given is correct.

  Some possible methods include the use of things like [filter_var](http://php.net/filter_var),
  the "ctype" functions (like [ctype_digit](http://php.net/ctype_digit)) or even a proven 
  validation library like [Respect's Validation class](https://github.com/Respect/Validation).

- **Use unit tests as a secret weapon:** When most developers think about unit tests, they
  think about only testing the success/fail of the execution of their app. They want to be
  sure that things are returning correctly and working as expected in a nice, easy to automate
  sort of way. Fortunately, this same kind of setup can be used to evaluate how your system
  will function if it was given bad data or the wrong kind of data. You can write tests that
  give your methods the wrong data on purpose to be sure they fail correctly.

- **Sanitize from all external sources:** Unless you know that you can trust the data you're
  pulling into your application 100%, validate and filter it to be sure there's nothing
  malicious hiding inside. Even sessions aren't immune to any kind of tampering. Remember,
  by default PHP only [base64_encodes](http://php.net/base64_encode) the data in the session
  and writes it to disk. You can help the situation by either writing the sessions to a 
  database (more overhead, obviously) or encrypting the data inside them.

One thing to remember as you "think paranoid" when it comes to your code - there's a balance
you have to maintain otherwise you'll never finish anything. Sure, it'd be great if you 
could validate everything that comes in and pass it through a magical filter to get rid 
of anything that might cause damage, but that sort of thing doesn't exist, so we have to
handle things a bit more manually. This, unfortunately, means picking your battles and keeping
this saying in mind:

> There's a difference between robust and secure.

Sounds like an easy thing to follow, but you'd be surprised at just how far out of hand 
some validation can get when the developer gets really paranoid about things. You could 
write things so that every single method checks every single piece of data and runs it 
through a whole set of tests before even trying to use it, but that's not the same thing
as an application being robust. "Robustness" is less about checking everything and more
about how you handle things when they go wrong and putting priority on the things that
need to be checked (usually the things "closer to the user").

#### A Reminder

Don't fall into the trap of writing more code to evaluate that your data is correct than 
actual app code - trust me, it's a very easy line to cross. There's a fine line between 
paranoid programming and accruing technical debt. Be sure your code is more in the former 
than the latter.

Hopefully this article has given you some good ideas to start from and think about as
you're working on your next application. Remember to think like an attacker and don't 
make assumptions that the user is going to give you the data you want or need.

##### Resources

- [Defensive Programming: Being Just-Enough Paranoid](http://swreflections.blogspot.com/2012/03/defensive-programming-being-just-enough.html)
- [Wikipedia - Defensive Programming](http://en.wikipedia.org/wiki/Defensive_programming)
- [Why Defensive Programming is Rubbish](http://danielroop.com/blog/2009/10/15/why-defensive-programming-is-rubbish/)
  (for perspective)

