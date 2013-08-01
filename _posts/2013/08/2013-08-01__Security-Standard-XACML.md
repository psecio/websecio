---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Security Standards: XACML - Extensible Access Control Markup Language
tags: standards,xacml,accesscontrol,markup
summary: The XACML standard from OASIS provides an attribute-based authentication structure.
---

Security Standards: XACML - Extensible Access Control Markup Language
--------------

{{ byline }}

Like any other community out on the web today, the security community has no shortage of acronyms
to toss around about their technology of choice: PCI DSS, SAML, NIST, XACML...just to name a few. These
standards have also helped shape products and platforms to help keep applications and their users (and
those users' data) safe from prying eyes.

This particular article is talking about just one of the technologies in this jumble of alphabet soup - *XACML*.
XACML, or the Extensible Access Control Markup Language standard, helps to define XML-based structures
that can be evaluated - language agnostic - to check if a given users (the standard calls them *Subjects*)
can access a given *Resource*. At its most basic level, XACML is a set of attributes and matches that are 
compared when the *Resource* is requested and a verdict of **PERMIT** or **DENY** is passed down from
the *Decider* and *Enforcer* dynamic duo.

The goal of the XACML standard is to provide a common language that applications, regardless of the language
their code is written in, can communicate about the allowed actions on a resource. While not the simplest 
thing in the world, it does provide a good and verbose structure for abstracting out the *Policies* to restrict
access. It's attribute-based, removing any notion of normal ACL (Access Control List) structures or custom 
permissioning that the calling application might have to know about. Instead it abstracts it out to match
the attributes and their values from the policies against ones in the *Request*, *Subject*, *Action* and 
*Environment* to see if they're allowed. XACML is all about authorization not authentication, so don't look 
to it to help you validate a user is who they say they are. It only knows what you give it, so you'd need to 
have the attributes for the *Subject* before even making the request.

#### Some terminology to start...

So, by now you've noticed several terms that have been emphasized - these are all keywords in the 
[OASIS XACML standard](http://docs.oasis-open.org/xacml/3.0/xacml-3.0-core-spec-os-en.pdf) that help shape
this common language. I'm not going to get into the same depth they do in the spec, but here's some of 
the most common terms and their meanings:

- **Resource**: The thing being accessed, be it a URL or an actual document. XACML is flexible enough to 
	cover both. Resources can have a set of attributes attached to them to help with the matching.
- **Policy**: The policies are the heart of the XACML structure. They provide the rules that govern the 
	permit or denial of the request for the Resource
- **Enforcer**: The Enforcer is the "point of contact" for the incoming Requests. When a resource is 
	requested, the Enforcer should be consulted with the appropriate information to check the "allowed"
	status. In XACML-terms, this is the Policy Enforcement Point (PEP).
- **Decider**: When the Enforcer catches the request, it passes off everything it knows to the Decider. 
	This chunk of functionality does the actual evaluations based on the attributes and policy matchers
	its been given. In XACML-terms, this is the Policy Decision Point (PDP).
- **Subject**: This is the user (or other service) that's making the request for the Resource. When a request
	comes in, the Subject must be passed to the Decider. Subjects have a set of attributes linked to them
	as well. This could include any kind of data about the user. For obvious reasons, though, no confidential
	information should become an attribute.
- **Environment**: The environment is the context around the request. This could contain anything from the hostname
	requested to the IP matching the request. Basically it's any additional information that could help the
	Decider come to its conclusion.
- **Function**: An evaluation type to perform on the contents of the attributes to see if there's a match. 
	Examples of this are things like "string equal", "any URI equal", "integer add" and "string normalize to 
	lower case."
- **Rule-combining algorithm**: This tells the processing how to handle the results from the matches. For 
	example, if your algorithm was defined as "Deny overrides", if you hit any DENY results in the matching
	the whole *Rule* would fail. You can set these on *Rules* and *Policies*.
- **Obligations/Advice**: You can think of these as post-evaluation hooks. They're things that should happen 
	after the PERMIT/DENY evaluation is complete. What's the difference between the two? Obligations *must*
	be carried out where as Advice can be ignored if you choose.
- **Effect**: There's only two valid values for the effect - PERMIT and DENY (simple, right?)

These are just a few of the many terms that the OASIS specification uses, so I'd encourage you to
[give it a look](http://docs.oasis-open.org/xacml/3.0/xacml-3.0-core-spec-os-en.pdf) and investigate them all
to help it all make sense.

#### The Flow

Now that we've covered some of the basic concepts and terms behind this process, let's look at the actual flow 
of information and how things are evaluated. Stick with me on this - it's not the simplest thing to follow, but 
I'll try to make it as clear as possible.

1. The user/remote application makes a *Request* to the source with a defined XML format including what's trying 
    to be accessed and the action they're trying to use (like "read" or "write").
2. This *Request* is then intercepted by the *Enforcer* (PEP) and passed off to a context handler and the *Decider*
    (PDP).
3. The *Decider* then gathers together all of the *Policies* that apply to the *Resource* being requested.
4. It also translates any attributes into their values to compare against the *Policies*
5. The *Decider* then goes through each of the *Policies* and compares the *Rules* and *Matches* inside them
    against the data given in the request and the *Subject* that was making the request.
6. When/if matches are found, they're evaluated with the *Combining Algorithms* and *Functions* to reach a verdict
7. If there's more than one *Policy* for the *Resource*, each of them are combined as a *Policy Set* and 
    the results are, again, combined using a *Combining Algorithm*.
8. The results of these evaluations are passed back up the chain, through the *Decider* and a *Response* is
    created containing the PERMIT/DENY decision.

This is a pretty basic evaluation flow. There's lots of other things that could go into it, but it's just easier 
to start simple and work your way up.

#### An example in PHP

As a part of my research on the subject, I worked through creating a PHP-based library to help with the Policy
creation and evaluation. I've [posted it over on Github](https://github.com/enygma/xacml-php) for you to check
out if you're interested.

I've pulled out the example from the README to show the usage of the library and dropped it in here. I've broken it 
down in the inline comments to help explain things a bit. This example assumes that you've installed it 

`
<?php
require_once 'vendor/autoload.php';

// Create our Enforcer and Decider to handle the evaluation
$enforcer = new \Oasisphp\Enforcer();

$decider = new \Oasisphp\Decider();
$enforcer->setDecider($decider);


//------- Start creating our Policies
/**
 * Create some Matches for "property1"
 * 	one for the string "test" and another for string "test1234"
 */
$match1 = new \Oasisphp\Match();
$match1->setOperation('StringEqual')
    ->setDesignator('property1')
    ->setValue('test')->setId('TestMatch1');

$match2 = new \Oasisphp\Match();
$match2->setOperation('StringEqual')
    ->setDesignator('property1')
    ->setValue('test1234')->setId('TestMatch2');

// Create a Target container for our Matches
$target = new \Oasisphp\Target();
$target->addMatches(array($match1, $match2));

// Make a new Rule and add the Target to it
$rule1 = new \Oasisphp\Rule();
$rule1->setTarget($target)
    ->setId('TestRule')
    ->setEffect('Permit')
    ->setDescription(
        'Test to see if there is an attribute on the subject'
        .'that exactly matches the word "test" or "test1234"'
    )
    ->setAlgorithm(new \Oasisphp\Algorithm\DenyOverrides());

/**
 * Make two new policies and add the Rule to it (with our Matches)
 * 	Both of the policies have the same Rule attached
 */
$policy1 = new \Oasisphp\Policy();
$policy1->setAlgorithm(new \Oasisphp\Algorithm\AllowOverrides())
    ->setId('Policy1')
    ->addRule($rule1);

$policy2 = new \Oasisphp\Policy();
$policy2->setAlgorithm(new \Oasisphp\Algorithm\DenyOverrides())
    ->setId('Policy2')
    ->addRule($rule1);

//------- End creating our Policies    


/**
 * Create the Subject with its own Attribute
 * 	(the one we're wanting to match)
 */
$subject = new \Oasisphp\Subject();
$subject->addAttribute(
    new \Oasisphp\Attribute('property1', 'test')
);

/**
 * Make the Resource and link the policies to it
 */
$resource = new \Oasisphp\Resource();
$resource
    ->addPolicy($policy1)
    ->addPolicy($policy2);

/**
 * Finally, we evaluate the subject and resource with the
 *   Enforcer (PEP) to see if they have access
 */
$environment = null;
$action = null;

$result = $enforcer->isAuthorized($subject, $resource);

/**
 * So, because the Subject has the "property1" Attribute and it's
 * set to "test", the $result is true
 */
echo "\n\n".' END RESULT: '.var_export($result, true);
echo "\n\n";
?>
`

One thing you'll note is that in the example above there's no concept of *Action* or *Environment* 
like there is in the spec. This was just to keep things simple. As a base-level sort of evaluation,
only the *Subject* and *Resource* are really needed. The *Action* is probably next in importance
with the *Environment* being last (given that it's mostly just a set of extra values to give the *Decider*).

#### Is it worth it?

While the goal behind XACML is a good one, like many XML-based standards it might be a bit too complex
for its own good. It's almost one of those "too flexible" kind of situations and can easily cause confusion
if you're not paying attention.

Should you implement it in your systems? Like many topics in technology (and security specifically) it 
gets a resounding "it depends" kind of answer. There's other technologies out there (like OAuth) that can
achieve some of the same goals with a bit less complexity and some other advantages. Still, if you're 
in a situation where a company or group wants to integrate with you on a more general level, XACML 
may be worth a look.


#### Resources

- [XACML on Wikipedia](http://en.wikipedia.org/wiki/XACML)
- [Official XACML page on OASIS site](https://www.oasis-open.org/committees/tc_home.php?wg_abbrev=xacml)
- [XACML is Dead](http://blogs.forrester.com/andras_cser/13-05-07-xacml_is_dead)
- [XACML and Role Based Access Control](http://www.cs.odu.edu/~mukka/cs472f08/lectures/E-commerce/lectures/xacml.pdf)

