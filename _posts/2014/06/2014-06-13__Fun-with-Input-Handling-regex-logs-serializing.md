---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Fun with Input Handling
tags: input,validation,handling
summary: ...
---

Fun with Input Handling: Regex, Logs & Serializing
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

One crucial part of any application security program is good logging. Without logging you have no way to track what's going on in your application. Good logs can also be used as evidence if you ever have to prove that a user performed a certain action or the whens/where/hows of what was done. Most of the software out there, especially the ones that work with PHP, use the same method for storing their logs: a file-based setup. Remember this point, we'll come back to it a bit later.

When you think about logging, there's a few common things that most systems will track. This includes common data like:

- timestamps
- process information (name, PID, etc)
- a level (warning, info, fatal)
- a message
- additional data

That last point is where things start getting a little tricky. This "additional data" usually includes some of the input coming directly from the user. It's easy, especially if you're rolling your own logging system, to forget that there's such a thing as tainted input when it comes to writing to log files. Let's look at an example of a (very) simple logging system and how a little hand-crafted user input could be used to abuse it.

`
<?php
function log($file, $message, $addl = array(), $level = 'INFO')
{
	if (is_file($file)) {
		$data = '['.date('Y-m-d H:i:s').']'
			.' ['.strtoupper($level).']'
			.$message.' ['.json_encode($addl).']'

		return file_put_contents($file, $data."\n", FILE_APPEND);
	}
	return false;
}

log('log.txt', 'this is my message', array('username' => 'testuser1'));
?>
`

In this super-simple logging function, we're taking in a `file` to write to, the `message` to include, any additional information and a logging level. The structure of this output will seem familiar to those [Monolog](https://github.com/Seldaek/monolog) logging library. It formats the string, puts it into the `$data` variable and uses the [file_put_contents](http://php.net/file_put_contents) function to append the result to the given file. The return value is `true` if the write was successful, otherwise it's `false`.

It's pretty easy to assume, especially on smaller applications, that the log information is being written out to a file somewhere. When thinking about log files, we have to think about the context and what kinds of characters normally end up there. It's pretty common for the lines in a log file to end with a newline (`\n` or `\r`...or both) so there's lots of software out there that expects that. There's even [functions in PHP](http://php.net/file) that look for line breaks to know where to break things up into an array. Unfortunately, without good filtering on the user input, these kinds of characters could be used to break this same software.

Imagine that your script is manually pulling in log file data and expecting each line to be in a format like the example above. Now, imagine what could happen if someone could sneak in some extra characters on the URL and they ended up there too:

<pre>
// Injecting a tab character
http://test.localhost:8888/cmd.php?data=testing%20%09this

// Injecting a newline character
http://test.localhost:8888/cmd.php?data=testing%20%0Athis
</pre>

Again, these two examples seem to be pretty benign, but think about what could happen when you process these same logs on the other side. If you don't have the correct error handling configuration (or handler) in place, you could risk exposing information through the error messages PHP throws. Effective error and exception handling are [topics for another time](http://websecio.localhost/2012/08/14/Playing-Your-Cards-Close-Error-Exception-Handling.html), but this gives you an idea of another place to watch out for.

It's easy to focus on the main contexts relevant to PHP applications like HTML and script output, but don't forget about other contexts too.

#### Poor unserialize handling

Finally, I want to share an interesting trick having to do with how the [unserialize](http://php.net/unserialize) functionality works in PHP. For those not familiar with serialization, it's a method of formatting data, either from a normal variable or an entire object, that represents it as a string. This string can then be a bit more easily handled and passed around and unserialized on the other side. In fact, PHP uses something similar to the normal serialization for it's own default session handling.

As I mentioned, one of the handy things you can do in PHP is serialize objects. Say I have a `Foo` object that has a `bar` property and a method `baz`. Here's what the result of the serialization might look like:

```
O:3:"Foo":1:{s:3:"bar";N;}
```

The resulting string isn't too hard to interpret. You can see the class, property and method names in plain-text with a little extra formatting around the edges. PHP can take in this string and use the `unserialize` function to restore the object to its former glory. Unfortunately, if you're blindly unserializing strings, this can lead to trouble. Consider this serialized string:

```
a:2:{s:8:"username";b:1;s:8:"password";b:1;}
```

In this case we've just serialized an array of data with a `username` and `password` defined with a value of `1`. To human eyes, it looks like I should be able to check the value of either index to see if it equals one in a `true`/`false` kind of way:

`
<?php
$data = unserialize($_GET['data']);
$adminName = 'admin';
$adminPassword = 'password1234';

if ($data['username'] == $adminName && $data['password'] == $adminPassword) {
	$admin = true;
} else {
	$admin = false;
}
echo 'Admin? '.var_export($admin, true)."\n";
?>
`

What do you think the outcome will be in this check? The result might surprise you...it will always return `true`. Confused? Well, the trick here isn't really in the serialization handling of PHP, it's in the `if` validation. See, when PHP unserializes the string, even though you'd assume that the result would be an integer value of `1`, the actual result is a boolean value of `true`:

`
<?php
var_export(unserialize('a:2:{s:8:"username";b:1;s:8:"password";b:1;}'));

// Resulting array
array (
  'username' => true,
  'password' => true,
)
?>
`

When PHP goes to evaluate the things in the `if` check, it does some type switching on the sly and resolves that, because the `$data['username']` is `true` and the `$adminName` value is set, that must mean that `true` equals `true`, right? Can you spot the real problem here? Here's a hint: it's in the evaluation.

When PHP tries to check two values against each other, it does what's called "type juggling" internally to try to figure out what you mean in comparing the values. In this case, it makes the assumption you're wanting things to evaluate to boolean values and returns the results of that match. Fortunately, there's an easy fix for this sort of thing: a change in the evaluation operator. You'll notice that the `if` check above uses the double equals (`==`) to compare the values. To more correctly have PHP compare them how we'd like, we need to use type checking too. This means using the triple equals (`===`) like this:

`
<?php
if ($data['username'] === $adminName && $data['password'] === $adminPassword) {
	$admin = true;
} else {
	$admin = false;
}
echo 'Admin? '.var_export($admin, true)."\n";

?>
`

This will correctly see that the type on `$adminName` (a string) and `$data['username']` (a boolean) don't match and return the correct `false` evaluation. This is a friendly reminder to use the `===` whenever possible to avoid these kinds of type juggling issues. Some times using `==` is justified, but more often than not it can lead to headaches and long debugging sessions down the road.

#### In Summary

Hopefully these three input-related issues have helped you think a bit more about how you're handling the user data you're being given. Obviously, these aren't the only issues that could possible plague PHP applications, but they are a few of the more "interesting" ones I've come across lately. 

Remember, **always** validate the input you're being given and treat it as tainted until proven otherwise.

#### Resources

- [serialize](http://php.net/serialize) and [unserialize](http://php.net/unserialize)
- [Monolog](https://github.com/Seldaek/monolog) logging library
- [preg_replace](http://php.net/preg_replace)
- [String handling in C](http://www.cs.nyu.edu/courses/spring05/V22.0201-001/c_tutorial/classes/String.html)
