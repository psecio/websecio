---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Beware the Mass Assignment
tags: vulnerability,massassignment,model
summary: TBD
---

Beware the Mass Assignment
--------------

{{ byline }}




Unlike Rails, this isn't a default PHP problem

#### The Problem

No filtering in the example below - they could submit an "admin" value of true (or 1)
and make themselves an admin when the record is saved.


So, what's this "mass assignment" problem really all about? Well, it's probably easiest
to illustrate with a code example, so here you go:

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

So, now that we've covered what the problem is, let's look at a solution to mitigate it.

Quick and dirty - add a check to "restrict" properties

`
<?php
class Model
{
    private $username = null;
    private $email = null;
    private $admin = false;

    private $restricted = array('admin');

    /**
     * Populate the model with the given values
     * @param array $values Input data
     */
    public function values($values)
    {
        foreach($values as $name => $value)
        {
            if (property_exists(get_class($this), $name) && !in_array($name, $this->restricted)) {
                $this->$name = $value;
            }
        }
    }

    /**
     * Save user to database
     */
    public function save() {}

    /**
     * We can only set the admin value from this!
     * @param boolean $admin Set user admin flag
     */
    public function setAdmin($admin)
    {
        $this->admin = $admin;
    }
}
?>
`

Obviously you can get more complex than this with properties/settings as a part of the model

#### Resources

- [Rails Mass Assignment Issue: A PHP Perspective](http://rubysource.com/rails-mass-assignment-issue-a-php-perspective/)
- [WebDevRadio Podcast - Episode 97: "Mass assignment, vulnerabilities and fun fun fun"](http://www.webdevradio.com/index.php?id=122)
- [Hacker commandeers GitHub to prove Rails vulnerability](http://arstechnica.com/business/2012/03/hacker-commandeers-github-to-prove-vuln-in-ruby/)