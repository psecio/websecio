---
layout: default
author: Jeremy Cook
email: jeremycook0@gmail.com
title: Dirty Data: Protecting Your App from Your Users
summary: All user data is tainted, but how to you effectively deal with it? Read on...
---

Dirty Data: Protecting Your App from Your Users
--------------

{{ byline }}

As programmers one of the most important things we can do is to cultivate a security conscious mindset
when writing code, yet this is one of the areas that seems to be discussed least. While there are many
resources dealing with individual ideas or problems related to security there seems to be little for
developers aiming to become more security conscious. This article will attempt, in some small way,
to redress this balance. I’ll present what are a number of concepts that are important when trying
to write security conscious code along with an explanation of them. This is not meant to be a fully
comprehensive list and merely represents ideas that I’ve found to be useful over the years. If anyone
has comments on this article I’d very much welcome the chance to discuss them. I’m highly indebted
in my thinking on web application security to Chris Shiflett’s book ‘Essential PHP Security’, and if you
haven’t read it I highly recommend it.

So, what are some of the key concepts to keep in mind when trying to be a security conscious
developer?

#### All Input is Tainted Until Proven Otherwise

This is perhaps one of the most important concepts to absorb since the majority of attacks against
applications boil down to developers placing too much trust in input. Simply put you can never trust any
input that arrives at your application until you have proven that it is safe. It doesn’t matter if the input
comes from an anonymous user or a (supposedly) authenticated and trusted source: all input is tainted
until proven otherwise. If you take nothing else from this article please take this idea as adopting this
way of thinking can help to avoid countless problems.

This then raises the question of what is input? For me the best definition is that it is anything that comes
into your application from a source that you do not directly control. While this always encompasses
input from users it also means that different things have to be considered as input in different
applications. For example, data from a third party web service or a database that you do not directly
control should be considered as input and treated accordingly.

#### Filter Input, Escape Output

If you subscribe to the idea that all input is tainted what can you do to handle it safely? The answer is to
filter input and to escape output. In fact this idea is so important that it should become a sort of mantra
for all developers.

Filtering input is the art of validating it to make sure that it conforms to patterns, formats and types
that your application is expecting. If you believe that all input is potentially tainted then all input has
to be filtered with no exceptions. The specifics of the filtering are a domain specific problem but could
involve things like checking the format, length or type of an input string or to see if an input field does or
does not exist in a database table. Filtering input can also be much more than simple format checking.
For example, while filtering an email address, URL or telephone number can tell you if the input is
syntactically correct it can’t tell you if you actually have a valid piece of data. To find this out your code
has to perform extra steps such as sending a confirmation email, performing a `GET` request on a URL or
checking a phone number in some way. All of this is part of filtering input and the amount of work you
will need to undertake on this depends on how important the input presented is to your application.

Escaping output involves taking input and replacing characters that have special meanings in it for the
output format that you intend to use with safe equivalents. This will vary depending on the output
source your application is producing but it’s important to understand what constitutes output. I think
that the best definition is that it is anything which your application produces and sends to another
location. Obvious common forms of output for web applications include HTML, XML and SQL queries but
there are many others. By simply appropriately escaping output you will stop injection attacks, such as
SQL injection or XSS, against your application in their tracks.

#### Practice Defence in Depth

Filtering input and escaping output is an example of defense in depth. Either on their own should
provide a good level of protection for your application but used together they can reinforce each other
with any failures in one will hopefully being mitigated by the other. This is an important idea because
at the end of the day we all make mistakes. If you can add multiple layers of defense to your code
problems, oversights or shortfalls in one will hopefully be compensated for by another.

#### You Can Never Know Where Input is Coming From...

The form that you spent hours lovingly crafting may be the way that the majority of input arrives at
your application but it doesn’t have to be the only way. Anyone who knows what they’re doing can
script HTTP requests in code or at the command line using tools such as cURL, and anyone trying to
hack your application will almost certainly be doing this. While you can make this process more difficult
through using things such as tokens in hidden form fields you can never really know where input to your
application is coming from. If you’re filtering application input however this shouldn’t be a concern to
you. It doesn’t matter what the source of input to your application is as long as you make it play by your
rules when it gets there. You can’t control where your input comes from but you can control how it is
treated by your app.

#### Always Know Where Your Input is Coming From

How can I say this given the last point? Let me explain. While you can’t control the source of input
to your application you should always know the method by which it arrived, and by method here I
mean HTTP. To understand this point we need to look a little more closely at the most common HTTP
methods.

Out of the five methods of `GET`, `HEAD`, `POST`, `PUT` and `DELETE` only `GET` and `HEAD` are considered
as ‘safe’. `GET` and `HEAD` requests should only be used to retrieve resources and should not take any
action other than this, meaning that they can be repeated without any ill effects. How does this relate
to security? I’ll use an example in PHP for this. PHP provides a number of ‘superglobals’ such as `$_GET`,
`$_POST` and `$_COOKIE` that contain data submitted through HTTP. It also provides the `$_REQUEST`
superglobal that contains data from `$_GET`, `$_POST` and `$_COOKIE` combined into a single array. If
you use `$_REQUEST` you can never know by which method data has arrived at your application and
this opens a potential security hole in that POST requests can be replayed by using a malicious HTML
link. For example if you use POST method forms on your site but retrieve the data submitted through
`$_REQUEST` there is nothing to stop an attacker replaying a submission over and over again using an

HTML link with data being submitted in a `GET` query string. Since you’re using `$_REQUEST` you have
no way of knowing that this data arrived via `GET`. `POST` is definitely not a ‘safe’ method and anyone
clicking on this link would be carrying out an action other than just resource retrieval on your app with
potentially damaging consequences. Of course an attacker would have to know that your app is written
in PHP and using `$_REQUEST` but this can be pretty simple to discover. The only way to combat this kind
of attack is to always know by which HTTP method data has arrived at your app. If you’re writing in PHP
never use the `$_REQUEST` superglobal.

#### Know Your Tools

Part of writing security minded code is knowing what your language or framework offers you when
filtering or escaping input. I’m sure many of us have seen code which needlessly re-implements
functionality that is offered natively, but why is this dangerous? The chances are far higher that an
application developer will make a mistake in filtering or escaping code than the development team
behind a major language or framework will. There’s also the issue of patching or updating. When
security vulnerabilities are discovered a relatively simple upgrade will protect all of your code. Contrast
that to updating ‘home-grown’ filtering code: even if you discover a vulnerability you have a much more
difficult task in patching and deploying a fix, potentially to multiple locations.

While your language or framework will do a lot for you it’s equally important to understand the limits
of what is available to you. No language or framework can possibly filter or escape every piece of input
that you may come across in a way that’s specific enough for your domain problem. What’s important is
that you learn to recognise when a piece of input can’t be handled by native functionality, meaning you
need to ‘roll your own’ validation. Even in these cases you may find that your tools of choice can take
you part of the way towards achieving your ends.

#### Conclusion

If you’ve reached this point I hope you’ve found some useful ideas in the article. At the end of the day
we all want to sleep soundly at night knowing that we’ve done as much as humanly possible to protect
our applications. I hope that the principles outlined here will help to set you on the path towards a
security conscious mindset if you’re not already on it. As a final thought I’d like to say that security in
an application is one of things that can never really be completed. There’s always the possibility that a
mistake or a new attack vector will open a chink in your armour, allowing an attacker to break in. Always
try to keep an eye on what’s going on in an application and wherever possible have another developer
review your code for problems. Happy coding!

#### Resources
- [PHP manual for Superglobals](http://php.net/manual/en/language.variables.superglobals.php)
- [HTTP Protocol (Wikipedia)](http://en.wikipedia.org/wiki/HTTP)
- [filter_var function in PHP](http://php.net/manual/en/function.filter-var.php)

