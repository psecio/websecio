---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Fail Fast Securely
summary: One key to keeping an app secure is the when & how of dealing with failure.
---

Fail Fast Securely
--------------

{{ byline }}

If you've been around either the development or security worlds for very long, the 
concept of "fail fast" is pretty familiar to you. I imagine even if you're a relatively
green developer, you've come across this kind of thinking in your code already. Here's 
the basic idea:

> When your software comes across something unexpected, it should break. The level of 
> "breakage" depends entirely upon the error. Fatal errors that could cause serious 
> problems are the most important to catch. For these kinds of issues, we want to 
> fail fast.

#### Throwing Exceptions

As PHP developers, this is an easy one - just throw an [exception](http://php.net/exception)
and you're good...right? There's a big debate in the community about when the right time
to use exceptions is. Some people like to use them every chance they get, using them 
more to control the flow of the application than error handling. Others use them so 
rarely, you never know if one's going to bubble up from some random part of the application.

The real key to their use if finding the right balance because they stop the execution 
(by default) and "break" the application. In my years as a developer, I've placed a lot
of exceptions in my time and here's the general rule I follow: "if the situation was allowed
to continue, would to cause serious damage down the line?" For example, if we have a user
signup form and they're allowed to pick their own username, it'd obviously need to be
whitelisted with certain characters. This is the perfect instance for catching a severe
failure - if the username doesn't match the criteria, tossing an exception can make sure
they don't slip through.

#### The Trick with "Fail Fast"

If you follow the [fail fast](http://en.wikipedia.org/wiki/Fail-fast) line of thinking, 
you want your software to - at the earliest possible moment - break because of the issues
that could come up if it proceeded. There's a few spots where this could happen:

1. Input validation when a *function/method* is called
2. After a *return value* has come back from another method
3. Checks on any other kind of *information coming from outside* the script (like maybe files)

There's more than just this short list, but at least it gives you an idea of some common
places to start. The "fastest" place you can fail is in the checks for #1 in the list. 
If the input the user gives you is incorrect or cannot be translated into something useful,
toss that exception and catch it to fail the script. This ensures that, not only is your
app protected from malicious data but the user is also informed immediately that there's
something wrong.

For example:

`
<?php
public function transformString($string)
{
    if ($string == null) {
        throw new \InvalidArgumentException('String value cannot be null!');
    }
}
?>
`

Checking like this on the values of every parameter can add a bit more code to what you've
already written, but in saves you time in the long run by making it easier to track down 
errors.

If course, since PHP's defaults spit out a lot of extra information with the exception and
error output, you'll definitely want to [create some custom handlers](/2012/08/14/Playing-Your-Cards-Close-Error-Exception-Handling.html) to keep some of the information (like file paths or stack traces) away
from the eyes of would-be attackers.

Throwing a baseline `Exception` is okay for a lot of circumstances, but sometimes you want
to be a bit more descriptive in what went wrong. Thanks to [this set of exceptions in the SPL](http://php.net/manual/en/spl.exceptions.php) there's some built-in help for that. Things like `InvalidArgumentException` 
and `RangeException` are useful, especially when it comes to doing input validation.

#### Failing Badly vs Fault Tolerance

Now, for anyone that's made an kind of applications that strive to be useful, breaking
things at every turn with exceptions can be a bit frustrating. You want to provide your 
site's visitors with the best experience, so you should consider building your app with
a [fault tolerant design](http://en.wikipedia.org/wiki/Fault-tolerant_design).

The idea here is that your application tries its best to take what the user has given it
and do something useful with it. Obviously, if not done correctly, this could lead to 
some design flaws (which could lead to security flaws).

> **Be very careful** when trying to add fault-tolerance into your app that you don't
> make it too forgiving. 99% of what's wrong with the security of web applications
> these days is someone, somewhere making an assumption about how the system will
> be used.

If your application fails at the first whiff of danger and keels over, your customers
won't stick around for very long. This situation is described in another term - 
"[fail badly](http://en.wikipedia.org/wiki/Failing_badly)". This kind of system 
relies on every piece of the puzzle to be in place and working 100% correctly (as fragile
as it may be) to keep from failing. If you have this kind of system, you're basically
asking for trouble.

Here's a few steps you can take on the application side to be sure you're not creating
such a system:

- **Minimize dependencies between parts of the app:** When constructing the application
give some thought to its structure and what parts can be abstracted out. This way, if 
one part of the application fails, it's not going to break everything else in the process.

- **Consider multiple data sources:** One of the tenets of a robust application is its
ability to scale and work with multiple potential data sources. For small applications,
it's not as big of a deal but larger applications must be able to swap these out at a moments
notice.

- **Understand intent, not just data:** Be wary of filtering data blindly, assuming that
you'll end up with what you want. Consider the situation and filter accordingly and failing
as soon as something wrong/threating is detected. It's a fine balance between letting
data break your system and breaking it on purpose when the data is bad.

- **Layers of defense:** When creating the security and functionality of your application,
think in terms of layers. Remember that one level doesn't have to take care of everything
(Ex. don't make a "Security Layer" that does it all, break it up into the objects - like
"Users", "Roles" or "Permissions" and check based on those). [^1]

#### Summary

Failing fast is easy in PHP, but failing fast securely takes careful planning and thought
to ensure that you're doing it correctly. Information exposure is one of the highest risks
an application can face, and error messages are some of the worst about exposing that data.
Be sure you're ready!

[^1]: For more information on this, see the article about [Defense in Depth](/2012/10/12/Core-Concepts-Defense-in-Depth.html)


