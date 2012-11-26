---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Core Concepts: Attack Patterns
tags: attack,pattern,coreconcepts
summary: Attack patterns provide a common language to refer to threat types and methods of attack.
---

Core Concepts: Attack Patterns
--------------

{{ byline }}

One problem with the ever-changing world of software security is the wide variety of methods
that attackers have at their disposal to try to exploit your applications. New ones are coming
up all the time, but there seems to be a core set of attack types that can be classified and
categorized at a high level to make it easier for security professionals (and those wanting
to secure their applications) to talk about.

That's the idea behind the [Common Attack Pattern Enumeration and Classification](http://capec.mitre.org/)
project (CAPEC for short).

> CAPEC&trade; International in scope and free for public use, CAPEC is a publicly available, community-developed 
> list of > common attack patterns along with a comprehensive schema and classification taxonomy. Attack patterns 
> are descriptions of common methods for exploiting software systems. They derive from the concept of design 
> patterns applied in a destructive rather than constructive context and are generated from in-depth analysis 
> of specific real-world exploit examples.

The concept behind "attack patterns" is a pretty simple (if abstract) one - there's certain 
high-level categories that attacks can fit into, mostly broken up by the kind of technology
they're trying to exploit. They can be used during testing as a method for testers to ensure
not only the bug-free operation of the software, but to also test its ability to handle 
errors, bad data and exploits without even batting an eye. Developers can use them as a guide
to encourage [defensive coding](/2012/11/12/Code-Defensively.html) practices and enhance 
the security of their applications. 

#### Structure of an Attack Pattern

Most developers are familiar with the concept of a "design pattern". If you're not, I'd suggest
checking out some of the many resources that come up [here](http://lmgtfy.com/?q=design+patterns+php)
to get your mind thinking "patterns" instead of "solutions". Attack patterns are similar - they
don't provide you with a technical solution that you can use to fix the issue. They give you
a "mental template" to help you get in the right fame of mind for finding a solution to 
a common problem. Like the name implies, a "pattern" is something that can, and is, repeatable
over time. This includes everything from common attacks using freely available tools to
more specialized attacks that might require custom development to work. Either way, if the 
*type* of attack can be re-executed, it falls into a "pattern". 

Much like any kind of attack, they're not to be taken as isolated instances to be taken care
of and checked off a list. Yes, the attack patterns describe to one type of attack, but they're
also related to other types of attacks. Thankfully, this can also provide you with a "map" of 
sorts leading to other issues lying in wait in your code.

So, what makes up an attack pattern? Well, its not the simplest thing to try to describe an attack
effectively and classify all parts of it. This means the language of the pattern has to be
very descriptive and includes details on:

- The pattern's name
- Its type and subtypes (like "Resource Depletion" and "Resource Depletion through Flooding")
- Possible "Also Known As"
- A general description of the attack
- The intent of the attacker (ultimate goal)
- Participants (parties/software involved to make the attack work)
- Dependencies & conditions
- Sample attack code
- Existing exploits
- Follow-on attacks
- Recommended mitigation
- Related patterns
- Related alerts, listings & publications (including [CVE](http://cve.mitre.org/), [CWE](http://cwe.mitre.org/) and [CERT](http://www.cert.org/nav/index_red.html) IDs)

The set of top level categories currently on the [CAPEC](http://capec.mitre.org/) site (their "Mechanisms of 
Attack") include things like:

- Data Leakage Attacks
- Data Structure Attacks
- Resource Manipulation
- Spoofing
- Injection (through Control Frame into Data Plane)

We're going to look a bit more in-dept at another item in the list, though, to give you a better idea
of what the pattern could contain and how they're defined - "Abuse of Functionality". This is something 
most developers can relate to as it's directly associated with the bugs their software contains.

#### An Example - Abuse of Functionality

The "Abuse of Functionality" attack pattern is pretty self-explanatory at its highest level. The 
attacker is probing the software for bugs and issues that they can use to exploit the target application.
This pattern can involve the use of automated scanners (like [sqlmap](http://sqlmap.org/) or [BurpSuite](http://portswigger.net/burp/)) or just a persistent attacker, hitting "pressure points" they know are common 
problems in certain types of applications.

"Abuse of Functionality" is a parent category that contains several different types of abuse
methods. Several of them have exploit names that match up as something a developer might more
readily understand:

Child Type | Exploit Name
---------- | ------------
Passing Local Filenames to Functions That Expect a URL | Remote File Inclusion (RFI)
Directory Traversal | Local File Inclusion (LFI)
Forceful Browsing | Insecure Direct Object References (OWASP - A4, Top 10)
Probing an Application Through Targeting its Error Reporting | Default [error handling](/2012/08/14/Playing-Your-Cards-Close-Error-Exception-Handling.html)


This list also includes others having to do with things like cache poisoning, threats related to the integrity
of the actual software and other sub-categories like "Functionality Misuse" and "Abuse of 
Communication Channels". Each of the categories has its own details. For example's sake, we'll take a 
closer look at a common one - "Passing Local Filenames to Functions that Expect a URL".

In PHP, all of the "inclusion" methods are, by default, allowed to include files from remote
sources. The `php.ini` variables for `allow_url_fopen` and `allow_url_include` allow this functionality
to be turned on or off, depending on the needs of the application. Obviously, this is a 
good thing to disable if your code doesn't specifically need it. Let's look at a quick 
exploit that could be used in a PHP application. The problem here is really two-fold, but 
try to focus on the "expect a URL" part:

`
<?php
$mySiteContents = file_get_contents($_GET['url']);
echo $mySiteContents;
?>
`

At its simplest, the idea behind this code is a "proxy" of sorts - it's designed to make a
connection to the remote site specified in the `$_GET['url']` variable and pull in the contents
to display to the user. If you've been doing PHP for any length of time, though, you'll see
the two glaring problems immediately:

- There's **no** validation that the incoming value for `$_GET['url']` is actually a URL
- The contents of the `file_gets_contents` call are directly outputted to the user

Add both of these together and you're just asking for it - there's actually multiple kinds
of exploits available with this code, but we're just going to focus on one related to the 
"remote include" variety. Thanks to the script's use of `file_get_contents` to pull in the
remote URL's data, it can also be exploited to fetch a local file just by passing something
like `/etc/passwd` in as the `url` parameter on the request. The contents of this file are
then displayed directly back to the user.

The steps to prevent this are two fold:

- First, you **always** want to filter and verify the data coming from the user. In this case,
  if we're expecting a URL to come in, use something like this to validate it with `filter_var`

- Second, we want to be sure to filter out the data coming in from the external site so 
  we don't inherit any security issues that might be coming with it (like XSS problems).

Here's a brief bit of code showing how to update our example:

`
<?php
$url = filter_var($_GET['url'], FILTER_VALIDATE_URL);
if ($url === true) {
    // valid URL, not local file - execute!
    $mySiteContents = file_get_contents($url);
    echo htmlspecialchars($mySiteContents);
}
?>
`

#### In Summary

This is just one example of the many different attack patterns that the [CAPEC](http://capec.mitre.org/)
have already defined. You'd definitely be doing better if you headed over there and checked
out the full listing. There's also a large section dedicated to Social Engineering attacks - 
everything from research gathering methods to target influence tactics to gain access to 
personal information via human interactions.

#### Resources

- [Wikipedia : Attack Patterns](http://en.wikipedia.org/wiki/Attack_patterns)
- [Common Attack Pattern Enumeration and Classification](http://capec.mitre.org)
- [The Art of Software Security Assessment: Identifying and Preventing Software Vulnerabilities](http://www.amazon.com/Software-Security-Assessment-Vulnerabilities-ebook/dp/B004XVIWU2)
- [U.S. Homeland Security on Attack Patterns](https://buildsecurityin.us-cert.gov/bsi/articles/knowledge/attack.html)

