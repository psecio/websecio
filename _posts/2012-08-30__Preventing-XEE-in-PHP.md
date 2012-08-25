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
expand them. This is good news for those looking for convenience but bad news for 
those that are more security minded out there as it can lead to XEE attacks without 
any kind of warning from the parser.

Let's look at an example to see how it could be dangerous:

`
<?php
$badXml = '<!DOCTYPE root
    [
    <!ENTITY foo SYSTEM "http://test.localhost:8080/contents.txt">
    ]>
    <test><testing>&foo;</testing></test>';
?>
`

In the above example, we've defined the `foo` entity in our header as a link to a text 
document on an external site, probably one of our own. When the PHP handlers try to parse 
this file, they automagically substitute the `&foo;` entitiy reference with the contenst
of that text file. So, say our PHP script is using SimpleXML to parse the incoming XML
document:

`
<?php
$goodXML = '<test><testing>my value</testing></test>';
$doc = simplexml_load_string($goodXml);
echo $doc->testing;
?>
`

Now, in this example, we're giving it a good XML structure, the kind we're expecting
with a valid value for `testing`. Now, imagine what might happen if we gave it the 
`$badXML` contents instead and the `contents.txt` file contained a bit of HTML or XML
markup itself (something scary like a `script` tag) and you had no idea. This could lead
to all sorts of problems including:

- Cross-site scripting issues if echoed back out without filtering
- Remote file inclusion (RFI)
- Data injection

Really, just about any exploit you can think of might come in this way. So, as a PHP 
developer with security in mind, how can you prevent issues like this from happening? 
Well, there's a few ways to go about it, but what they really boil down to is one thing
- don't load external entities. Since all of the XML parsing functionality that PHP 
offers is based on the `libxml` libraries, there's one function that's essentially a 
kill switch to prevent the loading of these entities:

`
<?php
libxml_disable_entity_loader(true);
?>
`

The [libxml_disable_entity_loader](http://php.net/libxml_disable_entity_loader) function
tells the underlying `libxml` parsing to not try to interpret the values of the entities
in the incoming XML and leave the entity references intact. If you're using SimpleXML, this
is really the only choice to prevent an XEE attack in the incoming XML. Fortunately, the 
two other XML parsing methods offer a few more features to help keep you safe while still 
allowing for the expansion of XML entities.

`
<?php
// with the XMLReader functionality:
$doc = XMLReader::xml($badXml,'UTF-8',LIBXML_NONET);

// with the DOM functionality:
$dom = new DOMDocument();
$dom->loadXML($badXml,LIBXML_DTDLOAD|LIBXML_DTDATTR);
?>
`

In both cases, we're adding in some predefined constant values (by name) that tell the
parser to either not allow a network connection during load (`LIBXML_NONET`) or to try to 
parse the XML according to the DTD (`LIBXML_DTDLOAD|LIBXML_DTDATTR`). Both of these
methods will keep your application safer from XEE issues. Of course, the XML you're
receiving is from an outside source and you **always** want to validate the data you
pull from the XML to ensure it's a) the type you expect it to be and b) that it's not
some kind of data being injected into your system.

##### Summary

I hope I've raised awareness about this very real issue for you and your XML-using
application and given you a few armaments to help prevent any issues it might cause in
your application.

##### Resources

- [PHP manual for libxml_disable_entity_loader](http://php.net/libxml_disable_entity_loader)
- [Zend Framework's recent XEE issue & bugfix](http://framework.zend.com/security/advisory/ZF2012-02)
- [Webappsec.org - XML Injection](http://projects.webappsec.org/w/page/13247004/XML%20Injection)




