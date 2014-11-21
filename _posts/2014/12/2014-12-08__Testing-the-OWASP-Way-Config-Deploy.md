---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Testing the OWASP Way - Configuration & Deployment Management
tags: owasp,testing,guide,part3,series
summary:
---

Testing the OWASP Way - Configuration & Deployment Management
--------------

{{ byline }}

##### II. Configuration & Deployment Management

Next up is a quick set of recommendations around testing of the configuration and deployment of the application and the platform it lives on. Web-based applications can't exist in isolation. They have to run on some kind of server and network to even be accessible to users. This set of tests help you track down some of these other environmental issues that could lead to potential compromises in your applications.

First they recommend testing the configuration of the platform the application lives on and validate that there's not extra or default settings enabled that could leave a hole for an attacker to abuse. Most web servers will come with a default configuration defined to make the setup process a bit less painful. Unfortunately this can also be detrimental to the platform and application, especially for administrators that may not know any better. The general rule here is: "if you don't know what it does or if you need it, turn it off."

There's also another kind of discovery that can be done with a little bit of digging and knowing where to look. Developers and site administrators are creatures of habit and one unfortunate habit several have picked up over the years is to leave copies of older configurations, files not referenced anywhere and backup copies where they're web accessible. Remember, just because there's not something in your application that points to a script it means it's secure. This is the very definition of "security through obscurity" and trust me...that never ends well.

The end of this section includes a few other exploratory suggestions to help you determine the "edges" of the application. The first recommendation is to test other HTTP methods besides the ones used in the site. I've seen some PHP applications that split out the `POST` handling from the `GET` in the same controller (REST anyone?) but don't think about securing the `POST` route since nothing in the application submits to it. The unfortunate reality is that, even if the site only uses a request like `GET /foo/bar` an attacker could see this and start messing with `POST`, `PUT` or even `DELETE` and see how the application behaves.

The last two deal with checking for the the strict transport security header (`Strict-Transport-Security`) and the potential use of RIA (rich internet application) cross-domain policies. Cross-domain policies can grant or deny several different kinds of low-level handling including socket permissioning, header handling and the requirements for HTTP/HTTPS.