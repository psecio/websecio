---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Fun with Input Handling
tags: input,validation,handling
summary: ...
---

Fun with Input Handling
--------------

{{ byline }}

It's no secret that PHP's input handling methods "need improvement". In fact, if you've worked with PHP for any length of time, you've noticed that there's nothing preventing you from directly accessing user input without any kind of validation what so ever. Sure, there's things like [filter_var](http://php.net/filter_var) or [other libraries](/2013/04/01/Effective-Validation-with-Respect.html) that let you do some validation, but these are all optional. It's amazing to see just how many PHP applications don't do any kind of validation on their input, happily oblivious to the potential security issues they've introduced.

Anyway, I'm not here to ramble on about the current state of validation in PHP applications (there's plenty of [other posts](/tagged/validation) that cover that). What I want to share is a handful of things that are a bit more off the beaten path when it comes to validation and filtering of user input. These go outside of the normal "be sure something is the right type" or "filter to remove any harmful strings" kind of recommendations. The following three examples are a bit more tricky and can easily come back to bite you if you're not careful.

#### Eval in preg_replace handling

First up is a fun little feature that's included in PHP's regular expression handling allowed for the `/e` modifier to be included in the regular expression string in a [preg_replace](http://php.net/preg_replace) call. What does it do, you may ask? Put simply, it's a shortcut to [eval](http://php.net/eval) that would take the results of the match and replace and execute it through the current process. Now, this isn't as big of an issue when the matching is done all internally, but when you start introducing user input into the matching, things get a bit more dicey.

Consider this example and what might happen if you included user input into the mix:

`
<?php
$result = preg_replace('/.*/e', "exec('uptime');", 'test');
print_r($result);

/**
 * The output in this case is the output of the "uptime" command like:
 * 9:22  up 17:06, 7 users, load averages: 3.19 3.36 3.98 9:22
 */
?>
`

With that `/e` modifier in place, the result of the regular expression match, in this case the `exec('uptime')`, is executed and the return is put in `$result`. Imagine the havoc that could be caused if unfiltered user input was included in either (or both) sides of the equation: the regular expression itself or the replacement string. In fact, if no filtering is being done, it's even possible that you don't even have the `/e` modifier on your regular expression. A crafty attacker could add it in there via [another bug](https://bugs.php.net/bug.php?id=55856) where PHP wasn't checking for null bytes at the end of a string.

Prior to **PHP version 5.4.7**, the `preg_replace` handling didn't check for null bytes at the end of a string. For those not overly familiar with the PHP's C roots, a string in C is ended with a null byte since it's just a series of characters (there's no "string" type in C). Since no checking was being done by PHP on the string the regular expression handling was being given, it was possible to append a null byte (`\0`) to the string and have PHP drop the rest of the string like it never existed. Here's an example of a valid use case and an exploit based on it:

`
<?php
// First, we show a valid replace
$_GET['search'] = 'b';
$_GET['replace'] = 'baz';

$result = preg_replace('/'.$_GET['search'].'ar/', $_GET['replace'], 'foobarbaz');
echo $result; // this results in "foobazbaz"

// Now, our exploit
$_GET['search'] = ".\/e\0";
$_GET['replace'] = "exec('uptime');";

$result = preg_replace('/content:'.$_GET['search'].'/i', $_GET['replace'], $data);
?>
`

While this example is a bit contrived (it's a *really* bad idea to accept a `replace` value from the URL), you get the idea. When the `search` string is dropped into the `preg_replace` call in the valid replace, you're correctly swapping out "bar" with "baz". In the exploit version, they're matching any character in the input string ("foobarbaz") by matching with the `.` (period), appending the `/e` to the regex and telling PHP to end the string with the `\0` null character.

So, what can be done to mitigate this issue? Well, there's a few things you can do here:

1. The easiest (well, for relative versions of "easy") is to upgrade your PHP version. Honestly, if you're using something older than PHP 5.4.7, released in September 2012, you have other larger security issues too. Do yourself a favor and upgrade (we're in the 5.5.x series now people).
2. Don't ever, *ever* take in this kind of data from the URL if at all possible, especially the "replace" string. If you absolutely have to, run it through something like [escapeshellcmd](http://php.net/escapeshellcmd) to ensure it's not a command.
3. Of course, just because it's not a command, it doesn't mean it's going to be valid for use. It could just be PHP code to execute, allowing the attacker to run whatever they'd like. Filter and validate what's coming in to be sure it's not code too.
4. Finally, and this is more specific to this case, remove any null characters you might find in the string going into the regular expression. A simple [str_replace](http://php.net/str_replace) can take care of this.

#### Log injection

@todo

#### Poor unserialize handling

@todo
