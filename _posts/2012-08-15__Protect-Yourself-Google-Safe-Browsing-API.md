---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Protect Yourself with the Google Safe Browsing API
---

Protect Yourself with the Google Safe Browsing API
--------------

{{ byline }}

One of the many services that Google offers is something called "Safe Browsing" and 
*drum roll* it's an API - one that you can request via a specially formed URL to get 
information back about provided URLs.

> Safe Browsing (v1) is a service provided by Google that enables applications to check URLs 
> against Google's constantly updated lists of suspected phishing and malware pages.
> The Safe Browsing (v2) API is an experimental API that enables applications to download 
> an encrypted table for local, client-side lookups of URLs that you would like to check.

You can pass any URL over to this API and it can tell you some helpful information about
it inclduing whether or not it's safe to visit or if there's some issue they know about
for that application/site.

It's an easy API to use, so here's a quick step-by-step to getting it set up and working
in your system:

1. *Signup*: As with any of the Google APIs, you'll need to sign up to get your credentials
by [visiting this page](https://developers.google.com/safe-browsing/key_signup). You'll be 
given an API key to use in your requests and en example URL you can test to be sure things
are working correctly.

2. *Create a client*: Since there's no sense in reinventing things, I'm going to use the 
[Guzzle HTTP client](http://guzzlephp.org/) and install it via [Composer](http://getcomposer.org).

First off, our `composer.json` to pull the needed packages:

~~~
{
    "require": {
        "guzzle/guzzle": "*"
    }
}
~~~

A quick call to `composer.phar install` later and you'll have the Guzzle library, ready for use. 
I'm not going to get into much detail on how Guzzle works - I'm just going to show it in use
to make our API GET request.

`
<?php
include_once 'vendor/autoload.php';

function validate($url)
{
    $urlBase = 'https://sb-ssl.google.com';
    $params = array(
        'client' => 'api',
        'apikey' => 'YOUR-API-KEY',
        'appver' => '1.5.2',
        'pver'   => '3.0',
        'url'    => $url
    );

    // put our params on the URL
    $requestPath = '/safebrowsing/api/lookup?';
    foreach ($params as $name => $param) {
        $requestPath .= $name.'='.urlencode($param).'&';
    }

    $guzzle       = new Guzzle\Service\Client($urlBase);
    try {
        $response     = $guzzle->get($requestPath)->send();
        $responseCode = $response->getStatusCode();

        if ($responseCode !== 204) {
            // lets find out what's wrong
            return $response->getBody(true);
        } else {
            return true;
        }
    } catch (\Exception $e) {
        echo 'ERROR: '.$e->getMessage();
    }
}
?>
`

Thanks to Guzzle, making our request is pretty easy - it handles all of the HTTP 
mess for us and gives us a clean interface to work with. The `validate()` function 
takes in a URL to check against the API, builds the request and sends it off.

The key to reading the responses (as is noted in [the docs](https://developers.google.com/safe-browsing/lookup_guide#HTTPGETRequest))
is the HTTP responses' status code. If their service has checked the URL against
its records and not found any issues, it'll return a `204` (No Content) status code
to Guzzle. We have access to this through the `getStatusCode` method on the Response
object. 

If it's a `204` everything's happy and shiny and you can go along your merry 
way. If it's anything else, though, that either means there was an error in the 
handling of the request or the Google Safe Browsing API found something wrong with the site.
Here's two examples:

```
<?php
$websec = validate('http://websec.io');
if ($websec === true) { echo 'GOOD!'; }

$badguy = validate('http://ianfette.org');
if ($badguy !== true) {
    echo 'RESON: '.$badguy;
}
?>
```

For the first call to check the `http://websec.io` site address, the result is `true`
as there's no currrent issues with that (this!) site according to Google. The second,
however, is an example "bad site" they use for you to test with. The `http://infette.org`
site shows up on the naughty list and, by grabbing the body of the response, our 
script knows why - "malware".

Hopefully this example will give you a good idea of how to use this handy service. The 
results you'll get from this service are the same ones you'd get if you visited the 
site in the Google Chrome browser. Anyone who has used that browser for any length of time
has seen the red warning screen pop up on certain sites. That checks against the same data
source.

#### Resources

* [Google Safe Browsing Lookup docs](https://developers.google.com/safe-browsing/lookup_guide)
* [Guzzle](http://guzzlephp.org)

