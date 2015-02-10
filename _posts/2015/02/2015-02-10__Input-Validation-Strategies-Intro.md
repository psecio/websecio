---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Input Validation Strategies - Introduction
tags: input,validation,filtering,injection
summary: Validating input can help prevent most common security issues - let's learn how.
---

Effective Input Validation - Introduction
--------------

{{ byline }}

In the world of application security, especially when it comes to working with data from outside sources, there's a practical mantra to follow: **FIEO** (Filter Input, Escape Output). These four simple words sound like they'd be easy, but in actuality it turns out they're one of the most complicated problems that can face a developer. Sure, there'll always be complicated multi-layered attacks with multiple entry points or exploits stacked on top of each other, but data handling is still at the heart of it. By allowing bad data into your application you leave yourself open to several of the [major vulnerabilities](https://www.owasp.org/index.php/Top_10_2013-Top_10) for example:

- **SQL injection** by not correctly filtering the input used in SQL statements (and, technically, using prepared statements can be considered "filtering")
- **Cross-site scripting** by not escaping the output of your application and preventing the injection of arbitrary HTML or other harmful strings

These, among other issues, can be cause by poor data handling, both on input and output. While there's other guides out there that may walk you through some of the general practices or the use of [other](/2013/12/31/Input-Filtering-Validation-Aura-Filter.html) [tools](/2013/04/01/Effective-Validation-with-Respect.html) to handle the actual work, I want to get a bit more high level. I want to take a look at the big picture of input validation and filtering and provide best practices to help guide you though what can be a minefield of hidden issues.

In this first article in the series, I want to set a good foundation and get you grounded with some of the most basic concepts. From there we'll get into more specific topics relating to the types of input you'll commonly come across and effective ways to work with it.

#### No Validation

First I want to start at the place we all do - no validation. It's pretty common practice for a developer to focus on functionality first and "shoring up" the handling later. Most devs that I know will, for example, make the endpoint handling code for a REST API first and then circle back around and do input validation following that. In this example, the validation can happen (and **should**) close to the point of input.

> There's differing points of opinion as to where validation really needs to be placed. One party suggests that it should be as close to the input source as possible. The other suggests putting it as close to the data layer as possible. I've seen both work...really it depends on your application structure and needs.

All applications start out at a state of "no validation" and then have the necessary layers built on top. Ideally there's multiple validation layers built up to help prevent different kinds of issues (but we'll cover that a bit later). While adding these layers of validation is a good thing, there'll also always be a slim case for the opposite - no data validation. "Heresy," you say, "you just got done saying that everything should be validated!" This is quite true, I did but there'll always be cases where effective and correct validation just isn't in the cards.

Web application input is becoming more and more complex. As a result, the validation and filtering rules that we need to include have to match in complexity. Some things, like those with standardized formats, are easy to validate. We can trigger a warning right away if things are amiss. Different payloads, however, may need a little more handling to get the job done. Say that you application allows a user to upload a binary file for evaluation. Your application may be able to ensure that it's a file of the correct type but not always, especially if it's a custom file type. In this case, the main validation may happen outside of the application all together with the file being passed off to a third-party service for processing.

Without any real time feedback from that third-party, can you tell the user if the file is good or not? Of course not, so you're left with only messaging about the success or fail of the hand-off, not so much about the contents of the data in the file itself. More often than not there'll be some other metadata submitted along with the file (like username or some file naming format) but not always.

We as developers have to understand what is the appropriate amount of validation to put into an application and how much risk is acceptable when we make that choice. Remember, a *lot* of security is about mitigating risk, but just as there's no such thing as 100% secure there's also no way to eliminate 100% of the risk.

#### Accept Known Good

In the next step up from our world of "no validation" we're going to look at one of the easier validation practices to follow, only accepting known good values. I say that it's easier because the checks are usually simpler and can return immediately when there's a problem with the data that's submitted. You're expecting a certain kind of data, formatted as required and that's all.

> Only accepting known good values plays nicely with the concept of "Fail Fast" that's a best practice in the application security world. Essentially this says that when you reach a failure, you immediately kick back an error to the user, no questions asked. By providing this more immediate feedback, the user doesn't have to wait until it gets too deep into the validation chain and it makes your code cleaner in the process.

Here's an example - say we're expecting a U.S. zip code as a part of the user location information. We might write a validation check like this:

`
<?php

if (preg_match('/([0-9]{5})([\-0-9]{4})?/', $_POST['zip']) === false) {
	return false;
}

?>
`

In this case we're checking to see if we've been given data that's either just the five characters (like 10001) or the more extended version with the extra four characters and a dash (like 10001-1234) but making that optional. Since this zip code information is a very limited format, we only really need the one check to ensure it's valid. Then, if it doesn't pass validation, we return `false` immediately and notify the user that the zip code value has failed.

This kind of handling is all well and good when you're dealing with known formats, but what happens when you're allowing the user to do things a bit more free-form? That's when you start getting into trouble. Putting a textarea on a page and letting a user put anything they'd like in it is probably one of the more difficult things you might have to handle. In general, there's a few things I recommend when it comes to more free-form inputs:

- Limit it. Yes, I know this sounds counterintuitive but limiting the field contents maybe by length, maybe by the contents.
- Don't allow formatting at all. Even if users put in whatever content they want (maybe a bio) let them know you'll be stripping everything off when it comes out the other side. Then the burden is on the output escaping.
- Use infrequently. As much user input should be given through known and verifiable methods. Speaking generally, accepting free-form text is more of a lazy way to take the input rather than making effective use of proper form controls.

Allowing only known good data (whitelist) is a powerful approach that can make validation easier in the long run, but it can be difficult with certain kinds of data. In this next section I want to take a look at the opposite perspective, rejecting data that is determined to be bad.

**Advantages:**

- Provides more immediate response on failures
- Ensures consistent data
- Easier to understand validation rules (when taken one at a time)

**Disadvantages:**

- Hard to use on more free-form input values
- Validation rules can get complex for more complex input

#### Reject Known Bad

Where the "known good" method looks at the data to ensure it matches what we're expecting, the "known bad" method (blacklist) tries to find malicious data in the input and reject it if found. If this sounds like a lot more work to you than validating good content/failing fast there's a good reason for that - *it is*.

With the whitelist approach, you're requiring the user to fit an expected format or you're taking the content they're giving you and massaging it to what you need (sanitization). In the "known bad" approach you're taking a list of things that you know shouldn't be in the input and cycling through them all to see if any problem spots are found. This is where the real pain comes in.

There's a concept when it comes to output escaping called "context". When we talk about context, we're referring to the final resting place of where the piece of input will be shown to the user. In some cases this is immediate, reflected back out as a value when the page renders. In other cases it's a piece of data pulled from the database and manipulated into something to include in the page output. A lot of the XSS prevention out there applies to just the HTML-related contexts, but there's also other places, including dynamic CSS and Javascript, where an injection attack could happen.

Here's an example of what a system like this might look like (at a super-simple level):

`
<?php

$input = $_POST['title'];
$find = array(
	'onclick',
	'onmouseover',
	'onchange',
	'onload',
	'onmouseup'
);
foreach ($find as $string) {
	if (stristr($input, $string) !== false) {
		return false;
	}
}

?>
`

So, what does this have to do with input validation and rejecting data that's known to be tainted? I mentioned the "loop until you find something" approach above. This means that, for every piece of data you're working with, you have to know where its final resting place is and what to correctly check for. After all, it doesn't make sense to try and check a value that'll just be output as plain text for CSS injection issues. No, you'd want to refine it down and only check for things related to the placement of the data in the output. This means building up massive sets of rules and checks for "The Bad Stuff" and creating them for each of the possible contexts. Start to see where this gets to be a much bigger hassle than just a little bit of validation handling?

There's two other problems with this approach. First off, when you're these sets of rules and checks on the data you have to know where it's going to end up. This is a pretty clear violation of the idea of separation of concerns between the data handling and validation layer and whatever method the application is using to render the result. This means keeping your data validation in sync with the output of your application. It could also potentially lead to issues where one rule might in one situation and not in another.

If you've been thinking in contexts I'm betting you've picked up on what the other major problem is. Two words: "mutliple contexts". The tricky thing about trying to remove things from data that are deemed "bad" is that, in one context a particular value may be allowed but not in another. If you're running through rules in your blacklist and stripping out data for an HTML context, you might accidentally drop something that was needed in a Javascript context. This also confuses the storage of the data. If you have one string that needs to end up in multiple contexts, how to do you store it? Do you keep a version of it around for each possible context? Some would venture a guess that a plain-text string is the answer, but if you do that you potentially remove formatting and/or special characters that might come along with the data.

**Advantages:**

- Could catch other potentially harmful data that might "hide" inside of formatted data
- Not much else...

**Disadvantages:**

- More complex to implement and maintain
- Has to know too much about where the data ends up

#### Sanitize

I'm going to shift gears a little here and talk about something that's more along-side validation and not really a specific part of it. This is something that I usually advise people use pretty sparingly because it's a slippery slope once it's in place. Sanitization is the act of taking the data you've been given and guessing at what the user intended to give you. The problem is that last part - the guessing.

In handling that has more standardized formats (like phone numbers or zip codes) you have a very limited set of parameters to narrow down the input inside of. For example, say the user is instructed to put in a phone number (text field) and enter "+1-214-555-1234" instead of the 10 digit, no dashes number you were expecting. It's reasonable to guess that they added the extra characters for formatting and that those can be safely removed away from the data before other handling.

> One quick thing here - sanitization should be used **together** with validation, not as a replacement. You can sanitize the data prior to validating it, but if it fails that validation, it's usually a better option to kick back an error at that point than trying another sanitize method.

This is all well and good with data that's relatively easy to figure out. Much like some of the issues surrounding the use of blacklists to validate the contents of user input, sanitization starts losing its usefulness relatively quickly. It's tempting to write custom handling that will try multiple ways to sanitize the data, but be careful how many you try. Remember, this is essentially giving the user an "out" and allowing the restrictions on incoming data to be more lax. Anytime you start getting less strict around security handling in your application, it's a bad thing in my books.

#### Multi-Level Validation

