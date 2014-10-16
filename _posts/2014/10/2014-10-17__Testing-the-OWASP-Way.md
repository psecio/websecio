---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Testing the OWASP Way
tags: shellshock,php,bash,vulnerability
summary:
---

Testing the OWASP Way
--------------

{{ byline }}

The [Open Web Application Security Project](http://owasp.org) (or OWASP as it's more commonly referred to) recently announced the release of the latest version of their testing guide, version four. [The guide](https://www.owasp.org/images/5/52/OWASP_Testing_Guide_v4.pdf) provides a comprehensive listing of tests you can perform against your web-based applications to ensure they're hardened against attacks.

@todo
threat modeling
also comments about integrating security testing into your workflow
talks about reporting on security issues for visibility

### Categories

They've broken the guide up into eleven different categories to make consuming it a bit easier. Here's the thousand-foot view of the topics and a brief look at what each entails:

##### Information Gathering

This kind of testing could almost be thought of more as general reconnaissance. The whole goal of this group is to gather some of the "metadata" around the application you're targeting and information you can glean from other outside sources. This includes the results found in popular search engines and the overall architecture of the application.

The idea of "fingerprinting" is brought up in this category. This is essentially what it sounds like: using various attributes of the application to create a unique value (hash or otherwise) to identify it. This fingerprint can include the content from a number of different attributes, but they suggest three to get you started: identifying the web server software, figuring out the application framework in use and any application that might be sitting on top of it.

For example, if we were to profile a simple PHP application, we might be able to determine it's running on an Apache web server based on headers returned from a simple HTTP `GET` request. Taking the next step "up" we can look at the framework that the application is being run on. In the PHP world, this might be something like [Zend Framework](http://framework.zend.com) or [Symfony](http://symfony.com). If it's a custom application running one of these frameworks, it might be difficult to determine which it is, but there can be some tell-tale signs, especially if they're using a default setup.

Since PHP has been around for a long time, there's no shortage of applications out there. Changes are, if it's not a custom application, you can figure out what kind of software is running a site just from the look and feel or even a glance at the page source. Don't forget about other data surrounding applications too...even things like cookie names can shed some light on your research.

Other information they suggest including in your discovery includes information that could be gleaned from web server configuration files (think `robots.txt`), the workflow through the application, other applications that might be living on the same server and information from cached error or exception messages a service might have stored.

@todo

##### Configuration & Deployment Management

##### Identity Management

##### Authentication

##### Authorization

##### Session Management

##### Input Validation

##### Error Handling

##### Weak Cryptography

##### Logic Failures

##### Client Side




#### Resources

- [The OWASP Testing Project](https://www.owasp.org/index.php/OWASP_Testing_Project)