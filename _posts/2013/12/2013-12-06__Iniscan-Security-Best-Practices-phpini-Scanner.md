---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Iniscan - A Security Best Practices php.ini Scanner
tags: phpini,scanner,iniscan,opensource
summary: Securing your configuration is important - let this tool help
---

Iniscan - A Security Best Practices php.ini Scanner
--------------

{{byline}}

A while back I wrote an article for this site about some of the security-related best practices when it came to working with your `php.ini` settings file. For those that don't know (and if you've spent any time with PHP at all, you should know) the `php.ini` file is the "master settings" file for your PHP installation. It's where the PHP interpreter (both on the web and command line side)looks for how to set itself up and what to turn on and off.

Since it's a global configuration file there's lots of different kinds of settings that can be included like:

- Output buffering limits
- Disabling functions
- Maximum execution time/memory usage
- Error handling and display preferences
- Allowing access to include or open remote resources

...and more. It's also what lets you load in external extensions and configure settings for more specific areas like session handling, regular expressions, mail server configuration and various database extensions. You can pretty easily see the importance of this file, and having the wrong settings for the options inside could leave some pretty big gaps in the security of your application.

> One thing to point out here - there's an option you can compile into the PHP installation (`with-config-file-scan-dir`) that points at another directory and loads all of the `*.ini` files it files there looking for PHP settings. It does this after the main `php.ini`, so there's always the chance it could overwrite some of the main settings.

Don't get me wrong, there's lots of things you can do at the application code level that can help increase the security of your application - things that can't be done easily with configuration settings. When you talk about `php.ini` security, though, you're looking at something a bit more fundamental. There's a lot of places, even in the default configuration file that PHP ships with, where you could be leaving yourself open to potential abuse and not even know it. There's lots of articles out there that make some good suggestions about what settings to disable and reasonable levels for other options, but there didn't seem to be one place that had both up-to-date information.

> I have to give a shout-out to [Ed Finkler](http://twitter.com/funkatron) for breaking the ground on this in his original concept - [PHPSecInfo](phpsecinfo.com). His tool is a PHP utility you could drop into your web application and visually tell where the pain points where. The HTML is color-coded according to pass/fail status and gives a bit more information about the issue as well as recommendations for good setting values. Unfortunately, it hasn't been updated in a while, but it's still a useful tool. Just be careful if you use it - it can provide a *lot* of information disclosure to those accessing your site. It's *not* recommended for regular use in production, but could be useful during initial configuration and setup.

As I mentioned, I gathered up all of the `php.ini`-related security information I could find for an article and decided to be a bit more practical about it too. Instead of just writing an article, I decided to make something more useful to the average user that would apply the lessons about secure PHP server-side configuration outside of any framework or application platform. So, I created the [Iniscan](https://github.com/psecio/iniscan) tool - a command line utility you can run against your `php.ini` file and have it advise you about some of the best practices when it comes to securing your application on this foundational level.

#### Iniscan - an Overview

The goal behind the [Iniscan](https://github.com/psecio/iniscan) project was to gather together the wide range of best practices. This includes both the more general PHP audience and those looking for things a bit more specific to the version of PHP they're running. The scanner looks at the `php.ini` file its been given (either the default or from a command line option) and compares the settings against its own rule set, looking for issues.

To try it out on your own system, you can install it easily with Composer:

`
{
	"require": {
		"psecio/iniscan": "dev-master"
	}
}
`

Once it's downloaded you can run it against your own `php.ini` via the command line:

`
bin/iniscan scan --path=/path/to/php.ini
`

This will produce output similar to:

<pre>
== Executing INI Scan [12.05.2013 17:59:40] ==

Results for php.ini:
============
Status | Severity | Key                      | Description
----------------------------------------------------------------------
PASS   | ERROR    | session.use_cookies      | Accepts cookies to manage sessions
PASS   | ERROR    | session.use_only_cookies | Must use cookies to manage sessions, don't accept session-ids in a link
FAIL   | WARNING  | session.cookie_domain    | It is recommended that you set the default domain for cookies.
PASS   | ERROR    | session.cookie_httponly  | Setting session cookies to 'http only' makes them only readable by the browser
</pre>

The (color coded) output provides a few things:

- A pass/fail status of the check
- The severity level of the issue
- The `php.ini` "key" that it's running the check on
- A summary description of what it's checking and why

> If you're interested in what its actually checking, take a look at the `src/Psecio/Iniscan/rules.json` file for the definitions and the values they're checking for.

When failures are reported for any of the checks, the command will exit with a non-zero status, making it easier for you to programatically check for the overall pass/fail status.

There's also a set of command line options you can provide that make things a bit more flexible:

- `--path` defines the `php.ini` path to review. If this isn't given, it tries to figure it out from PHP's internal settings
- `--fail-only` tells Iniscan to only show the failing options, not the passing items
- `--format` lets you output the results in various formats (currently supports the default CLI output as well as JSON and XML)
- `--threshold` defines the lowest value of failures you want to see (Ex. --threshold=ERROR would show Errors but not Warnings)
- `--php` lets you define the version of PHP to use for evaluation. Without this, Iniscan figures out the version from `PHP_VERSION`
- `--output` defines the directory you want the output to be written to (like with the output formats of JSON and XML)

So, if you wanted to only see the failures and output the results to a JSON data set, you might use:

`bin/iniscan scan --path=/path/to/php.ini --fail-only --format=json --output=/path/to/output-dir`

There's also two "listing" commands that're included to give you an idea of the checks that are performed by the tool:

`bin/iniscan list-tests`

And of why your current `php.ini` settings are for the file being processed:

`bin/iniscan show`

#### A Brief look at PHP.ini security

I was going to try to go through each of the items in the rules list and talk about why they were being checked, but instead I'm going to break it up into a few sections and give an overview of some of the basic security issues that can come up because of bad settings. Some of this has already been mentioned [in another article](/2012/08/13/Stay-Safe-in-Your-Phpini.html) but bears repeating in a bit more summarized format.

##### General PHP

There's several settings that don't relate to any particular PHP feature and are a bit more general. Some of them are debatable as far as how much of an issue they can cause (like `expose_php`) but several of them can make a real difference. For example, if you know for a fact that your application will never be opening files from remote locations via anything like an include/require or [fopen](http://php.net/fopen), you'd do well to turn off the `allow_url_fopen` and `allow_url_include` settings. These can prevent remote file inclusion issues that could code from unexpected code execution, like say from a user uploaded ".php" file placed in a publicly accessible directory (tsk tsk).

There's also a few when it comes to error handling mostly related to what kind of information is shared with the end user. Having the `error_reporting` and `display_errors` values turned to "on" in development can definitely help with debugging your scripts and tracking down errors. In production and public facing environments, however, this same information can disclose things about your application you may not want a potential attacker to be privy to. For example, say you don't have errors turned off in production and the end user is trying to break things with some SQL injection testing. If there's an error that pops up as a result of these requests, that attacker gains knowledge about the internals of the application and, thanks to PHP's natural inclination to provide stack traces on some errors, information about the actual files making up your application.

##### Session settings

Sessions are a big part of the usefulness that comes with using PHP. Where other languages may require a separate session handler to be introduced, PHP includes one in its default functionality (and has for a long time). As you'd expect, this means that the configuration is right there along with the general PHP ones, all in the `php.ini`. There's a lot of these settings that are already defined but that doesn't mean they're the most secure options out there. The current defaults are more along the "these will work on the most systems out there" kind of thinking.

So, which ones do we need to keep an eye on? Well, the first few are around the cookie handling for the session handler. By default, PHP sessions use a cookie with a `PHPSESSID` hash as its value. The Iniscan tool checks to ensure that your `session.use_cookies` and `session.use_only_cookies` settings are enabled to prevent sessions from being injected any other way. There's three other settings that are more about protecting the actual cookies themselves: `session.cookie_domain` to restrict the domain that can access them, the `cookie_httponly` setting to prevent access from sources outside the web environment and the `cookie_secure` setting to prevent access to the cookies outside of HTTPS/SSL connections.

There's a few more custom checks that are happening as well, one dealing with the path the script writes its sessions to and the other about the path to an entropy file. The first setting, `session.save_path`, tells PHP where to write the session files. The default on this is is writing to the `/tmp` directory on the local file system. There's an obvious problem with this and it has to do with the default permissions on that directory. The original intent of `/tmp` was to have a place that any application could read and write, including the web server running PHP. So, imagine what would happen if someone compromised the server via another method and was able to read the contents of those session files. By default, PHP just takes the data in your session and (kind of) base64 encodes it and writes it to a file. This is pretty easy to not only open up and be human-readable but also pretty easy to change. Iniscan checks the path you have set for the `save_path` and ensures it's not world-read/writeable.

The second custom session check is something that can help increase the randomness of the `PHPSESSID` hash used in the value for the PHP session cookies. While the default hash generation is acceptable, the PHP development group wanted to allow for a way users could help to make it more random. Rather than trying to introduce more functions into the language that'd have to be in your code, they went to a lower level and defined a `session.entropy_file` setting in the `php.ini`. By default this setting isn't defined but in PHP versions 5.4.0 and higher it uses the `/dev/urandom` or `/dev/arandom` if available.

These are good sources for random data, but what if you're below PHP 5.4.0? You can assign any file you'd like to the `entropy_file` setting and PHP will use the contents of that file in it's random generation process for the session ID. There's been some issues in the past with PHP's session ID generation being too predictable because its implementation was "random" (note the quotes there) and it made it possible to predict the results of other requests and allow for things like session fixation attacks.


##### Custom checks

There's also a few that are custom checks inside the Iniscan told that have more logic than just "is it set" or "be sure it's turned off". One has to do with the functions that are disabled via the `disable_functions` setting. This setting allows you to prevent PHP from executing certain functions and can be useful to turn off things like [exec](http://php.net/exec), [passthru](http://php.net/passthru) or the other file system execution functions. This can prevent unauthorized code from making system calls and possibly (depending on the access level of the executing user) make changes to local files.

Another is a bit more specific and was only really a problem on specific versions of PHP. The issue, referenced by [CVE-2013-1635](https://cve.mitre.org/cgi-bin/cvename.cgi?name=CVE-2013-1635), affected PHP versions 5.3.22+ and 5.4.13+ and was related to an issue with the SOAP extension's WSDL cache directory - `soap.wsdl_cache_dir` - being able to be set outside of the `open_basedir` restriction when it was enabled. While it's not exactly a "best practice" per se, it is something to be aware of. The custom checks allow Iniscan to use more complex logic to see if you're at risk (like detecting the current PHP version).

#### And it's open source!

One last reminder before wrapping this article up - the [Iniscan](https://github.com/psecio/iniscan) project is open source and hosted over on Github and is always open for contributions. It doesn't have to be code, either - there's always going to be things not thought of or specific to your configuration that might trip it up. If you come across those, [submit an issue](https://github.com/psecio/iniscan/issues) about it and it'll get worked. The project has already gained some attention, both on Github and a good number of installs via Packagist, but it can always use input or pull requests to help make it even better.

It's my hope that this tool will be a helpful way for the PHP community to help with server-side security in a simple but effective way. The project's always looking for good ideas and "what's next", so if you have some good suggestions or even just general feedback, [let me know](mailto:iniscan@phpdeveloper.org)!

#### Resources

- [Iniscan on Github](https://github.com/psecio/iniscan)
- [PHP Manual on php.ini](http://php.net/manual/en/ini.php)
- [Stay Safe in Your php.ini](/2012/08/13/Stay-Safe-in-Your-Phpini.html)
