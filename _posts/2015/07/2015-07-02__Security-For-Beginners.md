---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Security is for Beginners
tags: beginner,introduction,opinion
summary: Why isn't application security taught as a beginner concept?
---

Security is for Beginners
--------------

{{ byline }}

> This was originally posted as an article in the [Securing PHP newsletter](http://securingphp.com)

I got into an interesting discussion with a few folks on Twitter the other day about application security topics and beginners. I wondered out loud why appsec concepts weren't taught as a part of "The Basics" when it comes to learning a language. Maybe it's because I'm surrounded by it, but it seems to me like someone couldn't (and shouldn't) consider themselves an accomplished developer in any given language without some knowledge of how to secure what they're writing.

#### Why is the state of security so poor?

The conversation started when *Jim Plush* ([@jimplush](http://twitter.com/jimplush)) wondered "why the state of security is so poor". He shared two quick and slightly saddening facts from recent interviews he's done: 75% of engineers have never heard of XSS (cross-site scripting) and 50% don't know what a SQL injection is. With these two major vulnerability types being on the top of the OWASP Top 10 list, this comes as quite a shock. My reply to him was the point I'm trying to make this week - security should be taught right alongside things like OOP and proper abstraction practices to ensure a complete education, especially with PHP.

*Jim *puts it well: "[Security] should be seen as part of your job as an engineer." I'm 100% in agreement with this. I don't believe that a company or group should have to look for developers with security knowledge specifically for general development roles. I believe that every developer out there should have some security "notches" in their belt and at least understand some of the basics (Top 10 anyone?).

Another fellow Twitter user, *Alex Graul* ([@alexgraul](http://twitter.com/alexgraul)) came in at the end of the discussion and pointed out a sad fact about secure development. He points out that, until the industry as a whole changes to put more of a demand on it (or rewards it when it is learned), it won't be taught as a foundational element for learning a language. It's an unfortunate fact that funding and business needs are what makes the software development world go 'round. It's unfortunate that, without that buy in, developers just won't see the need to grow security knowledge as a part of their development learning from the start.

#### Learn early, Learn constantly

Learning secure coding practices later in your development life instead of earlier usually means un-learning practices you've learned over the years. Much like "bolting on" secure code after the fact, it's an uphill struggle and one that's largely discouraging to developers more set in their ways. These are the developers that I talk to at conferences and feel overwhelmed by the amount of things they feel the need to learn to write secure code. Had they started from the beginning and applied them slowly, not only would they be more experienced in that knowledge but they'd be more likely to share that with others.

#### The Concepts

There's a lot of misconception that developers have to be experts in security to *really* secure their applications. It's easy to forget that, when teaching application security as a beginner topic, that it's just like anything else. You're not going to be a pro at it when you first start out. After all, which of us PHP developers haven't started out with procedural code and then later graduated to object-oriented programming practices. The same goes with the basics of security. So where to start? I suggest following this path and integrating it into your development learning to help mesh the general programming good security practices:

1. Input validation: formats, whitelisting, blacklisting, sanitizing
2. Output escaping: understanding output location (context), escaping accordingly
3. Using HTTPS
4. Security-related php.ini settings
4. Understanding how PHP handles sessions
5. Good authentication practices
6. Good authorization practices
7. The difference between prepared statements/bound parameters and concatenation in SQL statements
8. Good cookie handling practices
9. Understanding what output PHP provides when errors and exceptions are thrown

With all this under your belt, you can then move on to learning more about the specific vulnerability types like XSS, CSRF and SQL injection. You'll also notice that several of these are more general than just PHP. In fact, most application security concepts are relatively general, it's when you start applying them to the language that things get specific. Once you have a good grasp on these topics, then you can move on to more complex things like cryptography, alternative authentication mechanisms and custom handling specific to your application.

