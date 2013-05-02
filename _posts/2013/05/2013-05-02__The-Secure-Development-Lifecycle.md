---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: The Secure Development Lifecycle
tags: secure,development,lifecycle,microsoft
summary: Learn about Microsoft's industry standard secure development practices.
---

The Secure Development Lifecycle
--------------

{{ byline }}

A while back some folks over at Microsoft put together a draft of something they call
the "[Secure Development Lifecycle](http://www.microsoft.com/security/sdl/default.aspx)".
This lifecycle consists of several suggested steps an organization can go through to help
encourage secure development.

> You might come across the term "push left" when talking about secure development. Traditionally, security validation and exploit location has been a step further along in the process than development - usually something QA does. The "push left" mentality puts development to the left of QA/testing in the flow of the lifecycle and advocates the integration of these practices early.

There's five main steps to the actual workflow that makes up their suggested process:

1. Gathering of the requirements
2. The design of the system
3. Implementation and the actual work of developing the application
4. Verification of the security of the result
5. Release of the tested, verified product

There's two other steps that are a bit "outside" of the rest of this flow but should
still be considered very much a part of the process - the training of the development
group and the creation of an incident response plan when issues come up.

Now, there's been whole books written on this flow, so I'm not going to get into
too much detail on it. I just want to give you a high level look at each of the sections
so it can get the wheels turning in your own mind about making it work in your
environment.

#### Gathering Requirements

At the start of any software project, there's always a requirements gathering phase. Even
if you're following some of the agile methodologies, you still have to have something
to write stories about. Coming up with the security standards and requirements for your
application is no different. These should be gathered by the same people, in fact. Since
the idea of secure development is to start at the very beginning of the coding, it helps
to have both the feature requirements and the security requirements side by side during
planning.

Since application security is such a subjective thing to think about, there's no hard
and fast rules about what might need to be included. Here's a few things to think about,
though, to help you along your way:

1. Are there external requirements for how the user data should be handled and stored?
2. What kind of system do we want to use for tracking issues?
3. What parts of the application are public versus those requiring a login?
4. What kind of permission and role structure does the application need?
5. Are there external services we'll need to interface with to make the app work?

Obviously, this is just a small sampling of things to think about. There'll be a lot more
that will be dependent on your application type. Be sure you think about all aspects
of the data flowing through your system and how your users will interact with it.

Be sure to think about the technologies you're planning to involve and how much of them
involve third party (possibly untested) code. Look for libraries that have been around
for a while and seem well developed. If you've been developing PHP for any length of time
you can usually look over the code of a library and tell if the author knows what they're
doing. Look on Github for projects that have a good history of resolving issues, are
full featured and have some sort of community around them. There's a growing trend in
PHP development of unit testing. Look for something that has these tests as it can tell
you a lot about the stability of the project and how much dedication they have to the
end result.

I> This is also a good time to define the security standards documentation that developers
I> on the project should follow as they progress through the application. Give them a
I> "checklist" of sorts they can keep handy and refer to often.

##### Quality Assurance

Another thing to think about here is the tools you're going to use to enforce the standards
you're setting up. Unfortunately, there's not too many PHP-centric tools out there to
help with this kind of thing. There's one tried-and-true method that's proven to not
only create better code but also more secure code - **code reviews**. I hear of more and
more organizations that are implementing code reviews as a part of their standard development
practices. Basically, a code review (sometimes called a "peer review") is another developer
looking over the code you're committing and ensuring it's up to the defined standards.

PHP have something that makes the automated checking of the code style simpler with *PHP_CodeSnffer*,
but there's not much out there for security testing. There are some static scanning tools
that can help to catch unsafe practices like using [exec](http://php.net/exec) or directly
outputting data without filtering it, but they're not very well maintained. You can also
get this as a service from another company like [Veracode](http://veracode.com).

##### "Bug bars"

It's also recommended that you come up with the "bug bars" or levels of allowed bugs and
types that can be in the different stages fo your development. Several groups will divide
this up by environment. For example, they'll say that, in order for a release to move
on to be tested by QA, there has to be five or less bugs in the development environment.
This also sets the level of these bugs - say, of the five, only one can have a "High" severity
and the rest must be "Medium" or below.

##### Risk Assessment

This part of the process isn't necessarily something that developers need to do, but
it can't hurt to be involved. In the following chapter I talk some about the idea of
"risk" and how to determine it for you application. There's a lot involved in trying to
estimate risk, and it helps to think about those things as you write your code. If you
can get in on some of the threat modeling and risk evaluations done for the application
- even if it's just once - it'd be beneficial.

#### System Design

Once you have the requirements firmly in hand you can start on the fun part - actually
planning out the design of the system. Ask any developer and they'll tell you that getting
into the technology choices and structure of the application is one of their favorite
parts. If you've been lucky enough to have been given a "green field" project (brand new)
you're doubly lucky. There's no legacy code to have to worry about, just a wide open
space where you can start putting stakes in the ground.

This is the part of the process where you start thinking technically. You look at the
requirements you've been given and try to figure out a good structure for the overall
application and which parts are going to be the most important security-wise. If there's
an architect as a part of your team, it'll fall in his or her lap to think about these
things and give a "game plan" to the rest of the development staff for future development.

##### Worrying about Attack Surface

An "*attack surface*" is something that might be new to some developers out there. The
basic idea is that you take a look at your application from the outside and think about
what's exposed that a would-be attacker could abuse. This could include things like:

- public endponts on a REST API
- Login/Forgot Password pages
- Administrative functionality
- Normal user functionality

It's up to you, as a developer, to think about minimizing this "surface". Following
principles like the "least privilege" and "defense in depth" can help this. For more
information on these, check out the "Best Practices" chapter near the end of this book.

##### Threat Modeling

Threat modeling is the process by which you would through the parts of your application
and think about the threats that could effect the functionality. If you've never done
this kind of thing before it can be a little daunting. There are, however, some frameworks
out there to help guide you through the right kind of thinking.

**STRIDE** is another Microsoft invention. It's an acronym that stands for **S**poofing
identity, **T**ampering with data, **R**epudiation, **I**nformaton disclosure, **D**enial
of Service and **E**levation of privilege. Using the STRIDE model, you can look through
your features and evaluate the level of risk that's associated with each of these options.
By breaking up your application into bite sized chunks, you can more effectively run it
past these common threats.

> Don't be fooled into thinking that worrying about the risks of each piece individually it enough. Most software does its work through components interacting with each other, so be sure that you take into account how the components will talk to the others when assessing risk.

Another common approach is **DREAD** modeling. DREAD is another acronym that stands for
**D**amage, **R**eproducabiity, **E**xploitability, **A**ffected users and **D**iscoverability.
This approach is a bit different than the STRIDE way of thinking. With STRIDE, you have
a specific set of threats to think about and help resolve. With DREAD it's more about
the end result of the attack than the method used. There's a focus on how the attack
effects the system overall and the difficulty of finding and reproducing the attack is.

#### Implementation

With the planning of that "what" and "how" you're building the system with out of the way, you
can get down to the business of actually implementing features. With a secure coding standard
firmly in hand, you can start to talk that first set of stories (you are doing agile,
aren't you?) and start building up the system and its structure. All of the tools and
techniques that you planned out in the previous steps are put to use here. Approved
tools, methods for testing and coding standards should be applied and tested for as
the development progresses.

During this time, some effort should be spent to determine what kinds of situations and
functionality of PHP you want to avoid. Generally, there's some pretty easy wins here
by restricting things like [eval](http://php.net/eval), [exec](http://php.net/exec) and
its cousins as well as things like the direct use of the MySQL extensions ([PDO](http://php.net/pdo) and bound
parameters is a much better option). You can use the `disable_functions` configuration setting
in your `php.ini` to turn off the usage of those methods as a means of enforcing that
part of the coding standard. Obviously, there's going to be certain situations where
some of those methods are needed, but those should be handled on a case by case basis.

You should also think about the versions of the tools and libraries that you're using.
Try to keep things up to date and as patched as possible.

> This **does not** necessarily mean using the latest and greatest versions. Often those early adopters find themselves having to revert back to a previous version because of security concerns or functional issues. PHP itself isn't even immune to this kind of thing. There's been several occasions where the PHP development group has had to push out a new version a day or two after a previous release just to fix security issues.

Track the status of the libraries you're using and, if need be, lock them down to a
specific version until you feel comfortable enough to update. Things like
[Composer](http://getcomposer.org) make this simpler by offering the ability to define
the tag or branch you want to pull the library from on Github.

#### Verification/Testing

As development it progressing, according to the agile methodologies, the code that's
coming out from the developers should be continuously tested. Sometimes this means
humans sitting in front of a keyboard and mouse clicking away on the application, but
a lot of times it comes in the form of automated testing. As was mentioned before,
there's not a lot of PHP-centric testing tools out there, but there are plenty of
web application-related tools that can use and abuse your app regardless of the underlying
technology.

##### Dynamic Analysis

This kind of testing is knowns as "dynamic analysis" and can be run with any number
of scanners, both commerical and free for use. Here's some of the more popular free/open source
tools out there:

- Google's [Skipfish](https://code.google.com/p/skipfish/)
- Subgraph's [Vega](http://subgraph.com/products.html)
- [Burp Suite](http://portswigger.net/burp/) (though this is more than just a scanner)

Point these tools at the frontend of your application and set them to work. They'll produce
reports you can use to find some of the flaws in your applications before the attackers do.
I still haven't found a good one that will output a parsable report (maybe something in
XML or even just a text file) but some of them will return an response code from their
execution indicating the pass/fail status.

If you don't want to go the scanner route and feel like some more in-depth testing is needed,
you can always go with something like [Seleinum](http://docs.seleniumhq.org/) or [Behat](http://behat.org/)
for frontend testing. Behat is a PHP-based *behavior-driven development* tool that
uses tests defined in a [Gherkin](http://docs.behat.org/guides/1.gherkin.html) syntax
for its tests, making them as readable as plain English. Here's an example:


    
    Feature: Testing the user auth for a correct response

    Scenario: Test with a valid username/password combo
        Given that I am on "/user/login"
        When I fill in "username" with "testuser1"
        When I fill in "password" with "testpass1"
        And I press "loginButton"
        Then I should see "Login Success!"


This, along with a good set of unit tests (with something like [PHPUnit](http://phpunit.de)),
can be executed as often as needed. Most development groups will make test execution 
a part of their continuous integration efforts. They run these and other tasks via something
like [Jenkins](http://jenkins-ci.org/) to track the status of the quality of their software.

##### Evaluating Changes

Having a good automated testing setup and some good human-based exploratory testing is good,
but when there's major changes in your application like the addition of a new library
or a new section of the site. This should trigger a reassessment of the threat assessment 
of the application and a reevaluation of it's attack surface. Software may be made up 
of individual modules, but those modules have to work together to make things happen. As
as result, a change in one part of the application could effect a number of systems and
this impact needs to be tested.

This usually involves less testing on the automated side and more of the human testers 
executing their own scripts and running through their own plans to ensure the functionality
of the application overall. Specific focus should be given to features that directly 
implemented the changed code.

Thankfully as developers, being able to execute our own automated tests can help reduce 
the number of issues that QA might come across. This holds especially true for an existing
codebase with a good set of tests (unit and functional). Having a well-tested application
allows you to "recklessly refactor" and still be sure your application will function as
expected.

#### Release

Finally the day has come to unleash the hard work you've been doing and set it free for
customers to use and abuse. There's just a few things to clean up before you push that
release out the door, though. You want to be sure you have a few things checked off
the list:

1. A **plan for handling the issues** that come in (yes, there'll be issues - no code is perfect)
This kind of plan can involve not only setting up certain people to handle the reports of
the issues but also the tools you'll need to manage the issues that come up. Usually 
this will be a bug tracker with a specific type of issue for user-reported issues. By
keeping them along side the rest of the issues the development group deals with, they 
can easily be prioritized with the rest of the work.

2. The QA group should run the application through one **final security review** before 
blessing it and sending it down the line. This review should be done on an environment 
that's as close to the end user system or, if possible, multiple environments at once
to ensure that there's no cross-platform issues. If all goes well, the application will
pass with flying colors and you can call it good. 

    Of course, this is the real world and we all know there'll be *something* that comes 
up despite through testing through out the entire process. These issues can either be taken 
as "**acceptable risks**" to be fixed later or they might be "show stoppers" and result in a 
dash to fix issues prior to the release. Hopefully you'll have enough time before the 
final release date of the app to have a little slack to fix these kinds of issues.

3. Finally, when the final review is done and everyone's signed off on everything you're
ready to push it out the door and see how it does. Be sure that you **archive everything**
you've done so far and document everything that needs to be done, what was put off as 
a "Should" or "Pending" issue as well as any documentation that might go with the release.
This also includes all of the tests, both the automated and manual processes, that was
used to ensure the app was working as it should. The idea is that you can have something 
to look back at and know the state of the software at that time. 

    Developers have a bit easier time with this as most of their stuff should be in source control anyway.

#### Resources

Entire books have been written about this suggested process. If you're interested in more detail, I'd suggest checking out some of the links below for more information.

- [Secure Development Lifecycle](http://www.microsoft.com/security/sdl/default.aspx) (website)
- [SDLC on Wikipedia](http://en.wikipedia.org/wiki/Microsoft_Security_Development_Lifecycle)
- [The Security Development Lifecycle](http://www.amazon.com/Security-Development-Lifecycle-Michael-Howard/dp/0735622140) (MIcrosoft Press)
- [Secure Development in an Agile World](http://www.blackhat.com/presentations/bh-dc-10/Sullivan_Bryan/BlackHat-DC-2010-Sullivan-SDL-Agile-wp.pdf) (presentation)

