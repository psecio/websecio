---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Core Concepts: Defense in Depth
---

Core Concepts: Defense in Depth
--------------

{{ byline }}

The concept of **"Defense in Depth"** is a pretty simple one - and one that anyone that's
been around the security industry is familiar with (whether they know it or not). Here's
the basic idea:

> A "Defense in Depth" approach looks at the protection of your applications (and systems)
> from a layered, integrated and cohesive approach. The "depth" is the number of layers 
> of protection you've implemented in your environment to prevent attacks from internal 
> and external sources.

That's the real key to the idea - the layers (like an onion...or parfait). In fact, there's
even a common set of these layers that relate to the overall environment an application
could live in. Starting with the innermost and working our way out:

- Data sources (databases, logs, etc)
- Applications
- the Host they live on
- your Internal Network
- the Perimeter of your environment
- the Physical hardware and systems themselves
- any Policies and Awareness you want to have about the environment

As developers, we're really only concerned with those first few layers when we're writing 
our apps, but it's good to have an overall picture of where the things you're creating 
will fit. Our job as developers is to provide the functionality that's requested (or 
just wanted in general) in the most secure way possible. This has to be done to the best
of our abilities in our code. It's a lazy developer that writes an application thinking
"I don't have to secure that because it'll be firewalled off". Remember, when you're 
thinking about the attack surface of your app (potential points an attacker could use
for exploits), you can't rely on another piece of hardware or software to be 100% secure.
After all, is your code 100% bug free? (If you just said "yes" you need to be smacked
in the head by the nearest pentester).

So, if we're just focusing in on what we can do in our applications to help secure that
innermost layer (the Data) from getting into the hands of the wrong people, what kinds
of things should we be thinking about? I'm glad you asked...

#### The Layers

One of the keys to a good "*Defense in Depth*" approach to building your applications is 
to remember that there's no one silver bullet that's going to take care of everything 
in your application. For example: say your application requires users to log in to access
it. Is that good enough? Probably not. Chances are, even if you don't have sections of 
the site that only certain people should be seeing, you will. If you're implementing 
any kind of user authentication system into your application, you'd do well to include
an authorization system as well. 

Wait, aren't those the same thing? Not really - "*authentication*" refers to the act of 
validating that a user is who they say they are while "*authorization*" is used to check
and be sure the user should even be there. By enforcing both of these kinds of checks 
(these layers) you're protecting your application even more and reducing that attack 
surface. 

This is just a simple example of the kinds of layers you might have protectng your 
system. As your environment gets more complex and more "moving parts" are added, the 
kinds of layers will grow. For example, say your site is a simple messaging service
that you offer via a basic site for reading and sending messages to other users all
over the world. It gets popular and people start asking about integration with their
applications and wanting to use your service as a platform. The next logical step is 
to offer them an API for easier scripted access...but your username/password 
authentication isn't good enough anymore - you need something different. Then, say your
API is a hit, and people want to use your software in in-house messaging. How are you
going to talk to those servers and what kind of changes will you have to make when its 
inside someone else's network. 

You can see that the complexity can grow rather quickly and can get out of control if 
you're not keeping everything in check.

#### A Secure Architecture

When you're thinking about the architecture of your application - even if it's just a 
sketch on the back of a napkin - think about the security needed in the various parts
of your application. Think about things like access rights, request methods and how 
you can implement things to secure those pieces. Whatever you plan, there's one main 
thing to keep in mind - your solution doesn't have to do it all in one shot. There's a 
lot of libraries and tools out there that promise "complete security" by handling a lot
of things for you. Remember, though, that one of the key concepts behind "Defense in Depth"
is this interlocking structure. Pieces helping pieces to create an overall more secure 
application, not one point of failure that may or may not be handling things exactly 
how you need.

If you take this modular approach to securing your app, you also have the added benefit
of an easier to maintain, more flexible system that can be changed much easier than a 
tightly integrated package. Thanks to things like [Composer](http://getcomposer.org)
for PHP (or other package managers), you can easily pull in external tools to piece 
together the system you want. 

> **NOTE:** Something to keep in mind when using 3rd party libraries - the higher the risk 
> associated with your application and what might happen in a breech, the closer you 
> need to look at the code that powers these 3rd part libs. Don't trust with blind faith 
> that the developer thought of everything and got it right the first time.

The solutions you use should be reputable, tested (unit tests, functional tests, etc)
and should be something that is well-known enough to have been through its paces and 
tested. The same kind of ideals should be held by you and your developmen team as well. 
Be sure that whatever solution you come up with has been well-tested and put through its
paces, preferably in an internal, more real world situation.

#### A Few Words of Warning

Be careful, when planning of your application, that you think about a few things so 
it doesn't get out of control:

- **Start simple, then add complexity**: Sometimes the simplest approach is the best. Figure
  out what kind of security and protection the parts of your app need and work with that.
  If there's a special stuation that comes up (like implementing a more complex authentication
  mechanism), be sure to research several solutions before picking a path. Complexity
  means specialized knowledge and if the people with that knowledge aren't available,
  you're in for some major pains.

- **Understand what you're using**: This goes hand in hand with the first point - if you're
  going to implement something in your application to help protect its users, you better 
  know what it's doing. If you implement something "just because" and haven't thought
  through all of the potential ripples it could cause, it'll come back to bite you
  one way or another.

- **Evaluate the Threat then Respond**: To effectively mitigate the risk in your application
  you have to know what you're protecting from. Sure, you're not going to know every possible
  way that someone would want to attack your app, but by starting with some of the
  [most common](OWASP Top Ten) and working your way out, you can be much more effective than
  just guessing. Threat modeling can help with this.

Don't be lulled into a false sense of security just because you added a new layer to the 
onion that claims it will solve all of your problems.


#### So, talk nerdy to me...

All this is great, but lets talk a little more practically. Developers are less about 
the shiny package and more about what the service or tool can actually do for them. They
want examples of these layers they can take into their planning sessions to see where (or if)
they apply in their application. So, here's a few things to think about and questions to 
ask as you're in your planning sessions: 

- **Authentication systems**: What kind of authentication do we want to use for our service
  and how does that relate to our users. Is a username and password enough to identify them
  or should we use something more like OAuth or other token-based system to validate the users?
  Is it worth it for us to try implementing something like Mozilla's Persona into our app
  and maybe make life simpler for us? 

- **Access limiting and resource protection**: Once the user's in, are there resources
  and data that we don't want them to see? How will we protect these resources and the 
  data of the other users of the system? Should some kind of identifying token (generated
  or static) be included along with each request? How fine grained should we get with our
  permissioning and should it be different for the frontend (view) than the backend?

- **Thresholds**: Do we need to imlement any kind of thresholds for our users? If we're 
  offering an API, do we want to throttle requests to prevent over consuption of resources
  by a single user? How do we want to handle authentication failures? If they hit a certain
  number, do we block the requestor for a certain length of time?

#### Summary

As you wok through your application, there's always going to be things that pop up that
might not fit into your current security model. Thankfully, if you've followed the "interlocking
pieces" philosophy that the "Defense in Depth" methodology promotes, it could be as simple
as turning up a different kind of tool for that situation. Just remember to keep the complexity
low and don't do a lot of "just because" implementations.

Don't get me wrong, it's a tricky thing to do to bring together multiple security measures
into one cohesive application, but if you focus on the relations between them and don't 
make them islands unto themselves, you'll be doing you and you application (and data!)
a greater service.
