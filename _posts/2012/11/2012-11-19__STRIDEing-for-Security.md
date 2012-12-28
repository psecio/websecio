---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: STRIDEing for Security
tags: stride,sdlc,threatmodel
summary: The STRIDE method of threat modeling gives you a simple way to evaluate the possible weak points in your application.
---

STRIDEing for Security
--------------

{{ byline }}

One thing that can go a long way to help you secure your application is to sit back and
take a good hard look at it and evaluate where the possible problem areas are. This is
known as "threat modeling". It's more than just "oh, I think we need to add some more
validation here" or "maybe this should be more well protected". It digs a bit deeper 
than that, using some of the more common types of attacks (or maybe ones more specific
to you and your industry) and evaluating the strength of your application base on how
it can prevent them. 

#### Threat Modeling

As every experienced software developer knows, even if there's a good security-centric
architecture in place, there's still going to be things that come up that weren't thought
about when the original vision was conceived. There'll be things in the application, 
sore spots, that you know, if you'd "just had more time" could have turned out much better.
Sadly, this kind of thing is all too common in the typical software development lifecycle (SDLC)
and can - if not kept in check - leave some pretty wide security holes. 

Enter threat modeling. There's two ways to go about using them during the development
of your applications - pre-assessment and post-assessment. In the pre-assessment phase,
you can look at the planned features for your application and try to evaluate what the
largest risks are that those features bring with them. Obviously, this will be completely
different for every system out there. There are some similarities between the threats
associated with certain kinds of network and system setups, but because environments can
vary so widely (as can applications), there's no "One Way" to assess the threat of your 
setup.

#### Microsoft and STRIDE

Microsoft, arguably one of the largest companies that knows a thing or two about software
development (I'll leave it up to you if you think this is positive or not), shared what they
saw as a set of common threats - a sort of checklist - that you can run through when doing
your modeling to be sure you're hitting some of the main paint points. 

As a part of the process, you pick apart your application and figure out its various major
parts and ask several questions about them to find where the problems lie. Ideally, this also
includes any kind of data modeling, system architecture and what external services/sources
it users in documentation that can be referred to through out the modeling process.

**STRIDE** is an acronym for the six kinds of threats that MS saw most commonly in their 
development and resulting applications. Most of them are pretty self-explanatory, but I'm 
going to give a brief overview of each to help explain more of what they're about:

- **Spoofing identity**

    Anyone that's ever created any kind of user authentication/login system for an application
    know how important this kind of threat is. Even if you haven't made a system like this
    before, you - as a user of just about any system out there - know how much trouble it
    would cause if someone was able to access your account by mimicking you. This isn't 
    just about user authentication either - spoofing can happen on a layer lower than the
    code would know about (like network address spoofing, falsifying email headers, etc).

    When you're evaluating your application's parts, start to think about how it validates
    that the user is who they say they are. Is there a way for someone to bypass part of the 
    authorization to get to things they shouldn't just by falsifying a bit of information?
    By doing what you can to reduce the dependency of your application on external pieces
    of data for it's security, you can help to minimize the number of possible exploits
    that could be performed on that part of your code.

    Remember, threats can be human too - if there's a human involved in the process somewhere,
    there's nothing saying that whatever tool they use to interact with your application
    and there level could be exploited and their user spoofed. Simple social engineering
    shows that a lot can happen if people aren't on their toes.


- **Tampering with data**

    This sort of threat has more to do with the data that's powering the application than
    it does the sort of input that your users are providing. This sort of threat isn't for
    things like XSS or CSRF problems, it's more about the sort of holes that might allow
    someone to modify the data of your application maliciously. This could be anything, 
    depending on how your application is written - database records (via a `SQL injection`
    vulnerability), text files, binary files, etc. 

    These sorts of threats should include good looks at whatever external data sources your
    application uses and evaluations as to the overall safety and reliability of those 
    sources. Obviously, if you have things locked down tight and firewalled off correctly, 
    you'll help to mitigate some of the problems. 

    As a developer, one of the best things you can do is to [filter the data](/2012/09/14/Dirty-Data-Protect-App-Users.html)
    coming in to your application, no matter the source. Some sources can be trusted more
    than others - determining that is one of the points of this section - but you still
    cannot trust anything outside of what you've already proven as valid.

    Man-in-the-Middle attacks also have to be considered here. Thankfully, HTTPS/SSL provide
    a relatively simple and effective aid at preventing those sort of issues.

- **Repudiation**

    While it might sound a bit more complicated than some of the other things on this list, 
    mitigating repudiation is a pretty simple task if the application does its logging 
    and tracking correctly. The threat of repudiation is basically that, with all of the 
    possible actions that a user could take in your application, that you would not be able
    to provide enough proof that it was actually done (and by them).

    If a malicious user was to enter your system and start to poke around and find an 
    issue where he could make himself an administrator for the remainder of his session, he could
    cause some pretty good damage if he wanted to. If you're not tracking things like what
    user is doing what, their level of user and information about their connection, you
    would have #1 no way of knowing that they did something wrong and #2 not have a way
    to prove that they even did it.

    Simple logging is good to help prevent this kind of threat, but a more complete, robust
    logging scheme is required to effectively track and monitor user access to the system.
    It's a fine balance between what data is useful and information overload, though. Be
    sure when you're evaluating this threat you think about the amount of data being logged,
    what kind of data it is and if there's certain parts of the application that are more 
    important (and should therefore be tracked in more detail).

- **Information Disclosure**

    This is another in the list that's fairly obvious, but it can be one of the most damaging
    if the attacker is able to exploit it. This kind of data could come from any number of
    data source types including database records (via a `SQL injection`), local file information
    (though `Local File Includes`) or even the stream of data flowing between two machines 
    (back to the Man-in-the-Middle). 

    When this threat is evaluated, you also have to consider what sort of information they might
    be able to access were they able to bypass parts of the system and reach into your data
    and pull out what they'd like.

    You can also apply this threat category in a slightly different way - but using it as
    justification to instruct and educate users about your system and what's happening 
    with their data when they give it to you.

    There's a human element to this one too - certain people will have access to certain
    things and it's human nature to try to make things easier and help others out with 
    favors. Unfortunately this can also leave some doors wide open to people that shouldn't
    have that access. You have to educate not only your users on the correct ways of
    controlling access to their accounts (don't share passwords, lock terminals, etc).

- **Denial of service**

    This threat has become so common that quite a few people, even those way outside of 
    any kind of security knowledge know what a *Denial of Service* attack is and what 
    it can do to a service/company. What you, as a developer, have to do is ensure that
    the services and features you're providing aren't open to this sort of attack.

    This is particularly important when providing things like APIs and web services to your
    users. You have to make doubly sure, since the input really could be anything, that
    your application can handle it and fail accordingly. Be sure that invalid user input
    is evaluated quickly and correctly - and dismissed - so it's not causing processing
    issues on the backed.

    When most people thing of a DoS attack, they think if overloading the servers with
    a large number of requests so it can't service real customers/users. This is a common
    case, but if the attacker is clever enough, they could find a spot in your app that
    breaks or times out if bad data is given to it. This can cause the process to hang, 
    making it unusable by anyone and effectively denying service.

    Also consider things like memory and disk usage as well, being sure that you're not
    overloading the system with too much data in memory at the same time or writing out
    too many logs (where it could fill the drive).

- **Elevation of privilege**

    This type of threat category could almost be seen as a super-set of several of the 
    other items in this list. Things like *information disclosure* or *data tampering* 
    could all come as a result of an attacker that has figured out how to go above 
    their permissions level and gain access to resources they shouldn't. This can happen
    either on a per-session basis or as a result of the user somehow updating their 
    permissions in the system to escalate their status in the system every time they
    access it. 

    When thinking about this threat, take a good long look at how you authorize users
    and what they can access. You cannot trust that a user won't discover another part
    of the application just because they can't see it when they're using the service.
    When developing applications, all user actions should be run through a system that
    checks their access levels and evaluates to ensure their allow/deny status right then.
    Assumptions should never be made that a user is coming from a certain place and 
    immediately allow them access. 

    Remember, user authentication and authorization are [not the same thing](http://people.duke.edu/~rob/kerberos/authvauth.html)
    so have strategies in place for both sides of the equation.


A handy thing about a lot of this work is that, since threats are directly linked to the 
targeted part of an application and the features it provides, they can be "reused" across 
a system that uses similar features in many different places. 

Additionally, not all of the *STRIDE* threats will apply to every part of the application,
making it easy to cross a few off the list for discussion immediately.

#### Next Steps with DREAD

If you'd like to take your modeling a step further, you can look into the [DREAD rating system](https://www.owasp.org/index.php/Threat_Risk_Modeling#DREAD)
that helps you to judge which of the threats you uncovered during the *STRIDE* exploration 
are the most severe and should be tackled first. It relies on a rating system for a few different
categories (that make up the acronym): damage potential, reproducibility, exploitability,
affected users and how easy it is to discover.

Most developers are doing evaluations like this all the time in their heads as they write
their code and discover bugs, but *DREAD* gives you a solid set of numbers you can look at
and see which makes the most sense to get fixed.


#### Resources

- [Microsoft's STRIDE page](http://msdn.microsoft.com/en-us/library/ee823878(v=cs.20).aspx)
- [Microsoft on Threat Modeling](http://msdn.microsoft.com/en-us/library/ff648644.aspx)
- [Wikipedia on STRIDE](en.wikipedia.org/wiki/STRIDE_(security))
- [OWASP on STRIDE](https://www.owasp.org/index.php/Threat_Risk_Modeling#STRIDE)

