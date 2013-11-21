---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Access Control: A Primer
tags: access,control,coreconcepts
summary: Different kinds of acccess control
---

Access Control: A Primer
--------------

#### Authentication & Authorization

Access control can, broadly, be defined by two major terms in the identity management circles - authentication and authorization. Some people look at those two terms and understand the difference 100%. Others may think of them as the same thing. The truth is, they're very similar but not exactly the same - there's some subtle differences that help define their roles in the access control process. Authentication is the first step in the procees, requiring the user to - through different mechanisms - prove that they are who they suggest. Authorization is the "second piece" to this puzzle. It looks at the user (who at this point has already proved who they are) and tries to figure out what they should be able to do.

Loosely, both of these concepts could fit into the idea of "access control systems" - they both have to do with access, one requiring the burden of identity and the other allowed actions. For the purpose of this article, though, I'm going to focus on the second meaning of "access", the authroization an applicaion has to perform to ensure a user is only doing what they've been allowed.

#### Positive vs Negative Authorization

A common pattern that comes up when dealing with authorization is the "allow/deny" handling of the requests. This positive/negative evaluation usually happens when a subject makes a request to a resource and could happen in several places:

- When the resource is accessed, at the routing level
- When the user reaches the resource but has to go through "gates"
- At the data level when a user requests a record (and possbily related information)

You can see how just geting a simple "yes" or "no" answer when there's multiple levels like this can be difficult. The key when working in layers like this (defense in depth anyone?) is to keep the idea of [failing securely](/2012/10/22/Fail-Fast-Securely.html) in mind. This concept, applied simply in this case, bascially says that if something goes wrong or an auth check fails - any auth check at all - fail to the most secure option. Obviously this depends entirely on the situation as to what that means. For the resource access, it might mean a HTTP status of 403 (or 405 depending). For the data access it could mean only returing a "public" set of data without any kind of extra or nested information. Things get really tough when you have things like nested data sets and the user may or may not have access to all of the records in that data set. If it's handled programatically, it's not as big of a hassle but with larger datasets the processing overhead could get pretty significant.

Remember, failing securely (and "failing fast") means you should always deny by default, then allow as necessary.

#### Access Control Overview

Now that we've talked some about the basics of authorization, I wanted to break things up a bit more. I'm going to outline some of the major techniques to handle authorization in your application. They're going to range from the super-basic all the way up to more complex systems that may (and usually do) require external systems or software to get working correctly.

Like any technology, there's trade offs for each - usually in the amount of effort to implement, using it in the application and the strength of the protection it provides. Things on the low end of the spectrum may be trivial to implement (especially with 3rd party libraries) but might not be as flexible or as easy to maintain down the road as some of the more robust options.

So, let's get started in on our list with the most basic first...

##### Unique Identifier

If you're creating a shiny new application and you want to protect certain resources in it, what's the first thing most people gravitate towards? A username and password, of course. They use the tried and true combination of a unique identifier and a secret phrase/word to ensure only the right people are using these special parts of the application. 

> I won't get into all of the concerns around just using usernames and passwords here, but let's just say that these days you better know what you're doing to keep your app safe. Lots of people get this stuff wrong.

When either the application or the developer is in their infancy, it's easy for the idea to evaluate the unique identifier - in this case a username - as a valid option for checking access. Once the user has passed the authentication stage and we know who they are, the application then just checks against a single piece of information and compares it to a list of known good folks. 

Super simple, right? Just add someone to the list and they have access. Unfortunately, this method is *severely* limiting. All you're basically gaining out of this is a "yes" or "no" for something hard-coded into your app. Think about how the check might happen and how it could make your life as a developer pretty tough:

Say a user accesses an endpoint on your API - this single-check system would look at the list and give a yes or no answer based on its findings. Notice anything missing? If you're only validating off of a single list of usernames, you're only really able to check access for one thing. Once you start wanting to run the check for other resources, you have to add that concept into the validation resource. Now you have the concept of a many-to-many relationship in the data and you have to update the schema and checking method accordingly.

You can see how things could get out of hand quickly if you're not careful. So, what's the next logical step in the authorization process to make this a bit more functional? Let's flip things around and worry about the resource first with the idea of access control lists.

##### Access Control Lists

In the previous section, we focused on an identifier related to the subject accessing the resource to see if they were a part of the "special people" list. It doesn't implement any kind of properties around the resource or the user accessing it. All you can ever get back from it is a "yes, they're on the list" or "no, tell that deadbeat to get lost" kind of answer. The idea behind access control lists (ACL) is that you put a bit more context around the resource itself by adding in another abstract concept - permissions.

In the unique identifier example, we essentially had a system with only one permission. It validated that the user had the "on the list" permission even though it wasn't a concept that existed in the system. Now, lets look at how an access control list system would help here.

The basic idea behind an ACL is two-fold:

1) The resource has a set (one or many) permissions attached to it
2) The user has a set (one or many) permissions attached to them
3) When a user accesses a resource, these two lists are compared for matches

More often than not, the system will be looking for a positive match. It goes looking for one or all of the permissions attached to the requested resource and, if found, returns an "allow" back to the questioning functionality. Having checks like this let you abstract things out a bit more. It allows you to relate permissions to different types of things - functional pieces, specific data or even different levels of access to the same systems.

Permissions also have an added benefit that single identifiers just can't handle well - complex logic. With a single identifier, you're stuck with the simple yes/no but with permissions you can branch out a bit more. For example:

- Check to see if the user set contains a permission
- Check the set for multiple permissions
- Check to see if they *don't* have certain permissions
- Check to see if they *don't* have any of multiple permissions

ACLs aren't specific to web applications either - there's lots of systems that make use of the structure. 

There are, as wth any system, problems with using ACLs. Not the least of these is the management aspects. Think about the number of users your application has (or could have) and think about having to manage the permission sets for all of them. Even if they're hidden behind the best UX-ed interface in the world, there's only so much you can do to help make their management simpler.

Another common ACL pitfall is the opposite side of that same coin. For as much of a problem as it is keeping up with what users have which permissions, it's also difficult to track where in the application which permissions are being checked. This is true on both the code and conceptual level. Imagine you had several places in your application that checked for an "superduperadmin" permission on a user to see if they had access to delete other users. Now say your business rules change and you need to add another permission check to those same places. Now think about what would happen if you missed one and didn't update the check. You've left a gap in your security model where someone *could* access something they shouldn't.

Even worse, what if the user has a set of permissions they need to be able to do one thing in the application but, as a side effect, those same permissions let them do something they shouldn't. This is commonly refered to as the [Confused Deputy Problem](http://c2.com/cgi/wiki?ConfusedDeputyProblem) and can be a very large concern for any application. It's also nortiously difficult to find too - you have to have a complete audit of where permission checks are applied and which the user currently has to make any kind of decisions.

So, if maintaining individual permissions on users and the resources they're trying to access isn't the answer, what's the next level we can try? With the introduction of *roles* (or groupings, really) we can simplify some of the permission management problems.

##### Role Based Access Control

The basic concept of role based access control (RBAC) is pretty much stated in its name. By now I think you're familiar enough with the concepts of access control that you could probably work out how roles/groups are the next logical step in the process, but I want to be sure you understand some of the small things some people don't think of when it comes to implementing roles and their permissions.

Lets start with the basic goal behind the RBAC structure - making permission managemnt easier either by: 

- grouping permissions into sets and assigning those sets to users
- grouping users and assigning that grouping to a permission
- both

It's easy to look at that definition and think that "role" and "group" are interchangeable. For a lot of the impmenetations out there, they probably will be. Most often, the "roles" that people think about in their applications relate directly to think like departments in their company. They think that "an HR employee" is a role so they set that up and apply permissions to it. Human Resources employees are then assigned this role and everything's happy. Unfortunately, there's two things that keep this from being a perfectly peaceful existence.

First, you need to remember that "roles" are *not* "groups". Yes, I know what I've said, but stick with me here. When you're thinking about the roles your application should contain, don't think about organizational groups within your company. IT's an easy trap but it can lead to larger problems down the line. The key here is to think about the actions the users will be performing inside the system and not see them as the people behind the keyboards. For example, don't think "HR Representative" think "Personal Data Manager" that would be able to work with the personal data of other employees. By focusing the groupings on the functioanlity instead of on the pre-existing organizational structure, you'll save headaches down the line with permission interactions.

Speaking of interactions, RBAC comes with another interesting dilema similar to something we saw earlier with the basic access control lists - permission interactions. With the regular ACLs, the problem was that the user may require a permission for accessing one resource but having it means they can reach something else they shouldn't. With RBACs, the problem has more to do with the permission groupings.

The real problem is that it's hard to group permissions in sucha way that there will be as little interference as possible. For example, if both Group #1 and Group #2 have the "can access admin" permission in them but are based around two different kinds of roles, you may assign a user to Group #2 because they need a few of the permission in there and inadvertantly give them admin access too. So, how do you solve that in a RBAC world? Why, make another role with a slimmed down, more precise list of permissions...and we're back to the maintenance issues from ACLs.

While it's a sort of "trial by fire" way to handle things, if you've opted for the RBAC methods for your app, I'd highly recommend taking a step back anywhere from six months to a year from when it was implemented and figure out which groups are being used, who's in them and what the variations are. It's very probable that you'll find groups not being used or ones that just have too wide of a permission set to be useful. These can all be trimmed down or consolidated to keep things from getting too far out of hand.

So, if working directly with permissions - either in groups or individually - isn't the right answer to your needs, what's going to do it? Part of the problem with the ACL/RBAC methods is the tight coupling of the permissions, the users and the resources that are a part of the process. If you look at the permissions or group names, you can almost get a feel for what the application does (or at least the auth-protected parts of it). This is not only a maintenance problem, but could also potentially reveal things about the application you may not want an attacker to know about were they to get a hold of the permission/group information.

How can we still provide the fine-grained control that permissioning offers to us and our app without having some of the downsides that come with it? Is there an option that can take things to the next level of abstraction, hopefully making it even easier to accurately and safely describe the security model for your application. Let's move to one of those possibilities - attribute-based controls.

##### Attribute-based Controls

##### Discretionary & Mandatory Controls

{{ byline }}