---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Safety in PHP Dependencies with Composer
tags: composer,packagist,thirdparty,library,module
summary: Composer providies easy package management for PHP developers, but be careful with what you use.
---

Safety in PHP Dependencies with Composer
--------------

{{ byline }}

One the most important things that's happened to PHP in recent years (well, besides
the new features and improvements to the language itself) is the introduction of 
[Composer](http://getcomposer.org) to the community's toolkit. It's not only made it 
simpler to get the code but it's also had the positive side effect of developers creating
more code and better code that starts to follow some of the 
[standards](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md) that
have been set out by the community.

The tool has made life easier for PHP developers by making a simple, easy to use and 
maintain package management system. It does, however, bring along some issues with it 
and the use of third-party code. The [OWASP](http://owasp.org) group recently posted 
[an updated version](http://owasptop10.googlecode.com/files/OWASP%20Top%2010%20-%202013%20-%20RC1.pdf)
of their "Top 10" list for 2013 (still in Release Candidate at the time of this post)
that includes a new addition to the list - "Using Components with Known Vulnerabilities" (A9).
To put it simply, it's the use of not-your-code packages that may or may not have 
vulnerabilities that could compromise your application and leave it vulnerable. 

Let's take a look at what Composer is first, then we can loop back around and pick
up this issue after...

#### What is Composer?

Simply put, Composer is a package and dependency manager for PHP. It makes it simpler to 
include third-party code just by adding a few lines to a `composer.json` configuration file.
Prior to Composer, PHP had a system called [PEAR](http://pear.php.net) - it's still there
and still works, but has been notorious about being a bit difficult to work with. People
have traied various things over the years to make it a bit easier to use, but there still
were downsides. One of which is the fact that by default it installs at the OS level and
not where the user can necessarily update or change things (like in shared hosting). Sure,
you could install it somewhere else, but it was a little tricky to get it to work quite
right.

Two PHP developers, *Nils Adermann* and *Jordi Boggiano* (along with countless others
that have contributed to the project) created a system that, based on a structured 
configuration file, the `composer.json`, allows you to pull in code based on it's 
entry on the [Packagist](http://packagist.org) website. Since most of the code on Packagist
that use with composer conforms to the PSR-0 autoloading standard, it can be used immediately
with only the need for a `require_once` statement at the start of your script:

`
<?php
require_once 'vendor/autoload.php';
$user = new \DuoAuth\User();
?>
`

Composer does a lot of the work for you, including defining an autoloader that knows 
about the third-party code you've installed. In the above example, you'll see it using 
an object from the [DuoAuth](https://github.com/enygma/duoauth) library. The `composer.json`
file for this would be:

`
{
    "require": { "enygma/duoauth":"dev-master" }
}
`

Then you'd run `php composer.phar install` and it'd do the rest for you, even define a 
custom namespace/class mapping to help make things a little bit more speedy for the 
autoloading. Obviously, this only scratches the surface of Composer's abilities, but 
there's plenty of in-depth articles out there if you'd like to get into those features
, not to mention the [actual manual](https://getcomposer.org/doc/) for the project.

#### Sounds great...so what's wrong with it?

There's no doubt that Composer has been an amazing boost to the PHP development community
and encourages code sharing on a whole different level, but with it comes a few different
risks. 

The OWASP Top 10 for 2013 entry for "Using Components with Known Vulnerabilities" talks
issues with third-party libraries. These include things like versions not being defined 
along side vulnerability reports and that issues may not always make it back to the 
[CVE](http://cve.mitre.org/) or [NVD](http://nvd.nist.gov/home.cfm) lists for reporting.
Often you just have to keep up with the project and its current issues on a more individual
basis to stay informed. With the way Composer/Packagist works, it pulls code from GitHub
repositories. Each of these repositories has its own "Issues" section and, through the 
tracking options GitHub offers, you can watch these repositories and get notified when 
new issues are added (for view the current list).

Keep in mind that for some projects, though, not all issues would be reported that way.
They may have their own bug tracker or mailing list where they keep up with these things,
so be sure you find the most relevant resource.

##### Know Your Code

First on the list, and probably the biggest issue with using a lot (or even a little)
third-party code in your application is the code itself. One of the tenets of security
in development is that the most software that's added, the more risk is introduced. It's
really easy to just pick up a new library and add it to your `composer.json`. Unfortunately,
if you do this without any code review of the incoming library, you never know what could
be hiding in its classes.

Since the Composer/Packagist dynamic duo make it so simple to introduce your libraries 
to the rest of the world, there's a pretty low barrier for entry. Be sure when you're 
looking for functionality to drop into your application, be sure you do a few things
before using it:

1. **Run through all the code, at least once** looking for any issues that might stand
out like poor input validation handling, poorly structured code and assumptions of what
the methods will be given. The status of your application and the security compliance
standards for your company will have a lot to do with how deep this research needs to go,
but don't forget to do it - you'll be glad you did.

2. **Use established libraries** when you're looking for functionality to add. Chances 
are, when something's been around for a while and is more mature, a lot more consideration
and thought has been put into it. This includes working out bugs, fleshing out features
and maybe even having security reviews of its own.

3. **Check their dependencies too** to see what third-party they're using. In several 
PHP packages you'll find on Packagist, they use other third-party code to make theirs
work. An example of this is the popular [Guzzle](http://guzzlephp.org) HTTP client/framework.
This tool is widely used by several projects to work with their HTTP requests. This project,
in turn, uses things like the [Doctrine Common](https://packagist.org/packages/doctrine/common)
library, the [Monolog logger](https://packagist.org/packages/monolog/monolog) and the 
event manager and service manager from the [Zend Framework](http://framework.zend.com).
Guzzle is a great library, but this means you'll also need to review those other packages
in order to ensure they're safe too.

> **Tip:** It's easy to get into "dependency hell" when you're working with third-party code libraries.
> Not only does additional code mean additional risk, it also means possibly having to 
> review code several levels deep to ensure security compliance. Keep this in mind when
> selecting your packages.

The second issue that's a bit more specific to Composer and not just a general "using 
third-party code" kind of tip relates to how it handles the packages it installs. As it
stands right now, Composer doesn't support any kind of checking to ensure that the source
it's pulling from is the correct one. Without this kind of checking, the Composer package
downloads are pulled down and things like Man-in-the-Middle attacks are possible. There's
[been efforts](https://github.com/composer/composer/issues/1074#issuecomment-8394281) to add 
this kind of thing into the manager, but it hasn't gained traction yet. 

As such, keep this in mind when you're using Composer and Packagist. After your initial 
security checks of the code when it's introduced into your application, you'd do well
to check any updates that come through when you run the `composer.phar update` to get
the latest versions.

#### Ultimately, you're responsible

One final thing to keep in mind when using third party code - you've made the choice to 
include the library or module into your codebase and this makes you responsible for whatever
happens through the use of this code. It's easy to think that you can pass the blame off
to the maintainers of the library and point fingers when your application is exploited,
but **you** made the choice, and **you** have to deal with the reprocussions.

Don't take the selection of third-party software lightly - be sure it's safe, secure and
reliable. Following these recommendations won't prevent issues in your use of Composer, 
but it can help prevent some of the more common issues you can control.

#### Resources
- [Composer main site](http://getcomposer.org)
- [Packagist site](http://packagist.org)
- [OWASP 2013 Release Candidate](http://owasptop10.googlecode.com/files/OWASP%20Top%2010%20-%202013%20-%20RC1.pdf)
