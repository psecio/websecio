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

##### I. Information Gathering

This kind of testing could almost be thought of more as general reconnaissance. The whole goal of this group is to gather some of the "metadata" around the application you're targeting and information you can glean from other outside sources. This includes the results found in popular search engines and the overall architecture of the application.

The idea of "fingerprinting" is brought up in this category. This is essentially what it sounds like: using various attributes of the application to create a unique value (hash or otherwise) to identify it. This fingerprint can include the content from a number of different attributes, but they suggest three to get you started: identifying the web server software, figuring out the application framework in use and any application that might be sitting on top of it.

Having these fingerprints can also help you detect when an application changes. Depending on how you create the fingerprint, you might even be able to tell when anything in the application's environment changes, even down to the host that it lives on. It also provides a unique identifier you can use to track the application in your systems and its change over time. All applications change in both subtle and not so subtle ways and tracking this can help aid your testing and make it simpler to know when tests and policies need to be revised.

For example, if we were to profile a simple PHP application, we might be able to determine it's running on an Apache web server based on headers returned from a simple HTTP `GET` request. Taking the next step "up" we can look at the framework that the application is being run on. In the PHP world, this might be something like [Zend Framework](http://framework.zend.com) or [Symfony](http://symfony.com). If it's a custom application running one of these frameworks, it might be difficult to determine which it is, but there can be some tell-tale signs, especially if they're using a default setup.

Since PHP has been around for a long time, there's no shortage of applications out there. Changes are, if it's not a custom application, you can figure out what kind of software is running a site just from the look and feel or even a glance at the page source. Don't forget about other data surrounding applications too...even things like cookie names can shed some light on your research.

Other information they suggest including in your discovery includes details that could be gleaned from web server configuration files (think `robots.txt`), the workflow through the application, other applications that might be living on the same server and information from cached error or exception messages a service might have stored.

##### II. Configuration & Deployment Management

Next up is a quick set of recommendations around testing of the configuration and deployment of the application and the platform it lives on. Web-based applications can't exist in isolation. They have to run on some kind of server and network to even be accessible to users. This set of tests help you track down some of these other environmental issues that could lead to potential compromises in your applications.

First they recommend testing the configuration of the platform the application lives on and validate that there's not extra or default settings enabled that could leave a hole for an attacker to abuse. Most web servers will come with a default configuration defined to make the setup process a bit less painful. Unfortunately this can also be detrimental to the platform and application, especially for administrators that may not know any better. The general rule here is: "if you don't know what it does or if you need it, turn it off."

There's also another kind of discovery that can be done with a little bit of digging and knowing where to look. Developers and site administrators are creatures of habit and one unfortunate habit several have picked up over the years is to leave copies of older configurations, files not referenced anywhere and backup copies where they're web accessible. Remember, just because there's not something in your application that points to a script it means it's secure. This is the very definition of "security through obscurity" and trust me...that never ends well.

The end of this section includes a few other exploratory suggestions to help you determine the "edges" of the application. The first recommendation is to test other HTTP methods besides the ones used in the site. I've seen some PHP applications that split out the `POST` handling from the `GET` in the same controller (REST anyone?) but don't think about securing the `POST` route since nothing in the application submits to it. The unfortunate reality is that, even if the site only uses a request like `GET /foo/bar` an attacker could see this and start messing with `POST`, `PUT` or even `DELETE` and see how the application behaves.

The last two deal with checking for the the strict transport security header (`Strict-Transport-Security`) and the potential use of RIA (rich internet application) cross-domain policies. Cross-domain policies can grant or deny several different kinds of low-level handling including socket permissioning, header handling and the requirements for HTTP/HTTPS.

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