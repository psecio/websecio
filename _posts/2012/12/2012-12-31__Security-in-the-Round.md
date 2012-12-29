---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Security in the Round
tags: security,theaterintheround,policy
summary: Keeping the "bigger picture" in mind when assessing the security of your application is vital.
---

Security in the Round
--------------

{{ byline }}

> Note: This article was originally published as a part of the [Web Advent](http://webadvent.org) for 2012 [here](http://webadvent.org/2012/security-in-the-round-by-chris-cornutt).

As a developer, I know it’s easy to get tunnel vision when it comes to security. You look through the lines of code in your app and try to think like an attacker. You try to break things, perform injection attacks, and escape your output appropriately, but you’re missing something. Stick with me, and you’ll see what I mean.

There has always been theater of one sort or another. As long as there’ve been stories to tell, there’ve been people sitting in a group enjoying them. Theater troupes were formed to bring a more professional kind of presentation to crowds in the area, traveling around and sharing their stories with the masses. If you have attended any kind of play in recent times, though, you know that the audience is always in the front, facing the stage and enjoying a one-sided look into the lives of the characters on stage. They laugh, cry, and suspend their disbelief for a while, losing themselves in the story unfolding on stage.

It wasn’t always like this. Sure, you can do some great things when the audience can’t see certain parts of the stage — amazing sets, quick costume changes, and special effects that wow audiences. There’s another kind of theater that wows the crowd in a completely different way, though. The [theater in the round](http://en.wikipedia.org/wiki/Theatre_in_the_round) format, where the audience surrounds the actors and sees them from all angles, brings a certain openness and engagement to the experience. Audience members are no longer just spectators; they feel involved in the play. The actors have to work harder to bring audience members into their world — no elaborate sets, no hidden tools for special effects, just actors doing what they do best.

The key to the theater in the round format is that the audience can see all sides at once. Nothing is hidden, and the actors have to try even harder to make things work.

On the security stage, we’re the actors, and it’s essential that no matter how we go about implementing the security of our apps, we consider more than just the code.

A “security in the round” view, much like the audience’s perspective at a performance, allows for visibility into all parts of the environment. Depending on the company (and the environment), this can either be as easy as opening a document and checking out a schema, or it can be as difficult as herding cats into getting their knowledge out of their heads and down on paper. Fortunately, most development groups have a pretty good overall understanding of where their app lives, what kinds of systems they connect to, and what technologies they use.

“But, I’m just a developer,” you exclaim! “Other than making sure there are no exploits in my code, what else can I do?” This is a *dangerous* mindset to get into and can lead to some big issues down the line. Much like the actors in the theater in the round, you can’t ignore the other parts of your environment. You have to take the whole app into account, making sure to view it from the perspective of everyone in the audience. Without a good overall view of the app, you can’t possibly create an effective security plan to keep your users and their data safe (sometimes even from themselves).

A holistic view of your app’s environment lets you not only look at the possible issues that could come up as a result of some of the most common exploits (like cross-site scripting and SQL injection), but also lets you assess other threats like data corruption or code injected from other malicious sources.

Here are a few questions you can use to map out this overview:

- What kind of storage does my app use? Where is it?
- Are there any firewall or network restrictions in place?
- Do I have anything extra installed on my server(s) that I don’t need?
- Is my deployment method secure? Could bad code get into the live site without me knowing?
- Is my data protected if my app has an exploit that allows access?

These five are just a start to the long list of questions you might need to answer. (Be sure to check the list twice.) The answers can be very revealing, especially if you’re dealing with a dreaded legacy app.

So, get your head out of the code and take a look around at the entire environment surrounding it. Take out a piece of paper (or something [more high tech](http://en.wikipedia.org/wiki/Comparison_of_network_diagram_software) if you prefer), and diagram how things are set up; figure out what points will connect to your app. This gives you a much better picture of what parts you’ll need to protect and how you can best keep your users safe as they use your service. Keep this diagram close are you progress through the life of the app. Map out the new sections and features similarly, and figure out where their connections are and what risks might be associated with them.

With this in hand you can then [create a security plan](http://www.binomial.com/security_plan/why_you_need.php) tailored to your app, helping you and your users have even more silent nights knowing your data is safe.
