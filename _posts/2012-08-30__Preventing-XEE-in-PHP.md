---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Preventing XEE in PHP
---

Preventing XEE in PHP
--------------

{{ byline }}

There's an injection attack that's been around for a while now that's slipped under
the radar for a lot of web application developers. Unfortunately, it can be one that
could cause some serious information disclosure (or exploits) if it's not taken care
of. XEE (an XML eXternal Entity) injection attack takes advantage of a part of the XML
structure that's usually reserved for defining custom entities in your XML documents -
the ENTITY portion of the DOCTYPE definition. This section is usually used to define 
custom entities for the document such as:

`
<!DOCTYPE root [
    <!ENTITY test1 "testing">
    <!ENTITY mantra "test all the things">
]>
`

In the above example you'll see two custom entities that we've added to our document -
`test1` and `mantra`. This lets us do some easy (and multiple) substitutions in our
XML document using the entity versions of these two, `&test1;` and `&mantra`. When 
these entites are expanded, their replacements strings are put in their place. This
is a pretty simple example, but it should make it easier to pick up on what's coming next.

So, this is a handy feature to have when you need it, but there's lots of languages out
there, PHP included, that don't take something into consideration: external references.
See, in our example above, we set the value to a string that we determined. What happens
when the XML is coming from an outside source...like with a web service. The incoming
XML could contain just about anything. Now, hopefully your code is only looking for
certain values, but that's pretty easy to figure out, especially if you've done a good
job documenting your APIs. 

The real problem is that, in PHP, when you use one of the XML parsing methods (dosen't
matter which - [XMLReader](http://php.net/xmlreader), [DOM](http://php.net/dom) or 
[SimpleXML](http://php.net/simplexml) - they all pretty much handle it the same way by
default. They take whatever entities are defined in the DOCTYPE header and try to 
expand them by default. This is good news for those looking for convenience but bad
news for those that are more security minded out there.

Let's look at an example to see why:

`
<?php
$xml = '<!DOCTYPE root
    [
    <!ENTITY foo SYSTEM "http://test.localhost:8080/contents.txt">
    ]>
    <test><testing>&foo;</testing></test>';
?>
`