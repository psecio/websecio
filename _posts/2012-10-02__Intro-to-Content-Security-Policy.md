---
layout: default
author: David Müller
email: mueller.dav@googlemail.com
title: An Introduction to Content Security Policy
---

An Introduction to Content Security Policy
--------------

{{ byline }}

> NOTE: This is a guest post by David Müller and was originally posted on his blog
> [d-mueller.de](http://www.d-mueller.de/blog).

The problem of Cross-site-scripting / XSS seems to be more present than ever before -
you constantly hear about new attacks with devastating consequences that go far beyond
defacing guestbooks.

Being in development since 2009, the Content Security Policy is now almost completely
implemented in Chrome and Firefox and strives to end the misery of XSS.

Currently, the CSP is still in the state W3C Working Draft and is complemented by features
related to HTML5 like web sockets. You can use the CSP already today without any problems -
but let's start from the beginning.

#### What is the Content Security Policy?

The main pattern concerning XSS attacks is the inclusion of inline scripts in the HTML code
of a page which are then delivered and executed with every subsequent request.

In an ideal world, Javascript should be delivered exclusively in external .js-files. CSP is
driven by the concept, that Javascript shall only be executed if it is located in a script file.

Of course, the user's browser itself is responsible for compliance with the CSP, so the
protection is done on the client side and will only happen in modern browsers.

If the user's browser supports CSP, inline scripts are not executed - no chance for
malicious code that might be contained in the page.

To be able to use the CSP on your page today, a few adaptions might be needed.

`
<a id="mylink" onclick="foo()">
    Foo
</a>
`
... becomes ...

`
<script src="script.js"></script>
<a id="mylink">Foo</a>
`

... with the help of jQuery, the external scriptfile `script.js` looks like this:

`
$(document).ready(function()
{
    $("#mylink").on("click", function()
    {
        foo();
    });
});
`

It's anyway a good decision to separate HTML and Javascript, so making pages ready for
using CSP might bring some additional code quality to some pages.

#### But CSP is able to do way more!

The Content Security Policy is not limited to fighting inline scripts. There are a
variety of other resources that can be regulated, e.g. from which locations images
and CSS files must be loaded. This is smart, because even infiltrated CSS files and
images can pose a security threat. The CSP is designed to provide exceptions for each
rule, but this will be shown later in the examples.

For some rules, it's additionally possible to configure special options. For example,
the most hated Javascript "feature" `eval` is turned off by default when using the CSP
but you can enable it manually if you really need it. Furthermore, the CSP comes with
a built-in reporting process. For each detected violation of the policy, a predefined
script is automatically triggered via XmlHttpRequest by the user's browser with a POST request
and notifies the webmaster that a potential security risk is present on the reported page.

Also great: It is possible to activate a test mode, in which the CSP is not actively
enforced, but the reporting is turned on - this can be very useful for the webmaster
to see what the impact would be if the CSP was enabled.

#### Using the CSP

The desired policy is transmitted via HTTP header. In an earlier version of the
specification, the configuration of the CSP via meta tag was also supported. The HTTP
header currently differs from browser to browser, because the state of the CSP is
still experimental.

**Chrome**

`
X-WebKit-CSP: <CSP rules here>
`

**IE + Firefox**

`
X-Content-Security-Policy: <CSP rules here>
`

The **final header** after the specification is ready

`
Content-Security-Policy: <CSP rules here>
`

The following script can be very useful to avoid redundancy:

`
<?php
// Set a sample rule
$csp_rules = "script-src 'self' cdnjs.cloudflare.com; style-src 'self'";

foreach (array("X-WebKit-CSP", "X-Content-Security-Policy", "Content-Security-Policy") as $csp)
{
    header($csp . ": " . $csp_rules);
}
?>
`

#### What is covered by the CSP?

- **script-src:** Determines which domains are whitelisted for loading external script files
and if inline scripts are allowed or not. Furthermore, eval can be activated again, which is
restricted by the CSP per default.

- **object-src:** Determines which domains are whitelisted for loading Flash und other plugins
like SilverLight. This is possible for the tags &ltobject>, &lt;embed> and &lt;applet>

- **style-src:** Determines which domains are whitelisted for loading CSS files. Important: It
is impossible to allow inline CSS. This is deactivated by the CSP and can't be activated again.

- **img-src:** Determines which domains are whitelisted for loading images. This is not only
valid for the &lt;img> tag but also for CSS background images.

- **media-src:** Determines which domains are whitelisted for loading &lt;video> and &lt;audio> content.

- **frame-src:** Determines which domains can be included via frame or iframe - frame-src
https://facebook.com would only permit facebook (i)frames.

- **font-src:** Determines from which domains external fonts can be loaded via the @font-face
directive.

- **connect-src:** Determines to which pages a connection via WebSocket and XHR should be possible.

And **very important**: `default-src` specifies a default value for all resources that are not
explicitly mentioned.


#### Default Behavior

As a default, the CSP behaves like the `default-src: *` would be active - this means all
resources can be loaded from everywhere. This is per s&eacute; not very secure but at least prevents
the execution of inline CSS, inline script and eval - if you don't activate it manually, which
is not possible with inline CSS.

As mentioned before, `default-src` sets a standard for all resources that are not present in
the CSP ruleset.

#### Some concrete examples

`
default-src 'self' cdn.foobar.de; script-src 'self' cdnjs.cloudflare.com; style-src 'self' static.ak.fbcdn.net
`

All resources can be loaded from the own domain (`self`) and from `cdn.foobar.de`.

Important: `self` is contained in quotation marks because it is a keyword - otherwise, self
would be treated as a domain. The keyword `self` is very strict with the exact domain: If a
website is located at `david.myspace.com` and only `self` is whitelisted as a resource, the
domain `other.myspace.com` is not allowed as content provider. And the other way round: If
`myspace.com` and `self` is whitelisted, the domain script.myspace.com is not allowed and
has to be explicitly added.

Scripts can only be loaded from the own domain and `cdnjs.cloudflare.com` but **NOT** from
`cdn.foobar.de`, because `default-src` is overridden for scripts as soon as `script-src`
is explicitly mentioned. The execution of inline scripts is possible, eval is not allowed.

Stylesheets can be loaded from the own domain and from `static.ak.fbcdn.net`.
Inline styles are **NEVER** allowed.

#### Want to try it yourself?

<code>
<?php
$csp_rules = "default-src 'self' cdn.foobar.de; script-src 'self' cdnjs.cloudflare.com;
style-src 'self' static.ak.fbcdn.net";

foreach (array("X-WebKit-CSP", "X-Content-Security-Policy", "Content-Security-Policy") as $csp)
{
    header($csp . ": " . $csp_rules);
}
?>
||link rel="stylesheet" href="http://static.ak.fbcdn.net/rsrc.php/v2/yW/r/54gK7YK85pd.css" />
||script src="http://cdnjs.cloudflare.com/ajax/libs/underscore.js/1.3.3/underscore-min.js">||/script>
||script src="http://local/csp/s.js">||/script>
||style>
h1 { color: red }
||/style>
||h1>Rot?</h1>
||script> alert("123") ||/script>
</code>

Chrome's Web Inspector console tells us:

![CSP in Chrome](/assets/img/csp-1.png)

#### Next example

`
script-src 'self' 'unsafe-inline' 'unsafe-eval'
`

This shows the possible special rules when dealing with script-src - `unsafe-inline`
and `unsafe-eval` are allowed, which means that it is possible to use the eval-function
and inline scripts will be executed. Don't forget the quotation marks because we do not
want these special rules to be treated as domains. This rule is not recommended at all,
because it makes the CSP useless.

`
||script>
alert(eval("2+3"))
||/script>
`

`unsafe-inline` and `unsafe-eval` have to be enabled explicitly because they are disabled
by default.

#### Next example

`
default-src 'self' https://*.site.de; frame-src 'none'; object-src 'none'
`

Content is loaded from the own domain and from any subdomain of site.com - but only via
HTTPS. Frames and object embeds are not present and will not be loaded at all. That's
it with the keywords. More examples can be found at this mozilla page.


#### The reporting feature

If a `report-uri` is appended to the CSP ruleset (can be absolute or relative), this URI
is called on every violation via XHR-POST request from the user's browser. Here, we can
process the violation: send email, write to logfile / database.

An example:

`
<?php
$csp_rules = "script-src 'self' 'unsafe-inline'; report-uri http://local/csp/reportcspviolation.php";

foreach (array("X-WebKit-CSP", "X-Content-Security-Policy", "Content-Security-Policy") as $csp)
{
    header($csp . ": " . $csp_rules);
}
?>
||script>
alert(eval("2+3"))
||/script>
`

Since `unsafe-eval` is not enabled, we have a violation and the `report-uri` is invoked.

![CSP in Chrome](/assets/img/csp-report.png)

The code of the file `reportcspviolation.php` might look as follows:

`
<?php
$c = file_get_contents("php://input");

if (!$c)
    exit;

$c = json_decode($c, true);
$c = print_r($c, true);

file_put_contents("csp.errors", $c, FILE_APPEND);
?>
`

We receive detailed information about the page on which the violation happened and what
exactly went wrong:

`
Array
(
    [csp-report] => Array
        (
            [document-uri] => http://local/csp/x.php
            [referrer] =>
            [blocked-uri] => self
            [violated-directive] => inline script base restriction
            [source-file] => http://local/csp/x.php
            [script-sample] => alert(eval("2+3"))
            [line-number] => 1
        )
)
`

Furthermore, it is possible to enable the reporting feature without enforcing the CSP.
This is very useful for experimentation: One CSP ruleset can be activated, but the other
is used for reporting the violations and shows, what would happen if it was enabled.

`
//Firefox only, use the loop from above to test in other browsers
header("X-Content-Security-Policy: script-src 'self' 'unsafe-inline'; report-uri /activeviolation.php");
header("X-Content-Security-Policy-Report-Only: script-src 'self'; report-uri /evaluationviolation.php");
`

In this example, the webmaster is enforcing the CSP in line 1 but is testing the more
restrictive CSP in line 2.

#### Recommendations

Some last words: I had trouble using Firefox with Firebug when testing the CSP. This might
be due the fact that Firebug - as a plugin - is not natively bundled with the browser. At
least, the console was not very helpful in some situations. But even with the setup
Firefox + Firebug, testing the CSP is not a big problem because we can still use the
reporting URI.

A tip for building your own CSP rules: It is recommended to forbid everything on default
and enable the things you need with exceptions from the ruleset - *"relaxing the policy"*.
Might look like this:

`
X-Content-Security-Policy: default-src 'none'; script-src 'self' js.mysite.com; style-src 'self' css.mysite.com; img-src 'self' images.mysite.com
`

I think you can figure out what happens. With the directive default-src, everything is
forbidden be default. After that, we use exceptions for scripts, CSS and images. Frames,
objects / embeds, audio etc. remains forbidden, because no exception is defined.

There is a cool bookmarklet which gives you CSP rule recommendations based on the resources
of the current site

Currently, there is a debate in the CSP working group about script-nonce to execute inline
scripts only when they have a `nonce="random_string"` attribute. With this, you can tell the
browser that an inline script is there intentionally. Stay tuned and have a look at the W3C
Working Draft if you want to follow up with the development of the specification.


#### Related Posts

- [Content Security Policy - Tutorial](http://www.d-mueller.de/blog/content-security-policy-tutorial/)
- [IE und Chrome mit Standard-XSS-Filter (X-XSS-Protection)](http://www.d-mueller.de/blog/ie-und-chrome-mit-standard-xss-filter-x-xss-protection/)
- [Parallel processing in PHP](http://www.d-mueller.de/blog/parallel-processing-in-php/)
- [Der Einfluss von Cookies auf die Performance einer Webseite](http://www.d-mueller.de/blog/der-einfluss-von-cookies-auf-die-performance-einer-webseite/)
- [Web-Performance: Best Practices](http://www.d-mueller.de/blog/web-performance-best-practices/)

