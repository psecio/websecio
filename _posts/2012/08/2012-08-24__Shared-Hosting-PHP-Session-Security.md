---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Shared Hosting: PHP Session Security
summary: A few things to think about when using PHP sessions, especially on a shared server.
---

Shared Hosting: PHP Session Security
--------------

{{ byline }}

> **NOTE:** This tutorial does not include anything about `safe_mode` as it has been 
deprecated in PHP 5.3 and completely removed in PHP 5.4. (the most current release at 
the time of this posting). Before you select a shared hosting platform, you should inquire
about the PHP version they're using. If they're still on PHP 5.2 or lower, avoid them - 
you'll be better off in the long run.

If you're like just about every other web application developer out there, chances are 
you either cut your teeth on a shared hosting platform or have had to work with a site
that used one. There's a few advantages that come with shared hosting:

- Lower price than dedicated options
- No server admin required
- A "one click" sort of solution for site deployment

Of course, there's also several things that you give up when you choose to go the shared 
hosting route. One of the largest of these is, obviously, the security of your application.
When you pick a shared host for your app, you're one small chunk of a larger system - 
one that, depending on the server, could be hosting hundreds of other sites. You have
to trust that the system administrators know how to correctly configure their systems 
to provide you with the most secure system they can. 

There's lots of security implications that come with a shared system like this, but I'm 
just going to focus on one of them for this article - the security of the data in your 
sessions. These problems only really apply to sites that host on shared machines, though.
If you're running on your own dedicated server (or maybe even a Platform-as-a-Service 
setup), you'll still want to be careful with your data, but you won't have these issues 
specifically.

##### Shared Session Directory

By default, PHP drops all of its session files and their data into a single directory, 
like `/tmp`. Additionally, again by default, all of the session files are written by 
whatever user the web server is running as. This is file for a single user/application
kind of system but when you start having multiple applications and customers all living
on the same box and all having their session files written by the same user, the problem
starts to show up. See it yet?

Here's an example of the problem - say `User1` has their application living on the same
host as `User2`. Somehow, `User1` figures out the existence of `User2's` app on the same 
server and signs up for an account. When `User1` logs in, they'll be all set up with a
session and the access for their user type. `User2` then uploads a simple PHP script that
takes in a session ID and a value to inject and, when executed, updates his session in 
`User2's` application...possibly even giving him admin rights.

"Surely there's something to prevent this," you think. Unfortunately, there's not. Because 
of how PHP and Apache handle the session files, if your sessions are written to a shared
directory, they're readable and writeable by anyone else using that same server.

Here's an example in code:

In the file `http://domain1.com/test.php`:
`
<?php
session_start();
echo session_id()."<br/>\n";
print_r($_SESSION);
?>
`

And in the file `http://domain2.com/test.php`:
`
<?php
session_id($_GET['sid']);
session_start();
$_SESSION['testing'] = 123;
session_write_close();
?>
`

To see the problem firsthand, put these two files on different two different domains
that both live on the same server (check the IPs to see). If you hit the first page 
in your browser, you'll be given a new session and output of the session contents (in
`$_SESSION`). Now, go and hit that second page in a different browser with the URL param
of `sid=[session ID]` where the `ID` is from the first page. The code tells PHP to change 
over the session to match that ID and then push the `testing` value into it. It then 
forces a write of that out to the session. Now go refresh that first page and you should
see something very interesting - a new value in the session!

This only works when the following criteria match:

- The two domains/apps live on the same server
- The session information is in a shared directory
- The session files are all written by the same user

##### What a Sysadmin Can Do

If you're lucky enough to have some say in the system (or happen to be the sysadmin for
such a system), there's a few different things you can do to help out your users and 
protect them and their data a bit more:

1. **Change the save_path per VirtualHost:** One option is to update the PHP setting 
`session.save_path` for each of the hosts living on that server. This setting would need
to be in the web server configuration, though, so that the user couldn't mess with something
like an `.htaccess` file and change it. By setting a different path for the user's session
files, you can keep them out of the shared realm and make it someplace inaccesible by other
users.

> **NOTE:** You should *never* set this path to a location inside of your site's document
root. This could expose those files, and their contents, to the outside world...a very bad thing.

2. **Run as the user:** There are options out there (like [suPHP](http://www.suphp.org/Home.html)
or [FastCGI](http://www.fastcgi.com/)) that will allow the PHP applications to run as 
users other than the one the web server runs as. Usually, the web server user is a `nobody`
that has almost no access. By defining the user the scripts under that domain should execute
as in the VirtualHost (or similar configuration file), it also writes your session files 
as that user adding an additional layer of permissions and security to your session storage.

3. **Use an alternative storage method:** Since the filesystem can be a dangerous place for
any kind of sensitive session data to live, you might consider another option - storing them,
using a custom session handler, in another data storage solution. Popular choices include
either a database like [MySQL](http://mysql.com) or a caching tool like [Memcache](http://memcached.org/) 
or [Cassandra](http://cassandra.apache.org/). Using storage like this, you can specify login
information in the domain's configuration (as environment variables) and a 
[custom session handler](http://us3.php.net/manual/en/session.customhandler.php) to read 
and write from it. If you're a really nice sysadmin, you'd provide options to your users
when they sign up to service as to where they'd like the session information stored.

##### What a Developer Can Do

Unfortunately, if you're on a host that dosen't really care about the security of your 
session information and leaves the `save_path` as the default, you're going to be forced
to take matters into your own hands.

If you have access to a **database** (and don't mind a little extra latency) you'd be far better 
off storing your session information there than in the shared path. Obviously you'd want to be 
careful with the credentials for this method - any would-be attacker that has them could 
connect to the database from their app and modify the contents if they know the username/password
to get at it.

Another possible tactic to preventing the theft of data is to use **encryption**
on your session data. You could use something [like this](https://github.com/enygma/shieldframework/blob/master/Shield/Session.php) to automatically read and write your data in an encrypted format to the server. This
prevents the casual browsing of the session contents (since it's just [serialized](http://php.net/serialize) 
by default) and would require the user to gain access to your source to figure out how
to decrypt it.

##### Summary

Session security, as with any other security-related topics, is a complex matter and precautions
should be taken to ensure the validity and integrity of the data. This goes double if you're
using a shared host. Hopefully some of the things outlined here have given you some ideas and
food for thought about your session data and how to protect it.

Are you on shared hosting? Do you have other methods you're using to protect your session 
data? I'd love to hear about them - leave a comment!


### Resources

- [OWASP on Session Fixation](https://www.owasp.org/index.php/Session_fixation)
- [Custom session handlers in PHP](http://php.net/manual/en/session.customhandler.php)
- [Storing sessions in memcache](http://php.net/manual/en/memcached.sessions.php)


