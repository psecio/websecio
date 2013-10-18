---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Two-Factor with a Wave - Using Clef
tags: twofactor,clef,wave
summary: The Clef service provides an interactive, easy to implement two-factor solution.
---

Two-Factor with a Wave - Using Clef
--------------

{{ byline }}

With two-factor authentication gathering more and more steam across the web, it's no wonder
more services around it are popping up. I've already discussed some of the other options out
there including [Yubikey](/2013/03/05/Two-Factor-the-Yubikey-Way.html), [Google Authenticator](/2013/01/11/Googles-Two-Factor-Auth-Online-Offline.html), [Duo Security](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html) and
[Authy](http://websecio.localhost:8888/2013/01/07/Easy-Two-Factor-with-Authy.html). I'm going to
add to this list with a relative newcomer to the scene - [Clef](http://getclef.com).

**Clef** bills itself as:

> A mobile application that replaces usernames and passwords with your smartphone. When you use Clef, you
> create a profile that never leaves your phone. Clef uses that profile to generate a new digital signature
> every time you log in. Sync with Clef Wave to send your signature and identify yourself.

It makes sense to have a custom smartphone application be the gatekeeper for the authentication, but
how is it different that some of the other solutions? Well, Clef handles things a bit differently. It starts
with their "wave" - here's a screenshot showing it in action:

![Clef login page](/assets/img/clef-sample-page.png)

This overlay is launched when the user clicked on a customized "Log in with your phone" button (you can see
it dimmed in the top left). The "wave" is a series of lines moving up and down in the center panel. It's
essentially a moving barcode that the application can then read.

#### The Flow

Here's the basic flow of the process to give you an idea of how the system works:

1. The user downloads the Clef application for their particular device
2. Once launched, they create an account with their related information, including things like First Name and Phone Number (most are optional)
3. Once the registration process is finished, the application launches the camera and overlays the "wave" on top.
4. The user then visits [a site that supports Clef logins](https://getclef.com/websites), clicks the button and holds the device up to the screen.
5. The application matches against the "wave" and sends a message back to the Clef servers about who they are.

This is where things shift over to the development side of things:

6. When the "wave" is matched and the user is confirmed, the Javascript forwards them over to a `login` page
7. This page, using a `code` parameter and the application's `ID` and `secret key`, makes a request to the Clef API to authenticate the user.
8. One the user is authenticated, their information can be requested from the API with a response that includes a unique token.
9. This token is then used in other requests, like the one to get the current user's information, including a unique `user ID`;

Simple, right? By setting it up this way, your application only has to store the unique `user ID` along side their own users. Then, when a user clicks to log in with their phone, the application can verify them and check against the `ID` to be sure they're who they say they are. They define how long they want to stay logged in (or indefinitely) and log out witha quick touch of the screen.

Thankfully, some of this process is handled by the service. There are some thing's you'll need to do, though. I'm going to give you an example of a basic site that uses the [Slim PHP framework](http://slimframework.com) and a library I've put together - [enygma/clef](https://github.com/enygma/clef) - to make a working example of logging in and out of your site.

#### First Steps

So, first off we'll need to get things installed. We're going to use [Composer](http://getcomposer.org) to manage our PHP dependencies. Here's what your `composer.json` file will need to look like:

`
{
    "require": {
        "enygma/clef": "dev-master",
        "slim/slim": "2.3.1"
    }
}
`

Run a `composer install` and you'll get the libraries you'll need (as well as any dependencies) where you can access them. Now, we're going to make our *Slim* application. This could be anything, even your current application. It doesn't have to exist as a separate service to work correctly.

First, we need to make an application - head over to the [Clef developer site](https://developer.getclef.com) and log in (using Clef, naturally). Then go over to the *Applications* menu and select *New Application*. Fill in the needed info and pick the permissions you want the app to have then click "Submit" to save it. When you've created the application in the Clef system, it will provide you with an "application ID" and "application secret" you'll need for your scripts. Copy these so you can use them here in a bit.

I'm going to share all of the Slim-related code for the whole sample app here, mostly to help it make sense as a whole rather that pieces scattered through the rest of the article. Obviously, the application ID and secret here are made up, but you get the idea:

`
<?php
require_once 'vendor/autoload.php';

$appId = '16d7a4fca7442dda3ad93c9a726597e4';
$appSecret = 'e555fdfacffb3eca7c0f8988e1cc3934';

$request = new \Clef\Request($appId, $appSecret);
$request->setClient(new \Guzzle\Http\Client());

$app = new \Slim\Slim();

// Start the page
$header = '&lt;html>&lt;head>&lt;/head>&lt;body>';
$footer = '&lt;/body>&lt;/html>';

$app->get('/', function() use ($app, $request, $header, $footer) {
    echo $header;
    $appId = $request->getAppId();
    $redirectUrl = 'http://test.localhost:8888/clef/login';

    echo '&lt;script type="text/javascript" class="clef-button" src="https://clef.io/v2/clef.js"';
    echo 'data-app-id="$appId" data-redirect-url="$redirectUrl"></script>';
    echo $footer;
});

$app->get('/login', function() use $app, $request) {
    $userCode = $app->request->get('code');

    try {
        $result = $request->authenticate($userCode);
        $user = $request->getUser();

        $_SESSION['user'] = (array)$user->info;
        $app->redirect('/clef/user');

    }catch(\Exception $e) {
        echo 'ERROR: '.$e->getMessage();
    }
});

$app->get('/user', function () use ($app, $header, $footer) {
    echo $header;
    print_r($_SESSION['user']);
    echo $footer;
});

$app->post('/logout', function () use ($app, $request) {
    $logoutToken = $app->request->post('logout_token');

    $result = $request->logout($logoutToken);
    if ($result !== false) {
        /** custom logic to log out the user based on the ID */
    }
});

// End the page
$app->run();
?>
`

The Slim microframework makes setting up this example pretty easy. If you're not familiar with the framework, I suggest checking out [their documentation](http://docs.slimframework.com/) for more information on getting started with it. The above code defines a few different routes we'll need to use the Clef API:

- A `GET` route for the main index page to show the Clef button
- A `GET` for the login page that makes the login (authenticate) request back to the Clef API
- A `GET` for a "user" route - this is just a placeholder after the login in finished
- And, finally, a `POST` route to handle the logout

#### The Walk-through

So, lets start from the top with **the `/index` route**. This route's job is to show a very basic page...in fact, the only thing on it is the &lt;script> tag. The source on this tag is a special Javascript file on the Clef servers. It make dropping the "Log in with your phone" button into anywhere in your page a lot simpler. They also offer a few other customization options not shown here, mostly dealing with the appearance of the button. You'll need to know your application ID and redirect URL in order to correctly create the button.

> **NOTE:** One thing to be sure of - when setting a redirect URL here, it needs to be the same domain (and port) as the one you gave in the application configuration. If it's not, you'll get a message when the page loads that the redirect URL isn't correct. Unfortunately, this makes testing it in a development environment and bit more difficult. You'd just have to remember to change the URL when you push to a live site.

Next up is **the `/login` route**. This route is the handler for the post-button click process. When the user clicks the button to log in, the application is configured with a "login" page to redirect to. When it requests it, it attaches a `code` variable to the GET request. This `code` is used to make a query back to the Clef API and authenticate. When they're successfully authenticated, a "request token" is returned.

With this token in hand, the script makes another call to get the matching user information for the token. In this data is where you'll find what you need to match against. There's a unique ID that's returned that you'll need to store in your system. When a user tries to log in with Clef and you get back the user information, you'll need to verify the requesting user's ID matches the one you have stored. When the authentication is successful, the user is then redirected to the `/user` route.

So that's the basic flow of the request - click on the button, try to auth the user from the code and get the user's information with the help of the login token.

There's one more route to worry about, **the `/logout` route**. This is another one of the special routes that you'll need to set up on the Clef configuration for your application. There's a special spot in the configuration where you define the "logout" URL for it to hit when the user chooses to log out from their device.

On the user's device, they've either told it how long they want to stay logged in or they have a button they can hit to manually log them out. Either way, the Clef service looks at your configuration and hits the "logout" URL you've specified. When it does, it gives you a `logout_token` variable. This variable is then sent over to the API to grab the user associated with it, including the unique user ID. You can then check this ID against your system and terminate the login session for that user.

Unfortunately, as this request to the `/logout` endpoint is made outside of the context of the current user's session, you'll need a session management solution that validates against something like a database for a timeout. The default PHP session handler uses a cookie to store the session ID that matches the current user. The Clef system won't have this cookie, so it's likely that their session will be completely different. You could use the [session_set_save_handler](http://php.net/session_set_save_handler) to define a custom session handler (more info on this [here](/2012/08/24/Shared-Hosting-PHP-Session-Security.html)) that can do something like this.

How you log out the user is completely up to you. If you'd like ot see a more interactive example, go log into the Clef site and then hit "logout" on you device. The site almost immediately logs you out and sends you back out to an unauthenticated page.

#### Wrapping it up

The Clef system is an interesting entry in the two-factor authentication landscape. It's just different enough - between it's "wave" and how the smartphone application works - to set it a bit apart from the rest of the 2FA crowd. It's a little bit odd to use, though, as it seems to only let you work with a login on one site at a time unlike some of the other one-time password (OTP) solutions out there. This can be relatively limiting as you currently have to log out of the application you're in (via the device) to be able to even get back to the scanner to log in to another service.

I've only had a little bit of trouble with their smartphone app so far, mostly with random startup problems. Other than that, though, it's been super easy to use. It's very finely timed and doesn't take log to recognize the "wave" when you hold your device to the screen. The API is simple (basically authenticate and fetch user) so there's not a lot to worry about there. It's a service worth keeping an eye on to see how it evolves in the future.

#### Resources

- [Clef homepage](http://getclef.com)
- [Slim framework project](http://slimframework.com)
- [Using Drupal and Clef](https://drupal.org/project/clef)
- [Slim framework](http://slimframework.com)

