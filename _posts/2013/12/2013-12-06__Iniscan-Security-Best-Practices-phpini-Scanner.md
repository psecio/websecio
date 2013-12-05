---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Iniscan - A Security Best Practices php.ini Scanner
tags: phpini,scanner,iniscan,opensource
summary: @todo
---

Iniscan - A Security Best Practices php.ini Scanner
--------------

{{byline}}

A while back I was looking at writing an article for this site about some of the security-related best practices when it came to working with your `php.ini` settings file. For those that don't know (and if you've spent any time with PHP at all, you should know) the `php.ini` file is the "master settings" file for your PHP installation. It's where the PHP interpreter (both on the web and command line side) for how to set itself up and what to turn on and off.

Since it's a global configuration file there's lots of different kinds of settings that can be included like:

- Output buffering limits
- Disabling functions
- Maximum execution time/memory usage
- Error handling and display preferences
- Allowing access to include or open remote resources

...and more. It's also what lets you load in external extensions and configure settings for more specific areas like session handling, regular expressions, mail server configuration and various database extensions. You can pretty easily see the importance of this file, and having the wrong settings for the options inside could leave some pretty big gaps in the security of your application.

Don't get me wrong, there's lots of things you can do at the application level that can help increase the security of your application - things that can't be done with configuration settings. When you talk about `php.ini` security, though, you're looking at something a bit more fundamental. There's a lot of places even in the default configuration file where you could be leaving yourself open and not even knowning it. There's lots of articles out there that make some good suggestions about what settings to disable or reasonable levels for other options, but there didn't seem to be one place that had both up-to-date information and most of it gatherted in one place.

As I mentioned, I was gathering up all of the `php.ini`-related security information I could find for an article, but decided on a different path. Instead of just writing an article, I decided to make something more useful to the average user that would apply the lessons I'd been learining about secure PHP configuration. So, I created the [Iniscan](https://github.com/psecio/iniscan) tool - a command line utility you can run against your `php.ini` file and have it advise you about some of the best practices when it comes to securing your application on this foundational level.

#### Iniscan - an Overview

The goal behind the [Iniscan](https://github.com/psecio/iniscan) project was to gather togehter the wide range of best practices. This includes both the more general PHP audience and those looking for things a bit more speciifc to the version of PHP they're running. The scanner looks at the `php.ini` file its been given (either the default or from a command line option) and compares the settings against its own rule set, looking for issues.

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
bin/iniscan --path=/path/to/php.ini
`

#### The scanner in-depth

#### And it's open source!

#### Resources

- [Iniscan on Github](https://github.com/psecio/iniscan)