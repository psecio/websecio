---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Tools of the Trade: WebGoat & DVWA
summary: Learn about the WebGoat and Damn Vulnerable Web Application tools to practice your testing skills.
---

Tools of the Trade: WebGoat & DVWA
--------------

{{ byline }}

When you're just starting out and trying to figure out what in your application could
open a hole for a potential security threat, you might not know exactly what you're
looking for. You've read all the descriptions of what an [XSS](http://en.wikipedia.org/wiki/Cross-site_scripting)
vulnerability is or what kind of damage a [RFI](http://en.wikipedia.org/wiki/Remote_file_inclusion)
injection could cause, but you don't have much hands on experience as to what those
sorts of issues really look like. Thankfully, there's two tools that can introduce you
to these kinds of issues (and more) and will let you practice your skills to apply
them to your own applications - [WebGoat](https://www.owasp.org/index.php/Category:OWASP_WebGoat_Project)
(from the OWASP) and the [DVWA](http://www.dvwa.co.uk/) (Damn Vulnerable Web Application).

The idea behind both of these projects is providing you with a self-contained, follow along
sort of application that not only instructs you in how to perform some of the most basic
(and popular) vulnerabilities, but provides you with help along the way so you can
more easily spot the issues in the future.

#### WebGoat

The [WebGoat](https://www.owasp.org/index.php/Category:OWASP_WebGoat_Project) project is
a tool provided by the OWASP (Open Source Web Security Project) that walks you through
several different kinds of web vulnerabilities including:

- Cross-site scripting issues (XSS)
- Improper access control handling
- Weak session cookies
- SQL injection (blind, numeric, string)
- Web service issues

The WebGoat download is provided as a download from the [project's Google Code page](http://code.google.com/p/webgoat/)
as a stand-alone package. You download the latest release and fire up the server that's
provided with it (Tomcat - yeah, it's Java). You can then access the URL on localhost for the server:
`http://localhost/WebGoat/attack` and get to the first pages of the project. On the left-hand
side of the page, you'll find a list of different kinds of vulnerabilities that you can
try your hand at as well as a "report card" to see how you're doing. Sometimes the application
will offer you hints if you're close to locating the exploit, but most of the time you'll
really need to think about what you're trying to accomplish and find the shortest path there.

As a part of working with WebGoat, you'll be introduced to one tool that will prove invaluable
no matter what tool/training you're working with - an intercepting proxy. For their purposes,
they recommend the [WebScarab](https://www.owasp.org/index.php/Category:OWASP_WebScarab_Project)
proxy. Using this, you can hook your browser in and choose if you'd like to intercept any of
the incoming or outgoing requests back to the server. This allows you to modify the contents
of the messages being sent back and forth. Using something like this can help you do some
pretty interesting stuff, including bypassing Javascript validation and changing values
(like the contents of hidden fields) that might not otherwise be easy to modify.

With WebGoat, you use this proxy to manipulate the requests you're making and hack into
various parts of the application. You'll need to use not only the intercepting proxy to solve
some of the problems, but also other tools like Firebug/Chrome Developer Tools and some
good old fashioned educated guessing.

The WebGoat project shows you vulnerabilities like:

- Ajax security
- Authentication flaws
- Buffer overflows
- Denial of Service
- Insecure communication
- Parameter tampering
- Session management issues

Each of the lessons has a few sub-sections, so be sure you have some time to work on these
things. You can always come back later, of course.

#### DVWA (Damn Vulnerable Web Application)

Where the WebGoat application is more of a stand-along kind of thing that hasdles its
own configuration, the [DMVA](http://www.dvwa.co.uk/) is pretty similar to WebGoat in its
intentions. It also provides a contained set of examples where you can try things out,
testing your knowledge and learning at the same time.

There's one key difference between the two projects. The DVWA is actually a website that you
drop into your server (maybe as a VirualHost) and start running the tests. The DVWA covers:

- Brute Force tactics
- Command Execution
- CSRF
- SQL Injection
- Reflected XSS
- Upload issues
- File inclusion

Unlike the WebGoat software, you'll need one additional requirement - the setup of a database
backend (MySQL or the like). This is used in the SQL injection testing. All you need is to
create the database, set up a user for access to it and alter the configuration for the DVWA
to match the credentials.

Unlike WebGoat, the DVWA doesn't provide too much in the way of guidance for each of the examples.
There's usually a few links included in the page (and a "help" option) but WebGoat does a better
job of helping you along.

#### Summary

If you're new to the web application testing world and are trying to figure out exactly
how to locate the issues in your own apps, give these two tools a try. Personally, I found
WebGoat to be a better introduction to most of the important concepts, but the DVWA had a
few topics that WebGoat didn't include.


#### Resources
- [WebGoat on Google Code](http://code.google.com/p/webgoat/)
- [Damn Vulnerable Web Application](http://www.dvwa.co.uk/)
- [WebScarab](https://www.owasp.org/index.php/Category:OWASP_WebScarab_Project)

