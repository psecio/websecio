---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: SQLi in NoSQL - A Word of Warning
tags: sqli, sqlinjection, nosql, mongodb
summary: Just because you're using a NoSQL db doesn't mean you're safe from SQL injections.
---

SQLi in NoSQL - A Word of Warning
--------------

{{ byline }}

If you go hunting for common terms like "web application security" or "common web vulnerabilities"
on Google, it won't be long before you'll come across one of the [OWASP Top 10](https://www.owasp.org/index.php/Category:OWASP_Top_Ten_Project) that still effects a large number of web applications
out there - SQL injections, commonly known as "SQLi" for the lazy. Here's a brief definition
of what a SQL injection is (from the venerable [Wikipedia](http://en.wikipedia.org/wiki/Sql_injection)):

> SQL injection is a technique often used to attack a website. This is done by including portions
> of SQL statements in a web form entry field in an attempt to get the website to pass a newly formed
> rogue SQL command to the database (e.g., dump the database contents to the attacker). SQL injection
> is a code injection technique that exploits a security vulnerability in a website's software.

A bit more simply put - and probably more relevant to developers, here's an example in PHP
that helps to make it more concrete:

`
<?php
$sql = "SELECT from users where username = {$_GET['username']}";
$result = mysql_query($sql);
?>
`

Now, if you don't immediately see the issue with the above code, you might need to go
learn some of the more [fundamental concepts](/2012/11/12/Code-Defensively.html) behind secure web development.
The biggest problem with the above two lines is that, without doing any kind of filtering
or checking on the value, the `$_GET['username']` is being used directly. Why is this such a
bad thing, you ask? Well, because of how the information just gets appended right into
the string of the SQL statement, a would-be attacker could put anything they want in there.
They could do malicious things like dropping tables, updating all users to have the same
password or even corrupting your data.

#### That's fine with ANSI SQL but...

Now, this makes perfect sense when it comes to the usual ANSI SQL-based databases (like the
ever popular [MySQL](http://mysql.com)) but does using a NoSQL database like MongoDB protect
you from SQL injection attacks. There's a common belief that, because you're not using
the usual SQL structure to work with the database, you're safe and don't have to worry
about a thing. Unfortunately, **you couldn't be more wrong**.

The problem is that a lot of developers think about the end and not the method. Here's what
I mean: the popularity of RDBMS databases like MySQL, especially with PHP, have made quite
a few sites "powered by" them. Because of this, the most popular kind of "SQL injection" follows
the pattern in the example above. Most people think of this kind of problem when thinking
about the filtering and input handling in their applications. They think that, because
they're not using that SQL structure, they're safe from people trying injection attacks
against them. Too bad they don't realize, sometimes until it's too late, that the "injection"
part of "SQL injection" is the key, not the "SQL" part.

#### So, about that MongoDB

So, you might be asking, if I'm using a NoSQL database, how could an attacker compromise
my system since the structure isn't even the same. Well, there's one thing that I have to
preface this with - obviously the attacker would need to know you're using a NoSQL database
versus a RDBMS. If you've done your job as a developer, this could be very difficult to
figure out. If they do manage to find it out, there's some interesting things that PHP
might allow them to to if you're not careful.

Here's a simple example where a little filtering could go along way. In the code below,
we assume that we have a `people` collection with a few records in it, each with their
own `name` value.

`
<?php
$results = $db->people->find(array('name' => $_GET['name']));
$results = iterator_to_array($results);
var_export($results);
?>
`

This is all well and good if the correct URL is called - something like: `/?name=Test%20User`.
This would result in a correct search for the `name` value. Unfortunately, due to how
PHP handles URLs and array values, this allows for an exploit when called with a URL
like: `/?name[$ne]=1`. How does PHP interpret this?

`
<?php
print_r($_GET['name']);
// results in Array([name] => Array([$ne] => 1))
?>
`

Now, if the attacker uses a URL like this, all of a sudden your call to find a certain
user by a name match is returning a complete list of users!

#### Don't Forget the execute()

In PHP, the MongoDB functionality isn't limited to just using things like the `find()`
method to locate records. There's also the ability to [execute](http://php.net/manual/en/mongodb.execute.php)
queries directly - Javascript-formatted strings that can do more advanced operations
than a simple `find` or even `map/reduce` might be able to accomplish.

When you think about this, a JSON/Javascript-formatted string is even more similar to the
RDMBS SQL example above. If you're not correctly filtering your input, a user could inject
their own content directly in to the middle of your request and potentially do Bad Things.
Here's an example:

`
<?php
$results = $db->execute('print("'.$_GET['username'].'");');
var_export($results['retval']);
?>
`

This is a pretty simplistic example, but it gives you the idea - *injection attacks have
less to do with the context they're being injected into and more to do with the use of unfiltered
data.* See, if you don't check the incoming user data, even just a little bit, you're in
for a world of hurt ([more about that here](/2012/09/14/Dirty-Data-Protect-App-Users.html)).

If you call the above code with something like `/?username=");db.people.drop();print("`
you'll find yourself really quickly without a `people` collection to work with. Since the code
just injects what the user gives directly into the query in the `execute` call, the attacker
has unlimited access to send those commands directly to your application's internals.

In this particular case, there's a few things you could do to help prevent possible attacks.
Say your `username` values have to conform to a certain set of standards. If you can easily
reproduce them in a regex, you could do something like:

`
<?php
$name = $_GET['name'];
if (preg_match('[a-zA-Z0-9]{8,}', $name) !== false) {
    // perform the operation
}
?>
`

In the code snippet above, we verify that the username they've given us matches against the
format that we enforce for our usernames. If they try to pass in the exploit value from
above, this would obviously fail and not execute the query.

#### A Note about ORMS

Anyone that's used any kind of database in the past knows how handy ORMs (Object Relational
Mappers) can be, but if you choose to use one in your application, be aware of something. Some,
but not all of the ORMs out there will provide some kind of protection against SQL injection
attacks on your NoSQL data.

Here's an example using the [MongoQB](https://github.com/alexbilbie/MongoQB) query builder
library that exhibits the same result as the default Mongo client above (through no fault
of its own, it's doing its job correctly):

`
<?php
$qb = new \MongoQB\Builder(array(
    'dsn' => 'mongodb://localhost:27017/testdb'
));

$results = $qb->where('name',$_GET['name'])->get('people');
var_export($results);
?>
`

In this case, the value for `$name` comes from the URL and resolves to `Array([name] => Array([$ne] => 1))`
resulting in the exact same problem as before.

Be sure that when you're using an ORM, you're not lulled into a false sense of security thinking
that it'll take care of these issues for you. In the case of the ANSI SQL injections, it's a little
simpler to try to filter the input but NoSQL injections, because they're more data-related, are
a whole different thing.

Moral of the story? Filter input and validate that it's the type you're expecting.

#### Resources

- [PHP Manual for MongoDB security](http://us3.php.net/manual/en/mongo.security.php)
- ["NoSQL, No Security?" from AppSecUSA '12](http://www.youtube.com/watch?v=YEhy_SuCrYQ) (a video)
- ["NoSQL, No Injection?" from BlackHat '10](http://www.slideshare.net/wayne_armorize/nosql-no-sql-injections-4880169) (slides)
- [Avoiding NoSQL-injection with MongoDB](http://erlend.oftedal.no/blog/?blogid=111)
