---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Core Concepts: Trust Boundaries
tags: coreconcepts,threat,boundary
summary: Trust boundaries are the gatekeepers for data in your applications.
---

Core Concepts: Trust Boundaries
--------------

{{ byline }}

If you've been in development for any length of time, you know there's lots of concepts
that you work out on your own that end up having names. They might turn out to be a "best
practice" or a "design pattern" that someone's put a label on or they could be something 
as simple as an "everyone knows that" kind of thing. In this case, we're talking about
data. Think about it - where does the data that's being used in your application coming
from? Does most of it come from users of the system? Or does it mostly arrive on your 
doorstep like a present from another web service?

When you think about the flow of data in your app, step back from the implementation
details and the architecture of the code for just a second. Grab the nearest whiteboard
and try this exercise. Draw out the parts of your application as modules. Think about the 
data sources as independent from the code and draw lines connecting them based on
where they're used. Congratulations, you've just done a basic kind of threat modeling on
your application. "But it's just some boxes and lines on a whiteboard," you say, "How does
this hep me find the threats in my application?" Well, the short answer is that just the 
drawing won't. It's more about what it can be [used](http://websecio.localhost/2012/11/19/STRIDEing-for-Security.html)
[for](http://websecio.localhost/2013/03/20/DREADing-Your-Security.html) that's important and
one of those things is the topic of this article - assessing **trust boundaries**.

#### A Basic Definition

In a nutshell, a *trust boundary* is any place in your application that the level of trust
and reliability in the data you're using changes. Look back at the diagram you just drew 
of your application. Look at where the data is coming from and think about how it could
be tainted if you're not paying attention. Follow this line of thought out to the last trusted 
node in the system. The next hop after that is where the trust boundary changes.

True to its name, it's a line that's crossed where incoming (or outgoing, depending on the
application) data should be validated before being used. In your diagram, draw some dotted
lines where this kind of thing happens. Most likely you'll end up with a sort of "circle of 
trust" (or square, or triangle...the shape's not important) of systems/functionality that are trusted
and interconnected. Anything that comes inside that shape has to be validated or you'll
end up with that's called a "trust boundary violation" (there's even a CWE for it...[CWE-501](http://cwe.mitre.org/data/definitions/501.html)). Sometimes, this kind of thing can be easily mitigated with 
some simple content filtering. Other times, though, you might have to define some more complex
rules around what kind of data is coming in and how much of it you want to let through.

#### Boundaries and Violations

I've briefly mentioned the concept of "trust boundary violations" but I want to look at 
the idea in a bit more depth. At face value, most people try to visualize the concept as 
something like a single line drawn in the sand, just waiting to be crossed. There's a lot
more to it than just this simplified view, though. Think about the problems that might come 
with the handling of different parts of your application:

- secured objects that need to always remain secure
- secured objects that only need to be secure sometimes (or only for certain people)
- objects that are always insecure (don't have any protection)
- objects created as a part of the execution process

I think the first three on the list are pretty easy to get your mind around. They answer more
of a "yes/no" kind of question, letting the system know what level of protection to assign.
The last one is a bit tougher. When you think about protecting parts of your app and about 
the data that lies within, do you think about the things made during its execution? What about
the custom session handling you've written to use something like Redis or Memcache to store the
data. Consider what kind of implications there might be if the server used to store that data is
either in an untrusted part of the network or might be at risk because of poor maintenance and
package upkeep. You'll definitely want to pass this kind of data through something a bit more 
rigid rather than just trusting the data outright.

> There's a few things that should always throw up red flags when it comes to the level of trust
> you afford to something. If there's ever any question that you can't immediately
> answer about your architecture, you need to worry more about the boundary between that and the 
> rest of the application (and it's data source).

#### Data Flow Diagrams

Remember that little diagram we came up with earlier in this article? Well, that was a basic
version of something called a *Data Flow Diagram* (DFD). This kind of diagram is used, as expected
from the name, to follow the flow of various pieces of data within your application. It seems
like a pretty simple thing to put together, but when you start thinking about all of the data 
sources involved (user input, database information, cached data) you might find yourself casting
a pretty wide net to encompass it all. 

Let me provide you with some recommendations to think about as you're creating this kind of diagram:

- For existing applications, don't try to take it all in at once. Start with a subsystem and work 
  out. Once you get enough of these subsystem flows worked out, plug them all together into one larger,
  more complete image.

- Try and stick with some of the common [UML](http://en.wikipedia.org/wiki/Unified_Modeling_Language) 
  conventions in your diagrams rather than trying to make your own. UML's pretty simple to learn
  and sticking wth it can make sure others can easily read the diagrams and understand them without
  having to learn a new "language."

- Since UML doesn't have some of the data flow concepts built into it there's a few enhancements
  that can help make things clearer. These recommendations ([from Microsoft](http://msdn.microsoft.com/en-us/magazine/cc163519.aspx)) are: 


  | Item | Symbol |
  |----------------|-------------------------------|
  | Data flow      | One way arrow                 |
  | Data store     | Two parallel horizontal lines |
  | Process        | Circle                        |
  | Multi-process  | Two concentric circles        |
  | Interactions   | Rectangle                     |
  | Trust boundary | Dotted line                   |

One quick note - there's some compromise here, as UML usually states that data stores are rectangles so it's up to you
how you want to notate them.

So, what does a data flow diagram look like? You don't have to have any fancy software to create them at
first. It could just be a "back of the napkin" sketch until things get worked out. Then you'll probably
want to move it into something that can be shared electronically, maybe via Glify or another modeling tool.

Consider some of these examples:

[![Simple DFD Sketch](http://chemistrylearning.com/wp-content/uploads/2009/04/image0063.jpg)](http://chemistrylearning.com/wp-content/uploads/2009/04/image0063.jpg)
[![A DFD for a game](http://www.perceptek.com.au/kteam/docs/images/level1_dfd.gif)](http://www.perceptek.com.au/kteam/docs/images/level1_dfd.gif)

(All images copyright their owners)

You can see the flows of the data through their systems as well as what kind of data it is. Some diagrams
will just show the relationships between the objects, but this isn't enough for a true DFD. You need to
represent what the data is and, in some cases, show the multiple kinds of data.

In this final image, you can see how they've integrated trust boundaries into the diagram as well:

[![DFD with Trust Boundaries](http://2we26u4fam7n16rz3a44uhbe1bq2.wpengine.netdna-cdn.com/wp-content/uploads/080411_1935_Application8.jpg)](http://2we26u4fam7n16rz3a44uhbe1bq2.wpengine.netdna-cdn.com/wp-content/uploads/080411_1935_Application8.jpg)

#### Summary

When you think about the structure of your application, even in the planning stages, think about the
data flowing through it and the level of trust you have for that data. Be sure you follow some of
the [best](/2012/09/14/Dirty-Data-Protect-App-Users.html) [practices](/2013/04/01/Effective-Validation-with-Respect.html)
when it comes to validating and filtering the data you're using. Map out the flow of the data and you'll
not only get a clearer picture of the spots you need to pay attention to but might spot some issues and 
head them off at the pass before they get too ingrained.


#### Resources:

- [CWE-501](http://cwe.mitre.org/data/definitions/501.html)
- [Modeling Trust Boundaries created by securable objects](https://www.usenix.org/legacy/event/woot08/tech/full_papers/miller/miller.pdf)
- [Uncover Security Design Flaws Using The STRIDE Approach](http://msdn.microsoft.com/en-us/magazine/cc163519.aspx)

