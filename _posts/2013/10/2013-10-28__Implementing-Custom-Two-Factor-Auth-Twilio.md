---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Implementing Custom Two-Factor Auth (with Twilio)
tags: twofactor,custom,twilio
summary: Implement your own SMS-based two-factor authentication via the Twilio API.
---

Implementing Custom Two-Factor Auth (with Twilio)
--------------

{{ byline }}

It seems like a week doesn't go by without some major service announcing that they're adding two-factor
authentication to their service. They're taking the typical username/password methods they've implemented
and dropping in this extra layer of protection for their users.

#### Two-Factor Advantages

There's a few major advantages that come with adding a two-factor solution on top of your current authentication process:

- It's safer than just using passwords - they can be easily stolen, cracked or phished away from your users
- It provides an extra layer of protection to the application (see more about [Defense in Depth](2012/10/12/Core-Concepts-Defense-in-Depth.html))
- It can help increase customer confidence that your service is reliable and that you're doing more to protect their privacy

#### Two-Factor Disadvantages

As with any technology, there's always disadvantages that come with it as well. Some of them for implementing a two-factor solution include:

- The addition of "yet another device" that users will have to carry and remember. If they lose this device, there could be considerable trouble replacing it and resetting their account(s)
- It's not especially cost effective as it requires either hardware tokens to be distributed or adds more overhead for the support organization to handle issues related to its use
- It's also another hurdle for users to go over during the login process. Some that are used to the simple username/password process might find it annoying to have to enter the code each time

When thinking about implementing a two-factor solution be sure you keep both the positive and negative options in mind and how it all fits into your application and organization. Don't just implement it because all the "cool kids" are doing it.

#### Pre-existing Services

There's a number of pre-existing services out there that you might look at before trying to implement something
yourself (not "reinventing the wheel" so to speak). They're pretty similar in what they offer as far as the actual
two-factor part of the solution, but differ in the services around that. Here's a list of some of the more
popular services:

##### [Google Autheticator](https://code.google.com/p/google-authenticator/)

While not specifically a service, it is
    a pre-existing two-factor solution. I've already [written about how to implement it](/2013/01/11/Googles-Two-Factor-Auth-Online-Offline.html) using a custom library that should make it easy to drop in, but there's still a little work
    involved in getting it to work in your system.

##### [Yubikey](http://www.yubico.com/)

Again, not technically a service, but it is a technology that can be used as a
    secondary auth mechanism for your users. There's another article [on this site](/2013/03/05/Two-Factor-the-Yubikey-Way.html) about using the hardware device and the API YubiCo offers to validate it.

##### [Duo Security](http://duosec.com)

This is the first in the list that's more of a true service. As I've [written in a previous post](/2013/01/09/Two-Factor-Auth-Integration-with-Duo-Security.html) they offer a more complete user management
    and two-factor authorization solution with lots of handy features like SMS messaging and push authorization requests.

##### [Authy](http://authy.com)

This is another service (and another one I've [written an article about](/2013/01/07/Easy-Two-Factor-with-Authy.html)) with less features than what Duo Security offers but is still a solid service. It's definitely
    growing in popularity right along with some of the others, though, and supports a wide range of smartphones/devices.

##### [Clef](http://getclef.org)

Clef is a relative newcomer to the world of two-factor authentication. They've taken their
    own approach to the typical two-factor interface and moved away from the typical one time password code to what they
    call a "wave" (like a moving barcode). You can find out more about them [in this article](/2013/09/26/Two-Factor-with-Wave-Using-Clef.html).

##### Others

There's other options out there as well with their own features a niches in the two-factor market including:

- [Toopher](https://www.toopher.com/)
- [Tokenizer](https://www.tokenizer.com/)
- [Rublon](https://rublon.com/)

#### The Integration

For most applications out there, the idea of two-factor authentication means adding on to their current process. They already have some kind of auth mechanism and want to enhance it through a basic TFA process. Usually this means adding in an extra step for their users, post-login but pre-access. Before we get started with the actual code to make this work, there's q few questions you need to ask that will guide the implementation:

- When do we want to TFA? (ex. every login, only logins from new sources, etc)
- What services do we have at our disposal?
- Should we just roll our own?

This article focuses on that last question. The sample code shows you how to generate a (very) simple two-factor method into your current process without too much hassle. This system generates the code and sends it, via an SMS, to the waiting user. There's other implementations out there, but with the help of an API, it's a pretty trivial method.

> **NOTE:** The example code uses the [Twilio](http://twilio.com) API to send the SMS messages. If you don't already have an account, you can sign up for a trial. You'll also need to add a number for your account to use as the "From" in the message. Don't worry, they won't charge you off the bat - the only limitation is that the messages say they're from a trial account when the user receives them.

#### The Implementation

This implementation of a TFA solution is *super* simple - keep that in mind if you choose to drop this into your application and go with it. There's a lot of things that could probably be added on to help enhance it, but the main goal here was to get from Point A (no TFA) to Point B (TFA happiness) as easily as possible.

##### The Flow

Let's talk a bit about the flow of the process before getting into the actual code that makes it all happen. The example assumes you have an existing database of user and device information (phone number) and a basic username/password login in place. Here's the basic flow:

1. The username/password is authenticated and the user's TFA device number is pulled out of the data source.
2. The user would be redirected to a TFA page with something like a text field waiting for them to enter the code.
2. With the device number in had, we need to generate the unique code and store it temporarily in the database associated with the user.
3. Next the script will hit up the Twilio API and send an SMS with the code to the user's device.
4. The user receives the SMS and enters the code it gives into the site.
5. The code is validated and the user continues with the login process.

Now, there's a few things here to pay attention to, so let me go through them so these steps are clear. First off, let's talk about the code. 

##### Generating the Code

With some two-factor solutions, they use a one-time password (OTP) architecture. They use things like time-based algorithms to create codes that expire after a given time frame. This system doesn't use this method and instead generates a randomized code that's then related to the user in the database for validation.

Here's some basic code that will generate a code that's between 6 and 8 characters (just numbers):

`
<?php
$code = openssl_random_pseudo_bytes($length);
$userCode = '';
$i = 0;
while (strlen($userCode) < $length) {
	$userCode .= hexdec(bin2hex($code{$i}));
	$i++;
}
echo $userCode;
?>
`

To make this work, you'll need to have the [OpenSSL extension](http://php.net/openssl) installed. There's other ways to make a randomized code, but this one should give you a higher randomness than some other methods. If you don't have the OpenSSL extension installed, you could use something built-in like [mt_rand](http://php.net/mt_rand) and loop a few times. Remember, the code just needs to be randomized so it can't be easily guessed and it needs to be related to the user. Since most of the two-factor workflows add this check after the user has already logged in, the script can determine which code they'd need to validate against.

You need to take the result of this code generation, the string in `$userCode`, and relate it to the user. So, for example, if we have our `users` table with a `validation_code` column, we could make a simple PDO connection and insert it in the user's record:

`
<?php
$pdo = new PDO('mysql:host=localhost;dbname=myapp', $dbUsername, $dbPassword);
$sql = $pdo->prepare('UPDATE user set validation_code = :code, validation_date = NOW() where username = :username');
$sql->bindParam(':code', $userCode);
$sql->bindParam(':username', $username);
$sql->execute();
?>
`

This query uses bound parameters to help prevent any kind of SQL injection that could sneak its way in rather than just appending the code to a SQL string.

##### Sending the code

Now that we have a randomized code and we've added it to our user's record, we need to send it off. We'll make another assumption that they've already set up a device in your system to use for the two-factor authentication. More often than not, this is a smartphone or other device capable of receiving SMS messages. There's lots of other methods that could be used for this process, but I'm going to stick with the SMS version here.

In order to send the message, we'll need to use a service that can handle sending SMS messages. You could probably do this in-house but that's a lot more difficult. Instead we're going to use the excellent [Twilio](http://twilio.com) service's REST API to handle the delivery of our message. To follow along here you'll need to follow the directions earlier in this article about setting up an account and a number. They also have a great [SDK](https://www.twilio.com/docs/libraries) for using the API, but this is just a simple example, so I'm just going to go directly to the API.

When your account is all set up, you'll be assigned two pieces of unique data - an account ID and an authorization token. These will be provided at the top of your dashboard when you first log in. These are essentially used as a username and password for the API request, so you'll need to know them.

We're also going to use the [Guzzle](http://guzzlephp.org) HTTP client for PHP that can be easily installed via [Composer](http://getcomposer.org). Let's get that installed first. Make a file called `composer.json` in a directory and put the following in it:

`
{
	"require": {
		"guzzle/guzzle": "v3.7.4"
	}
}
`

This is the most recent stable version of Guzzle at the time of this article. You can (most likely) update that version number for grab a more recent one if it's available. To get composer to install the library, use the command `composer.phar install`. This will download that version of Guzzle and set up the needed autoloading.

Now lets use this in our application to send the `$userCode` we generated earlier. We're going to use the account ID, authorization token and your phone number to make the request to Twilio's "Accounts" endpoint:

`
<?php

require_once 'vendor/autoload.php';

$url = 'https://api.twilio.com/2010-04-01/Accounts/'.$accountId.'/Messages';
$message = 'Your verification code is: '.$userCode;

$data = array(
	'From' => $accountPhoneNumber,
	'To'   => $userPhoneNumber,
	'Body' => $message
);

$client = new \Guzzle\Http\Client();
$request = $client
	->post($url, array('Date' => date('r')), $data)
	->setAuth($accountId, $);
$response = $request->send();

$result = $response->getBody(true);
?>
`

In the above example, there's a few variables - most of them are pretty clear but here's a bit more detail:

- `$accountId` is your Twilio account ID
- `$userCode` is the code generated by the previous snippet
- `$accountPhoneNumber` is the phone number on your Twilio account
- `$userPhoneNumber` is the end user's phone number (the format is: a plus, country code, area code and number, no dashes)

When this is sent, the `$result` variable will contain the raw XML response from the Twilio API including a success or failure message (and reason). If all goes well, you'll get an SMS to the device you're testing with containing the string in `$message`. The response should look something like:

`
<?xml version=\'1.0\' encoding=\'UTF-8\'?>
<TwilioResponse>
	<Message><Sid>...</Sid><DateCreated>Mon, 28 Oct 2013 16:34:17 +0000</DateCreated><DateUpdated>Mon, 28 Oct 2013 16:34:17 +0000</DateUpdated><DateSent/><AccountSid>...</AccountSid><To>+12145555555</To><From>+12145555555</From><Body>Your login code is 14284159</Body><Status>queued</Status><NumSegments>1</NumSegments><NumMedia>0</NumMedia><Direction>outbound-api</Direction><ApiVersion>2010-04-01</ApiVersion><Price/><Uri>/2010-04-01/Accounts/.../Messages/...</Uri><SubresourceUris><Media>/2010-04-01/Accounts/.../Messages/.../Media</Media></SubresourceUris></Message>
</TwilioResponse>
`

If there was a problem, the message will look different and contain an error message with more details as to why it failed. That's all there is to it - you now have a basic script that will send your message to a given number. One thing to note - unless you have a paid plan, the messages will contain the string "Sent from your Twilio trial account" in addition to the rest of the message.


#### Integrating into your Authentication

With the basic script in hand, you can now integrate it into your application. Usually this means an extra screen that comes up post-login that consists of a single form field for the user to enter the code. There's a few things to think about when you're adding two-factor to your login process, though. For example:

- Do you want to do the authentication every time they log in?
- Do you only want to do it when they're logging in from an "unknown device"?
- Are there other kinds of authorization than just a login? How does this fit in?

Once you've figured this stuff out and have the system working and you've decided on the integration, be sure to enforce these basic rules:

- Limit the user to a small number of tries at the code, maybe one or two. If they still miss it, offer to resend a new code.
- When the user enters the code correctly, be sure to remove it and its related `validation_date` from the database.
- If a certain amount of time has passed since the user record was updated to include the code, resend and update the row with the new code.

Outside of these considerations, you can integrate it into your application however you see fit. This is just one route to take, so don't feel like this is the end-all, be-all of implementations. It's just here to give you an idea of the flow and pieces that can make it up. I hope it's been helpful and that you can use this method to get up and running using two-factor authentication quickly.

#### Resources

- [Twilio's tutorial on two-factor](https://www.twilio.com/docs/howto/two-factor-authentication)
- [Wikipedia on multi-factor authentication](https://en.wikipedia.org/wiki/Multi-factor_authentication)
- [Guzzle project](http://guzzlephp.org)
- [Composer project](http://getcomposer.org)
- [PHP manual on PDO](http://php.net/pdo)


