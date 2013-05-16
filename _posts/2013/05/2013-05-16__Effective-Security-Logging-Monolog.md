---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Effective Security Logging with Monolog
tags: logging,monolog,audit
summary: Logging is a tricky subject - what to log, when to log and what tools to use.
---

Effective Security Logging with Monolog
--------------

{{ byline }}

There's lots of things you can do to help the security of your site. There's the more
high-profile things like fixing vulnerabilities or running test to find others. There's
something that's a bit more "behind the scenes", though, that's really not given enough 
credit for helping the overall security of your site - good, effective logging.

Sure, it's not the first thing that comes to mind when people talk about the security 
of a site, but it's easily one of the most important. For the casual developer, logging
is almost an after-thought. It's something that they turn on and let their system do
in the background. They get information about what pages were hit, how often and - if 
they have some kind of analysis tool - trends on the usage of the application. Unfortunately,
this is where a lot of development groups stop. They think that this limited amount of 
data is "good enough" for their needs. That is, until they have a major issue and suddenly
have to try to figure out exactly where the problem is. See, with this kind of "lightweight
logging", you can get some of the basic information about what users are doing but there's
still a lot of questions that things like Apache logs or framework-generated log files just
can't answer.

#### Why log?

Logging is a useful tool with some real value if done correctly. When the right information
is stored it can make your life as a developer must simpler. Imagine being able to fire
up a tool, find a user's session and follow their flow through the site as a simple timeline.
Is this realistic? Maybe not, but it's a definite goal. 

It provides accountability of the actions of your users and, track the overall usage of your
site and, as a positive side effect, can also be used to gather some performance information.

When you're thinking about the logging you need to do in your system, be sure you treat it
like another requirement of the system and not a one-off "yeah, we should do that" kind of 
thing. Think about what kind of information you might need to log and any special cases 
of data that needs to be included. It's not a perfect science, so prepared to find data
you didn't even think about and needs to be added.

#### What and When to Log

One of the other big questions when it comes to logging is two fold. Everyone wants to 
be sure their logs are good, but they're not really sure what to log or when to log it.
So, here's a few suggestions I've seen of things that would be good to track:

1. User actions & administration (login, logout, profile update, permission changes)
2. Information about the request (hostname, HTTP method, URI accessed)
3. Any errors that might have come up (like when an exception is thrown)
4. Interactions with other systems/web services

There's also some optional information you *could* log, but don't really need to if you
don't need them:

1. HTTP request/response headers
2. Full request/response content
3. Performance information

Catching some of these is relatively easy in PHP but others will require logging methods
to be put into the code at certain spots. Making a static class that follows the suggested
[PSR-3 logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
is one option that's pretty easy to drop in (but more on that later).


#### What is "Effective Logging"?

One of the main tricks to logging - and one that won't have the same answer for every
application out there - is the question of how much to log. There's some people that 
take the stance of "log all the things" and then sort them out later. There's others that
are advocates of the "lean logging" approach. The tricky part is, both are right.

When you think about logging, there's a few questions you want to answer to be sure you're
collecting the right kinds and amount of information. Remember, one of the keys to having good
logs is to be able to look at a user or session at any given time and be able to recreate what
they were doing and how they did it. The best logging, regardless of the level of detail,
answers "The Five Ws":

- Who was involved?
- What happened?
- Where did it happen?
- When did it happen?
- Why did it happen?
- How did it happen?

(Yes, I know the last one starts with a "H"...) With the results from these questions, 
you should be able to figure out with some level of clarity how a user is working through
your application and what actions they took while using it. Answering these in plain 
English is one thing, but trying to figure out what data matches them on an application 
level can be a bit more tricky.


#### On to the Code!

So, I've talked some about the ideas behind good logging and some thing the things to 
think about as you integrate good logging into your system. Lets take some of this knowledge
and apply it. I've chose to go with the [Monolog](https://github.com/Seldaek/monolog)
PHP-based logging tool created by *Jordi Boggiano*. Not only does it probably provide an 
adapter for your logging method of choice, but it's also super easy to install via Composer:

`
{
    "require": {
        "monolog/monolog": "1.5.*@dev"
    }
}
`

Monolog has adapters to let you work with log files, alerts/emails and databases. I've chosen 
to go with a MongoDB storage method to make querying the results a bit easier than having to 
parse a log file just to use it. In the example below, we're setting up a new MongoDB 
connector (the handler) and a new Logger instance that uses it.

`
<?php
use Monolog\Logger;
use Monolog\Handler\MongoDBHandler;

$handler = new MongoDBHandler(
     new \Mongo('mongodb://localhost:27017'),
     'logDatabase',
     'logCollection'
);

$log = new Logger('audit');
$log->pushHandler($mongoHandler);

$log->addWarning('this is a warning');
$log->addError('this is an error');
?>
`

Monolog makes it easy to drop in logging wherever you need it. Build it into the base 
of your application and accessible via a static call and it becomes even easier.

So, we have the basics of using Monolog and pushing those messages into Mongo for later
perusal (you do have a log analysis plan, right?)...lets look at what kind of data we want
to log. Based on some of the items in my earlier lists, here's what I propose as a good 
base for the contents of your logs:

`
{
     "datetime": ISODate() in UTC
     "user": {
          "uid": "555-ccornutt",
          "sid": "fsa4324fdsbvcxbvcx7893fds"
     },
     "request": {
          "port": 80,
          "protocol": "HTTP",
          "method": "GET",
          "hostname": "www.myserver.com",
          "servername": "myserver.com"
          "uri": "http://myserver.com/user/test",
          "remote_addr": "192.168.1.1"
     },
     "event": {
          "type": "error"
          "data": {
               "msg": "The user did something stupid",
               "code": 1234,
               "type": "user",
               "severity": "low"
          }
     }
}
`

While a lot of the data that's in there is pretty self-explanatory, I'm going to work
through it just to be sure.

1. **Datetime:** It's pretty obvious that you should have some kind of timestamp in your 
    logs, but you'd be surprised how many logging implementations forget it.
2. **User information:** This kind of stuff is going to be pretty specific to your application,
    but there's a few things that are handy to have in here like a unique identifier for the user
    and a unique session ID. Having this kind of session ID lets you reconstruct the full 
    flow of what the user did and when they did it.
3. **Request information:** The information I have in here is just an example, but it gives
    you context about how the user got to your system, where they're coming from and more 
    information about what endpoint, HTTP method and host and server they used. If you have 
    load balancing on your infrastructure, this can be useful to see exactly how they moved
    through the system.
4. **Event information:** This section can be a bit more free-form. "Events" can be classified
    most generically as "anything that happens in the system." In the example I have
    the result of an error being thrown, but this could very easily contain simple information
    or log messages to help you track the data being passed back and forth. For example:

`
{
    "event": {
        "type": "info",
        "data": {
            "msg": "User 'ccornutt' updated profile",
            "data": "email=ccornutt@not-a-real-domain.com"
        }
    }
}
`

> Remember, you always want to be aware of what you're logging. Think about **sensitive data**
> that could be coming through your system and strip out things as needed.

Is this a perfect structure? No, of course not...that's the trick to effective logging. It's
completely relative to your system and the information you might want to track. This is just
my opinion on some of the basics that would make tracking a user through your system a 
simpler task.

#### Log Policies

With that technical stuff out of the way, let's look at the other side of effective security
and audit logging - the policies you need to define around it. Just having logging is good, 
but if you don't use it and review it for trends and problem spots, it's mostly pointless. 

I know the word "policies" turns off some of the developers out there, but push aside the 
restrictive context that might come with it. A "policy" doesn't have to be a massive forty 
page document that defines a complex process. It can be as simple as saying "lets have someone
check out our logs once a day" or even better "lets have an automated process review our 
logs for trends." As long as you have some kind of structure in place that says you'll look
at your logs on a consistent basis, you'll be better off in the long run.

Here's a good measure of how you're doing with your log review policy - if you look at your 
logs and find some surprises, you're not doing it often enough.

#### Resources
- [Monolog](https://github.com/Seldaek/monolog) on Github
- [PSR-3 logger interface](https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md)
- [OWASP Logging Project](https://www.owasp.org/index.php/Category:OWASP_Logging_Project)
- [Guide to Computer Security Log Management](http://csrc.nist.gov/publications/nistpubs/800-92/SP800-92.pdf)
