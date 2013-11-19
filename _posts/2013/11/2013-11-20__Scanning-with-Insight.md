---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Scanning with (Sensio) Insight
tags: static,scan,insight,sensio
summary:
---

Scanning with (Sensio) Insight
--------------

{{ byline }}

If you're been around the PHP community for any length of time, there's no doubt you've heard of the Symfony framework. Now in it's second major revision, the framework has its share of loyal followers. The [Sensio Labs](http://sensiolabs.com) folks
have made another recent contribution to the PHP ecosystem as well by introducing a new tool that, while ultimately tailored for Symfony-based applications can also be used for just about anything written in PHP - [Insight by Sensio Labs](https://insight.sensiolabs.com/).

#### Summary

While it's still in beta (at least at the time of this article's release), you can still sign up and add projects you'd like tested. They have a [page on the site](https://insight.sensiolabs.com/what-we-analyse) that talks about the tests they perform, both Symfony and non-Symfony related, divided into four different types - Critical, Major, Minor and Informational. Here's a list of just some of them:

- Dependencies on other projects with known security issues (Critical)
- Eval() in use (Critical)
- Exceptions enabled in production (Critical)
- Dependencies cannot be installed (Critical)
- XSS Vulnerability deteceted (Critical)
- PHP files contained in a public directory (Major)
- Valid/Invalid composer.json definition (Major)
- PHP superglobals should never be used directly (Major)
- sleep() should not be used (Major)
- A GET action should not modify a resource (Major)
- Default session cookie name should be changed (Minor)
- No hard-coded absolute paths (Minor)
- Avoiding the use of deprecated PHP functions (Minor)
- Classes should be unique per PHP file (Info)
- Classes should not use magic methods (Info)

As you can see, there's a wide range of checking that's happening even on non-Symfony projects. There's lots of other checks that happen in each category for Symfony-specific things as well (like use of certain framework features and configuration checks).

![Sensio Insight Project List](/assets/img/insight-screen1.png)

#### Resources

- [Sensio Labs Inisght](https://insight.sensiolabs.com/)