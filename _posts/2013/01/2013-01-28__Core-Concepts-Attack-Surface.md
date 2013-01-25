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

Only talk about the "software" surface here, not the network or human aspects

The attack surface of a software environment is the code within a computer system that can be run by unauthorized users. This includes, but is not limited to: user input fields, protocols, interfaces, and services.
- wikipedia

Attack Surface Analysis is about mapping out what parts of a system need to be reviewed and tested for security vulnerabilities. The point of Attack Surface Analysis is to understand the risk areas in an application, to make developers and security specialists aware of what parts of the application are open to attack, to find ways of minimizing this, and to notice when and how the Attack Surface changes and what this means from a risk perspective.
- OWASP cheat sheet

CLASP (Comprehensive, Lightweight Application Security Process) provides a well-organized and structured approach for moving security concerns into the early stages of the software development lifecycle, whenever possible.
- OWASP 
(defining attack surface is one step)

#### Aspects of The Surface

- Entry points (API)
- Entry points (Web)
- Open ports (80, 443, 8080, etc)
- Software changes
    -> authentication/authorization
    -> change in access default - fail positive vs fail negative
    -> new features
    -> access control logic
- Environment changes
    -> hardware
    -> database systems
    -> updated language versions (like PHP)

#### Minimizing The Surface

- evaluate the proposed features to find possible abuse areas
- evaluate it in the context of the application
- research alternatives to the feature (ex. a different UI to make less of a need for searching)

#### How to help

- create a standard security policy
    -> input handling
    -> output filtering
    -> fail positive/negative for permissioning

- create effective logging and monitoring

#### Resources

- [OWASP Attack Surface Analysis Cheat Sheet](https://www.owasp.org/index.php/Attack_Surface_Analysis_Cheat_Sheet)
- [Attack Surface Metric](http://www.cs.cmu.edu/~pratyus/tse10.pdf)
- [Measuring the Attack Surface of Enterprise Software](http://www.cs.cmu.edu/~wing/publications/ManadhataKarabulutWing08.pdf)
- [OWASP CLASP Project](https://www.owasp.org/index.php/Category:OWASP_CLASP_Project)


