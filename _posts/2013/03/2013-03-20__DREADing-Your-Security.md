---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: DREADing Your Security
tags: dread,threatmodel,rating,coreconcepts
summary: Using the DREAD threat modeling framework you can get a better view of the risk of your application.
---

DREADing Your Security
--------------

{{ byline }}

It seems like every time you turn around, someone in the information security industry is trying
to figure out the types and amount of risks that are involved in the software their company
provides, be it a service or an actual product. There's lots of different methods out there
to try to gauge the amount of things you might have to worry about. One of the simpler ones
(and a good starter) is the *DREAD* methodology. *DREAD* is an acronym for a set of principles
that you can use to estimate the overall risk of your applications:

- **D**amage
- **R**eproducibility
- **E**xploitability
- **A**ffected Users
- **D**iscoverability

These five points, together with some simple ratings - high, medium and low - can give
you a more clear, overall picture of where the risks lie in your applications. It lets
you spot the trouble points early and work more on mitigating those problems.

The rating system was created by a group over at Microsoft to help model the threats
they were finding in their own applications. *DREAD* is just one method for assessing
the risks, both technological and human-oriented around your application. The concept of
*threat modeling* is a powerful tool for any security professional, regardless of their
role in the project or company, to have as a part of their skill set. It helps put things
in perspective for a wide range of positions - developers learn more about the overall
security of the application, architects know what parts to focus on that are higher risk
and project managers know where the weak points of their applications are.

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

This covers everything involved with the application - data sources, the actual code base,
any interfaces the system might have with other applications as well as the server/VM the
site runs on. Any or all of the above could be compromised and it's the job of an effective
DREAD model to think about these things before hand and lower the amount of risk involved in
each.

##### Reproducibility

Some attacks may cause a lot of damage, but they lack one thing - staying power. In the
world of the attacker, the only really valuable vulnerabilities they find in your application
are the ones that they can abuse over and over again. Usually, they find these kinds of issues
*because* they've found one they can reproduce. Think about it - if they stumble across something
once, they might get at some good knowledge by accident, but they're looking for more than
just that. They want a a way to milk your system into giving them as much data or exploitability
as possible.

If an attack is not consistently reproducible - say it's time based so it can only happen
once every year - then the risk rating of it goes way down. Obviously, it'll never go down to
the zero level if the attack possibility still exists, but the lower the number the better.

##### Exploitability

The ease of exploitability is another big factor to consider. The more difficult you can make
it for an attacker to get at parts of your system, the better off you'll be in the long run.
Attackers have lots of tools at their disposal including scanners that come preconfigured
with tests for lots of common exploits. All they need to do is point this scanner at
your application and sit back to wait for the results. If they happen to pick up something good,
they can dig a bit deeper into it, sometimes finding other vulnerabilities in the process.

Another thing to take into consideration when thinking about the exploitability of various
parts of your application is the knowledge needed for the exploit. Is this something that
an attacker could just start guessing at and find the problem or would they need some
piece of insider knowledge that only someone in the company might know (domain knowledge)?
They might also need to know some about the structure and technologies used in the applications.
If you're masking what software you're using (like using `mod_rewrite` routing instead of `.php`
file extensions) it can make it more difficult for the attacker.

##### Affected Users

Another factor in determining the overall risks involved in the application is the number
of users that would be effected should something happen. For some systems, this is going to
be a larger cross-section than others. Sometimes the feature that was exploited will only
effect a certain group of the users too.

The larger the group of users that could be effected, the larger the risk. Additionally,
the larger the *amount* of data about those users that could be exposed, the even greater
the risk (access to your entire user database? double whammy). Something else to consider
here - people that might not be direct users of your site but could be effected. This is
just a pie-in-the-sky kind of guess, but if there's something that lets an attacker inject
some kind of abusive script or malicious payload into your site, that opens it up to
a whole other world of "users" to think about.

##### Discoverability

There's going to be risks in any application, that goes without saying. Sometimes they're
unknown ones, lying in wait for some attacker to pick them out or, hopefully, someone
on your team to come across and correct. Fortunately, there's a lot of things that can
be done to prevent some of the more common vulnerabilities. This leaves the less common,
more difficult to find issues.

The scanners I mentioned previously can also be used by those trying to secure their
applications, giving them a "leg up" on the tactics an attacker might use. Be sure to
shop around for a good scanner that will fit your needs. There's some that are a [bit
more well established](http://portswigger.net/burp/) while others are
[relatively new](http://subgraph.com/products.html) to the scene.

#### DREAD Ratings

The second part of the *DREAD* threat modeling process involves the ratings. Where the five
points above give you more general questions to ask, the rating "points" give you a finer
level of granularity about how much risk is involved in each of the points.

Inside of each of the five sections, there's three rating points - High (3 points),
Medium (2) and Low (1) and, of course None (0). Keep in mind that everything in this
rating system is relative. This can make it a bit more difficult to get a handle on it
at first as you have to compare your threats against each other to get more accurate numbers.
There's not really any "DREAD standards" that you can look at and say "oh, I see - if they
can bypass my authentication through a SQL injection, that's a 3." It's usually recommended
that you come up with a table of examples that testers and developers can look to
as a guide for the rating levels.

Lets follow this same theme and set up a scenario for the user authentication part of
an application. This tends to be one of the more sensitive areas of a site, so the ratings
might skew a bit higher than others.

So, for example, let's look at the "user can bypass completely" kind of situation. This is
a bit more extreme as it's a complete bypass, but it gives you a place to start. Since
it could potentially effect pretty much the entire user handling system, all of the
ratings are going to end up as "High".

**D:3, R:3, E:3, A:3, D:3**

On to the next threat that's been located: an attacker can get a listing of all admin
usernames from an API endpoint, but that endpoint is not documented as a part of the API structure.
Since this requires some discovery and trial and error to even find the endpoint, the
exploitability level gets downgraded. The data that's presented there is potentially
damaging, but just having the usernames is only one piece of the data he'd need
to perform an attack. He *could* brute force, but that's generally a last resort if you're
trying to get through an authentication layer. Discoverability goes down too since
it would require either a lot of time spent trying to find the endpoint or some
inside knowledge of the code to even know its there.

**D:3, R:3, E:2, A:3, D:1**

These numbers are then added up and the ones with the highest totals are the most
"at risk" places the development group needs to worry about.

#### A Word About Risk

Risk is a tricky subject to think about as you're developing an application. There's
constantly choices that you, as a developer, will be faced with that could effect the
risks involved for the users of your application. Every time you include a third-party
tool, you're increasing the risk. Each time you make a decision about security default
settings or what level of access to "fail" a user down to, you're modifying the
app's total risks. A lot of developers I know don't think in these terms, though.
They just want to get the work done, have things functioning correctly and well-tested.
There's not an emphasis on risks associated with their actions.

One way to help this (it won't solve it, but it will help) is to "push left" the
secure development process and get devs thinking about these things *as they're writing the code*
and not after the fact. Good security testing practices can help here too. Remember,
don't make assumptions about the security of your application. Model it out and really
think about how it could change things overall...and when in doubt, ask the person
requesting the feature (or Product Owner if you're agile) for a business decision.

#### Resources

- [DREADful on MSDN](http://blogs.msdn.com/b/david_leblanc/archive/2007/08/13/dreadful.aspx)
- [DREAD Rating System](http://msdn.microsoft.com/en-us/library/aa302419.aspx#c03618429_011)
- [Application Threat Modeling on OWASP](https://www.owasp.org/index.php/Application_Threat_Modeling)
- [A Practical Approach to Threat Modeling](http://adventuresinsecurity.com/blog/wp-content/uploads/2006/03/A_Practical_Approach_to_Threat_Modeling.pdf)
