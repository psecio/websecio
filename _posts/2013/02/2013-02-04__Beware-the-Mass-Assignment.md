---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Beware the Mass Assignment
tags: vulnerability,massassignment,model
summary: Mass assignment vulnerabilities can be a hard-to-find issue in your applications. Learn how to prevent them.
---

Beware the Mass Assignment
--------------

{{ byline }}

There's been a lot of talk lately about issues with the Ruby on Rails framework and
some of the security issues that have come to light. There's been issues with a "magic"
security value and YAML parsing that allows for code injection. What I want to focus
on, however, is something that came up a while back but that PHP (code built with it,
not the language itself) could be vulnerable to - a **mass assignment vulnerability**.

Since Rails is *the* framework for the Ruby world, the impact of the issue was a
bit larger than it would be in the PHP community. That doesn't make it any less of
a threat, though. It can also be one of those difficult to track down problems as
it relies on the contents of a POST request, something usually not logged by most
web servers. This issue is, like most PHP vulnerabilities unfortunately, due to bad
coding practices and not handling the user inputted data correctly.

The good news is that, unless you're using something like an ORM or a model layer,
you might not have to worry about this issue. It's good to keep in mind, though,
given the prevalence of PHP frameworks on the web.

#### The Problem in PHP

So, what's this "mass assignment" problem really all about? Well, it's probably easiest
to illustrate with a code example:

`
<?php
class Model
{
    private $username = null;
    private $email = null;
    private $admin = false;

    /**
     * Populate the model with the given values
     * @param array $values Input data
     */
    public function values($values)
    {
        foreach($values as $name => $value)
        {
            if (property_exists(get_class($this), $name)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Save user to database
     */
    public function save() {}

    /**
     * Check to see if the user is an admin
     */
    public function isAdmin()
    {
        return ($this->admin == true) ? true : false;
    }
}
?>
`

If you've got a keen eye, you've probably already spotted the issue with the code above.
Still looking? Well, here's a hint: a little filtering could take care of the problem.
Still stuck? Okay, so here's the issue - notice how there's a property on our Model above
called "admin". Well, in our `isAdmin` method we check to see if the user has this value
set to `true`.

This is all well and good, but because of how the values are assigned through the `values`
method, this could lead to dangerous consequences. So, to set the stage a bit, say that
you have a form that allows for the creation of a new user (a Registration form maybe).
Obviously, you're not going to have a field for `admin` in the form as you don't want
to make it too easy for someone to make themselves an admin. That's the problem, though -
there's no filtering on the input from the user (at least in this code) that would prevent
them from submitting a value for the `admin` parameter and having that pass through to
the model and get set along with the rest of the data.

This is where the crux of the "mass assignment" vulnerability lies. Due to improper filtering
of the input (or access control to properties) a malicious visitor to your site could
include all sorts of values in the POST request to your site, hoping to score a direct
hit on the right one. This can lead to one of the OWASP Top 10,
[A3: Broken Authentication and Session Management](https://www.owasp.org/index.php/Top_10_2010-A3)
and can be quite dangerous for your application and its users.

A quick word of caution for those using a framework out there that includes it's own model
functionality (usually just the full-stack ones) - be sure to check and be sure that
you're not leaving yourself open to this kind of attack and not even knowing it. Be sure to
review the code for your framework of choice (or library) before you fully trust it.

#### Resolution

So, we've looked at the issue - lets take a look at some of the things you can do to
help mitigate the problem. Most of these are pretty simple, but some depend on the
structure of your application, so keep that in mind:

1. **Filter, Filter, Filter**: Yes, this has been said numerous times in other articles
   on this site, but this might be a little different kind of filtering than you're thinking.
   When you think of filtering and security, you're thinking about removing the harmful
   things from input that could cause problems. In this case, you want to filter out
   the data itself. When a user submits their POST request with the array of data,
   force a removal of any of the "restricted" values you don't want them to be able
   to override. For example, if your key is `admin`, then you'd want to call an
   [unset](http://php.net/unset) on that value before calling `values`.

2. **Enforce "restricted properties" in the Model**: This is something that not a
    lot of frameworks or domain model libraries out there have as a feature, but
    it's not overly difficult to implement. Basically, what you want is a set of
    "restricted" items that can't be overwritten when calling something like `values`
    to load the data. In our case, `admin` fits the bill. You could have a class
    variable that contained the list and use an [in_array](http://php.net/in_array)
    check to see if it's there. If it is, bypass the setting of that value.

3. **Don't make it a property**: This is the one that depends on the architecture
    of your application. The idea here is to create your application so that things
    like administrative rights aren't controlled by a single property on the object.
    If you use something like a permission set or some other kind of flag (maybe a
    record in another table) it makes this kind of attack a lot less plausible. Then
    you could have *isAdmin* checks on your user reach out to this other data and
    evaluate from there.

Obviously, these are just a few solutions to the problem - chances are yours will
differ based on how your application is structured, but this gives you a place to start.

#### Don't forget the type

One other thing to keep in mind that's at play here and could be tricky if you're not
looking for it - see that `isAdmin` method in the first code example? Take a look at
how it's evaluating to see if the user is an admin. If you've been dealing with PHP
for any length of time, you've probably come across the difference between the "equals"
and how they behave. Here's a quick overview:

Operator | Name | Use
---------|------|----------
= | Single Equals | Assign one value to another
== | Double Equals | Evaluate if two values are equal
=== | Triple Equals | Evaluate if two values are equal and are same type

Looking at this table, do you see the problem with the first example? (hint: it's been fixed
in the second code example). Since the double equals checks to see if the values are the
same but **does not** check type, it leaves the validation open to potential abuse. PHP
is a weakly typed language and allows for the shifting of one variable type to another.
Without type checking, you get interesting things like `"0" == false`, `1 == true` or the
fun one `null == false`. Unless you include that extra "equals" in there, your check is
not valid and could cause pain for you down the line.

> As a general rule, evaluation in PHP should be as **specific** as possible. Use triple equals
> and the [ctype_*](http://php.net/ctype) methods to ensure your data is what you're expecting.
> *When in doubt, filter it out* (don't try to adjust).

#### About PHP Frameworks

I went back and looked through the model/ORM layers of some of the major frameworks
in use today to see if they allowed the concept of "protected properties" in their
code, but found almost nothing about it. Most of the frameworks (including [CakePHP](http://cakephp.org)
and [FuelPHP](http://fuelphp.com)). I was, however, happy to see that the upcoming
version of the [Laravel Framework](http://four.laravel.com/docs/eloquent#basic-usage) (version 4)
has something included to deal with the mass assignment issue via a `fillable` and `guarded` property
lists:

`
<?php
class User extends Eloquent
{
    protected $fillable = array('first_name', 'last_name', 'email');
    // and
    protected $guarded = array('id', 'password');
}
?>
`

The property names are a little odd, but they get the point across. It's good to see
some concern for this in the PHP community. Hopefully some of the other frameworks with
their own ORMs will follow suit.

#### Resources

- [Rails Mass Assignment Issue: A PHP Perspective](http://rubysource.com/rails-mass-assignment-issue-a-php-perspective/)
- [WebDevRadio Podcast - Episode 97: "Mass assignment, vulnerabilities and fun fun fun"](http://www.webdevradio.com/index.php?id=122)
- [Hacker commandeers GitHub to prove Rails vulnerability](http://arstechnica.com/business/2012/03/hacker-commandeers-github-to-prove-vuln-in-ruby/)
- [Mass assignment on Wikipedia](http://en.wikipedia.org/wiki/Mass_assignment_vulnerability)
