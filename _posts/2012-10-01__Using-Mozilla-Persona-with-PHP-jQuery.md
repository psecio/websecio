---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Using Mozilla Persona with PHP & jQuery
---

Using Mozilla Persona with PHP & jQuery
--------------

{{ byline }}

Mozilla has recently introduced a service that wants to help get rid of passwords for
sites all across the web in a simple, easy-to-use service - [Mozilla Persona](http://www.mozilla.org/en-US/persona).
(Not to be confused with the unfortunately similarly named, soon to be renamed 
[Personas](http://blog.mozilla.com/addons/2012/02/02/renaming-personas/) customization
feature for their browsers). The Mozilla Persona project in a nutshell:

> Persona allows you to sign in to sites using an email address you choose. So, 
> instead of having to manage multiple usernames and passwords across your favorite 
> sites and devices, you’ll have more time to do the important stuff. We’ll manage 
> the details! [...] Persona lets you get started with just your email address; you 
> can add your profile data later, when and where you think it’s appropriate.

If you've ever used something like [OpenID](http://openid.net/) the ideas behind Persona
will make you feel right at home. The service, launched in a sepearate window, uses
the Mozilla service for the authentication based on an email address and password (yes,
it doesn't completely do away with them). After signing up, you'll recieve an email
to validate the signup and a link to visit to get back to the application.

When you log in there, you then authorize the calling site to be able to access your 
information. This info, at least initially, only includes your email address and some 
other metadata (expires time, a unique ID, etc) and something they call an `assertion` 
and `audience` combination:

- An `assertion` is the unique information you get back when someone successfully
  authorizes against the Persona system. It's returned back to you via a simple Javascript
  callback and is a large encoded string. This string is how your application talks 
  back to the Persona service to ensure that the user did, in fact, just authorize with
  your site.

- The `audience` is pretty simple - it's just a static reference to your application's
  hostname (or full URL). This is usually something like `http://personal.localhost:80`.
  It doesn't need to be publicly-accessible or anything, just reference to your site.

#### In Practice - the Frontend

Mozilla has does pretty well when it comes to implmenting this service. They've made it
pretty simple to use, but you'll need a little bit of code to back it up. Here's an example
of the main HTML page that uses their Javascript for the connection:

```
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <title>Mozilla Persona Test</title>
        <script src="https://login.persona.org/include.js"></script>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
        <script src="persona.js"></script>
        <link href="http://twitter.github.com/bootstrap/assets/css/bootstrap.css" rel="stylesheet">
    </head>
    <body>
        <div class="container">
            this is a test<br/>
            <?php
            if (isset($_SESSION['user'])){ echo 'logged in as: '.$_SESSION['user']->email; }
            ?>
            <br/><br/>
            <button class="btn btn-primary" name="login" id="login">Login</button>
            <button class="btn btn-primary" name="logout" id="logout">Logout</button>
        </div>
    </body>
</html>
```

As you can see, we're including a few different things here:

- A Javascript file from `login.persona.org` - `include.js`. This brings in the Persona
  functionality as a drop-in module.

- `jQuery` just for convenience sake. You don't have to have this, but it makes it easier
  for lazy devs to work with the callbacks.

- Our own `persona.js` file with out handler code for the requests

- The [Twitter Bootstrap](http://twitter.github.com/bootstrap/) completely optional, 
  just makes things a little more pretty (and chances are, you're using it anyway, right?)

The next major piece of functionality is the `persona.js` file that's our custom code. 
This handles the callbacks Persona throws our way on their custom Javascript objects:

`
$(function() {
    $('#login').click(function(e){ navigator.id.request(); });
    $('#logout').click(function(e){ navigator.id.logout(); });
    navigator.id.watch({
        loggedInUser: null,
        onlogin: function(assertion) {
            $.post(
                '/auth.php',
                {assertion:assertion},
                function(msg) { console.log('login success!') }
            );
        },
        onlogout: function() {
            $.post(
                '/auth.php',
                {logout:1},
                function(msg) { console.log('logout success!') }
            );
        }
    });
});
`

One thing to point our first with the above code - with the inclusion of the Persona 
Javascript file, we get an object in our page: `navigator`. This is how our code interfaces
with theirs. You'll notice the calls to `navigator.id.logout` and `navigator.id.request`
that are attached to the login/logout buttons. These fire off the requests back to Persona
for those actions.

In our example page, when you hit the "Login" button, you'll get a popup like this:

![Mozilla Persona Popup](/assets/img/mozilla-persona-popup.png)

Once you enter in your information and authorize the application, the service calls 
whatever you have defined in the `navigator.id.watch` function as your `onlogon` callback.
In our case, it makes a post back to the `auth.php` script with the Persona `assertion`
as a payload.

#### In Practice - the Backend

Now we come to the other half of the equation - the backend code to validate the response.
In our `auth.php` file, we're responsible for checking to see if the `assertion` they've
given us is correct. This needs to be `POST`ed back to ther service along with the `audience`
value for validation. For this, we're going to use [the curl functionality](http://php.net/curl)
that's bundled into most PHP distributions:

`
<?php
session_start();

if(isset($_POST['assertion'])) {
    $url = 'https://verifier.login.persona.org/verify';
    $c = curl_init($url);
    $data = 'assertion='.$_POST['assertion'].'&audience=http://persona.localhost:80';

    curl_setopt_array($c, array(
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_HEADER          => true,
        CURLOPT_POST            => true,
        CURLOPT_POSTFIELDS      => $data,
        CURLOPT_SSL_VERIFYPEER  => true,
        CURLOPT_SSL_VERIFYHOST  => true
    ));

    $result = curl_exec($c);
    curl_close($c);

    $p = explode("\r\n\r\n",$result);
    $response = json_decode($p[2]);

    if ($response->status == 'okay') {
        $_SESSION['user'] = $response;
    }
}
?>
`

It's a pretty simple piece of code, really - all it does is make a connecton to the 
`verifier.login.persona.org` service (HTTPS, of course) and sends along the data to be
checked. If everything's good, you'll get a JSON reponse along the lines of:

`
 stdClass Object ( 
    [status] => okay
    [email] => user-email@address-here.com
    [audience] => http://persona.localhost:80
    [expires] => 1348971576253
    [issuer] => login.persona.org
)
`

You're looking for that `okay` value in the `status` to be sure everything's good. You
can then use this user information in your application and know they've correctly authorized 
to your application and approved access.

Logging out is simple too:

`
<?php
if (isset($_POST['logout'])) {
    session_destroy();
}
?>
`

Just destroy the session...

#### Some Drawbacks

There's a few drawbacks with the service as it stands right now:

- **It's just been released, so it's still pretty young.** I'm sure things will change 
  a lot for it in the coming year, so if you decide to try it in your app, be sure to 
  [subscribe to this list](https://mail.mozilla.org/listinfo/persona-notices) to get updates
  on the service as they're reeased.

- **There's no multiple levels of permissioning**. It's all all-or-nothing kind of thing
  and there's not an option to define any other level of access or permissions. It's up
  to the site's developers/admin as to how much a Persona user can do in their site.

- **It's not universally supported across browsers**. Yes, there'll always be trouble 
  here. This time it's [with recent versions of IE](https://developer.mozilla.org/en-US/docs/persona/Browser_compatibility)
  and some 3rd party browsers on iOS. Any browser that supports `window.postMessage()` 
  and `localStorage` should work correctly as well as allowing 3rd party cookies.

#### Behind the Scenes

If you're interested in seeing what's happening behind the scenes, check out 
[this site](http://lloyd.io/how-browserid-works) with full information about how the
`BrowserID` system works. Persona is based on this and is a user-friendly (and developer-friendly)
interface to it.

#### Resources

Check out the other resources below for more details:

- [Persona Homepage](http://www.mozilla.org/en-US/persona/)

- The [Persona Documentation](https://developer.mozilla.org/en-US/docs/persona) on the
  Mozilla Developer Network site

- [Security Considerations](https://developer.mozilla.org/en-US/docs/Persona/Security_Considerations)
  for implementation.
