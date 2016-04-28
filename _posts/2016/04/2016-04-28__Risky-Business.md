---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Risky Business
tags: risk,balance,evaluation,threatmodel
summary: Proper risk evaluation can minimize the impact an attack can have on your apps.
---

Risky Business
--------------

{{ byline }}

> This article was originally published in the [November 2015 edition](https://www.phparch.com/magazine/2015-2/november/) of the php[architect] magazine.

Welcome to the first edition of a new column in the magazine devoted to helping you, the developer, write more security code and to promote general security awareness. I've been focusing on application security, specifically in the PHP world, for several years now and I want to share some of the knowledge I've gained during that time with your the reader. My goal is to provide. In the future, I'll be getting into a bit more technical matters but to start things off the right way, I want to talk about something that's easily forgotten by the development side of the house: risk.

As developers, it's easy to focus in on making things work.. We want to complete that next new feature or finishing hunting down that elusive bug and wipe it from existence. We're living in a land of code and our focus is usually on getting the work done. Sometimes these bugs come in the form of a security issue. Maybe it's something that you discover as you're developing a feature (awesome) or as something that was reported by a customer (less awesome). You grab it from the top of the stack, assign it to a sprint and get to working. You put fingers to the keys and track down the problem with hopes of wrapping it up in a decent amount of time. Sometimes, though, you need to take a step back and think about the risks that are really involved in the issue.

Risk is usually defined as the combination of a few things: exploitability, reproducibility and discoverability. The risk I'm talking about here is the overall impact that the bug or flaw has on the application, not in the actual fixing of the issue. That's a whole different kind of risk that's a topic for another day. Let me go through each of these three topics and explain them briefly so everyone's on the same page.

> There's a few models in the security community for calculating the risk of an issue (DREAD and STRIDE mainly) but those are larger topics and could warrant articles of their own.

First off, lets talk about exploitability. The exploitability of a bug is directly related to the amount of effort that's required to exploit the flaw in your application. Some common flaws, like a reflected cross-site scripting issue where the data comes directly from the URL, have a high exploitability factor. Since this would be easy to reproduce and exploit, it bumps up the risk factor by quite a bit. In contrast, say someone located a logic flaw in a certain part of your application that can only be exploited by a small subset of users with certain permissions and only with a specially crafted request. The effort required to reproduce this is pretty high so it doesn't contribute much to the overall risk of the issue. A good rule of thumb here is that the higher the number of criteria or steps needed to reproduce the issue, the less it contributes to the risk.

Next is is reproducibility. This goes hand in hand with the idea of exploitability but is more about how many times the attack can be performed realistically. With exploitability you're just worried about the vulnerability existing and what kind of impact it could have on your system. Reproducibility is more concerned with what the attacker can do with the vulnerability and if they can successfully abuse it on a wide range of your users. If the attacker can only perform the exploit once in certain circumstances, the contribution to the overall risk is pretty low. However if the issue is something that could effect all users in your system, it's a pretty major problem.

Finally let's talk about discoverability. With the other two criteria in this list I've talked about the risks related to how hard the exploit is and how often it could potentially be performed. This piece of the puzzle relates more to how hard it would be for the attacker to even discover the issue. With exploitability I mentioned a scenario where the problem might be in a random, seldom used part of the application and only available to certain users. This has a very low discoverability factor as it would be pretty hard for an attacker to follow those exact steps and stumble across the vulnerability in the first place. Some issues, like the reflected cross-site scripting, give immediate feedback and are easy to discover. These highly discoverable vulnerabilities are usually the ones that will show up in static and dynamic scanners and, fortunately, are the easy ones to fix.

So, why all this talk about risk and how does it relate back to the development world? Security is a complex and wide-reaching topic. Just as there's always a long list of non-security related issues to fix in our applications, there's a list at least as long of vulnerabilities that need fixing, whether you know it or not. With non-security bugs you look at them with slightly different criteria. You think about how bad of an issue they are in relation to what they're preventing your users from doing or what kind of complaints you may get from them if something breaks. With security-related issues, we're just redefining those criteria. They should still be considered with the usual severity scale but with the three additional considerations I've covered you can more accurately judge which issues need to be fixed when.

The overall risk level provides more guidance on what needs to be fixed first, how quickly it needs to be released and how big of a potential impact it might have on your customers. The higher the risk the more important it is to get the work done and make the push for release. Fortunately we as developers are in the unique situation to help define all three of these criteria. Those that live outside the code base may look at a flaw that could allow an attacker to randomly delete records in your database as a high priority issue that needs immediate fixing. You, however, know the code and know that that issue would be quite difficult to reproduce and could only be exploited once per user in the system. While the risk on this is relatively high, in the overall scheme of things, it may not be as important as something that could have an impact on every customer across the entire system.
