---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Testing the OWASP Way - Introduction
tags: owasp,testing,guide,part1,series
summary:
---

Testing the OWASP Way - Introduction
--------------

{{ byline }}

The [Open Web Application Security Project](http://owasp.org) (or OWASP as it's more commonly referred to) recently announced the release of the latest version of their testing guide, version four. [The guide](https://www.owasp.org/images/5/52/OWASP_Testing_Guide_v4.pdf) provides a comprehensive listing of tests you can perform against your web-based applications to ensure they're hardened against attacks. The goal of the guide isn't to teach you how to fix the issues, though. The aim is to provide a helpful resource to help you "think like a bad guy" and learn the methods they might use to abuse your applications.

Before they even get into the tests themselves, though, they start off with a mini-guide to secure development and where this kind of testing belongs in the development lifecycle (hint: it's not just one place). They talk some about the overall principles of testing, why it's a good thing and some suggestions on how to run the testing practice. They also cover some of the basic terns and techniques you'll see mentioned through out the rest of the guide and some advantages/disadvantages of each.

@todo

As the guide goes through each of the sections, they break it down even further and have sections for:

- an overall summary of the test
- performing the test as if the application were a "black box" (no source access)
- doing "gray box" testing (some access to the source)

In some cases they'll also provide advice on things that can be done to fix the issues found, but they're usually more generalized so they can apply to any language out there.

They have the guide broken up into several different sections, each with several subpoints and a naming convention (much like you'd find on other vulnerability tracking, like NIST) to provide a quick reference point. Here's the general outline of those sections as well as a brief introduction to what they contain.

#### Information Gathering

When testing an application, it's always best to know what you're getting into. To this end, you should find out as much about an application as possible before trying to dig in and break its features. In the *Information Gathering* section they talk about various ways you can gather "metadata" about the application so you can detect when things change or even get more information about its internals without testing a thing.

This information includes things like any information that cached pages in search engines might expose and looking for well-known web server configurations that might provide any juicy tidbits of knowledge you could use and abuse. This is also the point where you do a high level scan of the application and try to identify all entry points to the app, what kind of framework it may be using and any execution paths you can determine.

#### Configuration & Deployment Management

In this next section they look at the configuration and resulting deployed application and any possible issues that could come from that environment. They advise the testing of the network infrastructure, the existence of any backups or older unreferenced files out to what HTTP methods the various parts of the application accept.

Here's where they also advise testing of two protocol-related topics: the use (or lack of use) of HTTPS enforcement and any cross-domain policies that might be in effect.

#### Identity Management

The Identity management section is where you start getting into some of the actual application testing. There's a few of the basic topics covered here that any good authentication/authorization system would have in place including:

- testing user roles and permissions
- testing the user registration process
- tests on the user provisioning process
- checking for guessable user account information
- places where there may be a weak or unenforced username policy

Each of these tests a different flow through the user handling processes in an application. This section just covers some of the basics around the auth* handling, really. The more in-depth and technical testing for these processes comes in the next two sections.

#### Authentication

#### Authorization

#### Session Management

#### Input Validation

#### Error Handling

#### Weak Cryptography

#### Business Logic

#### Client Side

This article is the first part in a series of posts that will walk through each of these sections, one by one, and get into more detail about what they're testing for and some possible solutions that we, as PHP developers, can use to help resolve the issue.


@todo
threat modeling
also comments about integrating security testing into your workflow
talks about reporting on security issues for visibility

### Categories

@todo



#### Resources

- [The OWASP Testing Project](https://www.owasp.org/index.php/OWASP_Testing_Project)