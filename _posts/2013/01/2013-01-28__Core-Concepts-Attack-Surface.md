---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Core Concepts: Attack Surface
tags: coreconcepts,attack,surface
summary: No summary yet
---

Core Concepts: Attack Surface
--------------

{{ byline }}

If you've ever looked at your application and wondered what kind of attacks it could be 
vulnerable to or what kind of resources exposed, you're already familiar with this "core
concept" term and may not know it. The "Attack Surface" of your application is just this.
It's the part of the application that an user, malicious or not, could access directly or
through an exploit in your system. This includes things like:

- Public resources or endpoints
- Any open services on the hardware your application runs on (ports, other software, etc)
- Protected resources with minimal security (or, heaven help you, security through obscurity)
- Publicly exposed database connections
- Open sourced code from a third party tool you're using 

All of these, and more, are things that need to be taken into consideration when you're thinking
about what's vulnerable in your application. That's the definition:

> Figuring out the Attack Surface of your application is the mapping of its parts that 
> need to be tested for security vulnerabilities, regardless of if they're public or not.
> It involved estimating risk and evaluating countermeasures put in place to see if 
> they're good enough prevention against possible attacks.

There's a key point in there to remember - not all of your attack surface is going to be
a publicly facing part of your application. Remember, while a large percentage of what
you'll need to worry about is external, there's still always the risk of internal security
issues caused - inadvertently or on purpose - from people inside your company or organization.


#### Aspects of The Surface

There's a lot of things that can contribute to the Attack Surface of your application, but 
for the purposes of this article I'm only going to focus on the software security side. As
developers, there's a lot that can be done to ensure that the services and functionality of
your application are protected, without having to worry too much about the other pieces of
the puzzle.

I've talked some about the things that could make up the attack surface of your 
application in more abstract terms, but let's get a bit more specific with some more 
real-world examples. For our purposes, lets use a [REST](http://en.wikipedia.org/wiki/Representational_state_transfer) API
based on something like the [Slim](http://www.slimframework.com/) microframework.

Here's a super basic example of an endpoint, with authentication, that displays some 
user data back to the visitor (we're assuming they've already logged in at this point):

`
<?php
$app = new \Slim\Slim();
$auth = new Auth();
$db = new Db();

$app->get('/user/:username', function($username) use ($app, $auth, $db) {
    if (!$auth->user->allowed()) {
        $app->render('notallowed.php');
    }

    $user = $db->find(array('username' => $username));
    if ($user !== null) {
        $app->render('user.php', $user);
    }
});

$app->run();
?>
`

It's a pretty simple example, but there's a lot of things to consider about it that could
contribute to the overall integrity of your application, things like:

- Does the `allowed` method check evaluate that the user is logged in correctly?
- Is the database connection valid? (not checked before use)
- There's no filtering being done on the value for "username", leaving it open to SQL injection
- The data bing passed to the view (the second call to `render`) isn't being filtered at all

Now, there's a whole science to determining the severity of what's open to attack in your
application (want more? [check this out](http://www.cs.cmu.edu/~wing/publications/Howard-Wing03.pdf))
but one of the easiest ways to record the current issues is in terms of the vulnerabilities 
they're related to. Some of the easiest ones to spot are those dealing with the 
[OWASP Top Ten](https://www.owasp.org/index.php/Category:OWASP_Top_Ten_Project). In the case
of our example above, we fit into a few of them:

1. **A1**: SQL injections (not filtering input)
2. **A2**: Cross-site scripting (not filtering output)
3. **A3**: Broken session management (possible, but hard to tell here)
4. **A5**: Cross-site Request Forgery

There's also another set of issues called the "[SANS 25](http://www.sans.org/top25-software-errors/)" 
that's a set of the most commonly found issues from [SANS](http://sans.org). Some of them
are similar to the OWASP items above, but there's a few additional ones to consider:

1. **CWE-807**: Reliance on Untrusted Inputs in a Security Decision (due to unfiltered data)
2. **CWE-723**: Incorrect Permission Assignment for Critical Resource

Remember, when determining the attack surface of your application, you're not just looking 
at the current code and finding the issues and describing those. You're describing the 
overall risks associated with the application.

Also, it's easy to look at your application and assume that the risks are going to be the same
across most of the application. You use the same authentication across the entire site, right?
So if you assess that risk in one area, why bother with it in another? There's an easy answer
here - while the app may share an authentication mechanism, the *authorization* for the 
sections of the site will most likely be different. 

#### Minimizing the Surface

So, I've looked at some of the things to consider and how to evaluate your application's 
current attack surface. Now lets look at some practical things you can do to help remove
some of this risk and make sure you've limited the amount of potential surface attackers
could abuse.

First off, if you have the luxury of doing so, it's much easier to **evaluate the surface
during planning**. This gives you the extra benefit of making good choices about implementations
that will both best suit the application and introduce the least amount of risk. This isn't
just for new features, either. This kind of planning should also take place when refactoring
the application - *especially* for when critical components (like authorization or resource
permissioning) are being reworked.

Secondly, if your application is pre-exisiting and you don't already have one, you should 
**create a security policy** that your group's developers can look at and find the standard
answer for given situations. It should contain things both general to web application 
development and more specific to your application, things like:

- Risks associated with functional pieces of the application
- Practices regarding input validation and output escaping (down to the specific library if need be)
- Authentication practices and authorization processes
- Common structures of data outputted (and/or messaging)
- Logging requirements (type, contents, destination)
- Encryption requirements

Optionally, the policy can also contain how it will be enforced and by whom.

Thirdly (and this was just touched on), implement **good logging and monitoring** of your 
applications. Without good logging of what's going on in your application and checking to 
ensure it's not being abused, you can't know for certain that the measures you've put in 
place are effective. Testing them is great, but ensuring their working as expected in 
a real-world setup is invaluable. There's things that attackers can think of that may 
not be covered by your tests, so seeing where they're abusing the system and what kind of 
attacks they're using real time can help you more effectively protect the app.

#### CLASP and Software Development

One last thing I wanted to mention before finishing out this article was the [CLASP](https://www.owasp.org/index.php/Category:OWASP_CLASP_Project) project that the OWASP group has put together. By 
definition it is:

> CLASP (Comprehensive, Lightweight Application Security Process) provides a well-organized 
> and structured approach for moving security concerns into the early stages of the software 
> development lifecycle, whenever possible. CLASP is actually a set of process pieces that 
> can be integrated into any software development process. It is designed to be both easy 
> to adopt and effective.

One of the early steps in this process is determining the *attack surface* and the planning
around the risks it introduces. Note: this kind of planning can happen before even a single
line of code has been written (and probably should). While the CLASP project seeks to define
the security of a system as it relates to the overall picture of security withing an 
organization or project, a big part of it is assessing risks (current and future),
planning the mitigation of them, describing the types of problems that could come up
and the consequences of the exploitation of the issues - all things that can be discovered 
in the research for the attack surface of your application.


#### Resources

- [OWASP Attack Surface Analysis Cheat Sheet](https://www.owasp.org/index.php/Attack_Surface_Analysis_Cheat_Sheet)
- [Attack Surface Metric](http://www.cs.cmu.edu/~pratyus/tse10.pdf)
- [Measuring the Attack Surface of Enterprise Software](http://www.cs.cmu.edu/~wing/publications/ManadhataKarabulutWing08.pdf)
- [OWASP CLASP Project](https://www.owasp.org/index.php/Category:OWASP_CLASP_Project)
