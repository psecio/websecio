---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: DREADing Your Security
tags: dread,threatmodel,rating
summary: Using the DREAD threat modeling framework you can get a better view of the risk of your application.
---

DREADing Your Security
--------------

{{ byline }}

It seems like every time you turn around, someone in the information security industry is trying
to figure out the types and amount of risks that are involved in the software their company
provides, be it a service or an actual product. There's lots of different methods out there
to try to guage the amount of things you might have to worry about. One of the simpler ones
(and a good starter) is the *DREAD* methodology. *DREAD* is an acronym for a set of principles
that you can use to estimate the overall risk of your applications:

- **D**amage
- **R**eproducability
- **E**xploitability
- **A**ffected Users
- **D**iscoverability

These five points, together with some simple ratings - high, medium and low - can give
you a more clear, overall picture of where the risks lie in your applications. It lets
you spot the trouble points early and work more on migtigating those problems.

The rating system was created by a group over at Microsoft to help model the threats 
they were finding in their own applications. *DREAD* is just one method for assesing
the risks, both technological and human-oriented around your application. The concept of 
*threat modeling* is a powerful tool for any security professional, regardless of their
role in the project or company, to have as a part of their skillset. It helps put things
in perspective for a wide range of positions - developers learn more about the overall 
security of the application, architects know what parts to focus on that are higher risk
and project managers know where the waek points of their applications are.

#### DREAD Defined

So, lets take a look at each of these points and explain them a bit more. A single word 
(or two) carries a lot of meaning, but as with any other security concept, there's a lot 
involved in it.

##### Damage

"Damage" is probably one of the easier ones to start with, as it's what most people think
of when they hear those fateful words, "we've been hacked". They immediately wonder what
kinds of things the attacker was able to do and what they might have done to the internal
systems. Obviously, this is going to be different from application to application and maybe
even parts of the app.

This covers everything involved with the application - data sources, the actual codebase, 
any interfaces the system might have with other applications as well as the server/VM the
site runs on. Any or all of the above could be compromised and it's the job of an effective
DREAD model to think about these things before hand and lower the amount of risk involved in 
each.

##### Reproducability

Some attacks may cause a lot of damange, but they lack one thing - staying power. 

@todo

##### Exploitability

The ease of exploitability is another big factor to consider. The more difficult you can make
it for an attacker to get at parts of your system, the better off you'll be in the long run.

@todo

##### Affected Users

Another factor in determining the overall risks involved in the application is the number
of users that would be effected should something happen. For some systems, this is going to
be a larger cross-section than others. Sometimes the feature that was exploited will only 
efect a certain group of the users too.

@todo

##### Discoverability

There's going to be risks in any application, that goes without saying. Sometimes they're
unknown ones, lying in wait for some attacker to pick them out or, hopefully, someone
on your team to come across and correct. Fortunately, there's a lot of things that can 
be done to prevent some of the more common vulnerabilities. This leaves the less common,
more diffficult to find issues.

@todo

#### DREAD Ratings

The second part of the *DREAD* threat modeling process involves the ratings. Where the five
points above give you more general questions to ask, the rating "points" give you a finer
level of granularity about how much risk is involved in each of the points.

Inside of each of the five sections, there's three rating points - High (3 points), 
Medium (2) and Low (1) and, of course None (0).

@todo

#### DREAD Applied

Let's look at a more practical application of the ideas of DREAD. The [WordPress](http://wordpress.org)
software has, unfortunately, become a poster child for security issues in PHP applications.
It's had a rocky life with some major issues in the past, but in fairness they have come
a long way in recent years trying to secure the software.

Security doesn't mean anything without context, so lets set up a scenario for our WordPress
application to be in so we can accurately rate it with DREAD. Our sample WordPress blog is
a custom-themed corporate wesite with several popular plugins that let its users share data 
with social networks and upload their own images of the company's projects and their happy
users.

@todo

#### Resources

- [DREADful on MSDN](http://blogs.msdn.com/b/david_leblanc/archive/2007/08/13/dreadful.aspx)
- [DREAD Rating System](http://msdn.microsoft.com/en-us/library/aa302419.aspx#c03618429_011)
