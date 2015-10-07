---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Security Policy Evaluation in Laravel with PropAuth
tags: policy,authorization,laravel,propauth,property
summary: Property-based policy evaluation is a more flexible alternative to the usual hard-coded checks
---

Security Policy Evaluation in Laravel with PropAuth
--------------

{{ byline }}

When you think about authentication and authorization checks on users of your system, the first things that come to mind are usually the typical role-based access control types: groups, permissions, passwords, etc. Most RBAC systems out there hard-code these kinds of things and force you into a pattern of checks on these values to ensure user access for a resource or record. This can be somewhat constraining on a complex system where checks can get very complicated very quickly.

There's an alternative to this hard-coded RBAC handling though and it turns the whole system into something much more flexible while still retaining the same groups/permissions/username/etc checks that the typical role-based systems have defined. With property-based evaluation, you open up the checking to any property that the subject (user) has, not just the small set of RBAC checks.

Here's a common scenario where the typical RBAC structure is used: check to see if User #1 is in "group1" and "group2" and has the permission "perm1". That's a pretty simple kind of check that could look something like (pseudo-code here):

```
if ($user->inGroup('group1') && $user->inGroup('group2') && $user->hasPermission('perm1')) { }
```

This is fine but it has two main problems:

- As the evaluation gets more and more complex, the part inside the `if()` can become a tangled up mess and become quite confusing for developers to understand.
- It's not reusable at all. Any place you want to put in the same checks you'd have to copy and paste the code in, making for another lovely maintenance nightmare when permissions need to be changed.

While property-based authorization checks won't solve the reusability problem by themselves, introducing the concept of "policies" will. Policies can be thought of as reusable sets of checks that can be easily pulled and applied across the application, making things more [DRY](https://en.wikipedia.org/wiki/Don%27t_repeat_yourself).

### So where does PropAuth come in?

The [PropAuth](https://github.com/psecio/propauth) library is designed with two goals in mind:

- Making the creation of property-based checks (policies) easier to generate
- Allowing for the easy creation and reuse of these policies simple to integrate

The `PropAuth` engine provides a fluent interface for creating simple or complex policies and evaluating them against the current user. For example, if you just wanted to check and see if a user has a certain username, you could use:

```php
<?php
$result = Enforcer::instance()->evaluate($user, Policy::instance()->hasUsername('ccornutt'));
?>
```

This assumes that the user's "username" value is accessible either as a public property, through a `getUsername` method or a call to `getProperty('username')`. The value in `$result` will either be `true` or `false` depending on the evaluation results.

That's a simple example, but things can get a lot more complex with chained checks to build a more robust policy:

```php
<?php
$policy = Policy::instance()
    ->hasUsername(['ccornutt', 'ccornutt1'], Policy::ANY)
    ->hasPermissions(['perm1', 'perm2'], Policy::ALL)
    ->notPermissions('perm3')
    ->hasGroup(['group1', 'group2', 'group3']);

$result = Enforcer::instance()->evaluate($user, $policy);
?>
```

Here's what the above policy defines:

- The username should match at least one of "ccornutt" or "ccornutt1"
- The user should have both permissions "perm1" and "perm2"
- The user should not have permission "perm3"
- The user should have at least one group in "group1", "group2" or "group3"

All of the evaluation of this policy against the user is internal to the library, reducing the need for the copy/paste-ing of complex `if` checks across the codebase. It also prevents complex evaluation with multiple "gates" (if checks) as the user trickles down through the code.

Now that you've gotten a taste of the tool, lets look at a more full example: implementing it in a Laravel-based application as a provider. This allows for the definition of policies/policy set and easy evaluation inside of a controller.

### Installation

Installing the library is easy with [Composer](http://getcomposer.org). Use this command to have it require it as a part of your application:

```
composer.phar require psecio/propauth
```

This will pull in a stable release of the library and do all the autoload magic that Composer does to make it available in the application.

### A Simple Example

The `PropAuth` engine allows you to define policies that are as complex or simple as you'd like, depending on your needs. Since it's based on the properties the subject under evaluation has, there's no hard-coded methods for things like groups, properties or even username. The examples here check those things because they're pretty common across authentication/authorization systems, but the properties could be just about anything. The tool uses PHP's magic method handling to relate the "has" and "not" checks, so a method like `hasUsername` works with a `username` parameter but `hasAddress1` might work with an `address1` property on the user without any direct modification to the library.

Here's a simple example of how the different pieces fit together in the library:

```
<?php
require_once 'vendor/autoload.php';

use \Psecio\PropAuth\Enforcer;
use \Psecio\PropAuth\Policy;

$enforcer = new Enforcer();
$myUser = (object)[
	'username' => 'ccornutt',
    'permissions' => ['test1']
];

$myPolicy = new Policy();
$myPolicy->hasUsername('ccornutt');

$result = $enforcer->evaluate($myUser, $myPolicy); // $result === true

// You can also chain the evaluations to make more complex policies
$myPolicy->hasUsername('ccornutt')->hasPermissions('test1'); // also true
?>
```

This code will look similar to the example above, it's just a bit more complete. Our sample user is a basic `stdClass` object with `username` and `permissions` properties publicly accessible. That's then injected into the `Enforcer` along with the simple policy examples, the first just checking the username and the second looking for the username + permission combination.


### Implementing in a Laravel Provider

Now we'll move on to implementing this setup in a Laravel application. First we'll need to define the provider in our application to set up our policies. You can create this file in `app/providers/PolicyServiceProvider.php`. This example include a simple check with the "has" function and a more complex evaluation with the use of a closure:

```
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Psecio\PropAuth\Enforcer;
use Psecio\PropAuth\Policy;
use Psecio\PropAuth\PolicySet;

class PolicyServiceProvider extends ServiceProvider
{
    public function register()
    {
    	$this->app->singleton('policies', function($app) {
    		$set = PolicySet::instance()
    			->add('can-edit', Policy::instance()->hasUsername('ccornutt'))
    			->add('can-delete', Policy::instance()->can(function($subject, $post) {
						return ($subject->username == 'ccornutt' && $post->author == 'ccornutt');
    				})
    		);

    		return Enforcer::instance($set);
    	});
    }
}
?>
```

This provider sets up a group of policies inside of a singleton that's injected into the application wide `app` container under the `policies` name. Inside this singleton I've defined two things: a `PolicySet` and an `Enforcer`.

In the example above I'm defining two policies: `can-edit` and `can-delete`. The first one just does a simple check to see if the subject (user) provided as a username of "ccornutt". This is the `can-edit` policy. When this policy is checked, as shown in the example below, it passes if the `username` property on the subject is "ccornutt". If not, the evaluation fails.

The second policy is a bit more complex. The `can-delete` policy makes use of a closure for its evaluation allowing for more custom logic than just the evaluation of simple properties. When closures are used as a policy, the first parameter (here it's `$subject`) is always the subject of the evaluation, usually the user in question. If you skip ahead a bit down to the example at the bottom of this article you'll also notice something else you can do that's handy - pass in a third option on the `allows` method that provides more details to the closure. For example:

```
<?php
// If our "allows" check is
\App::make('policies')->allows('can-edit', Auth::user(), ['test', 'this']);

// Then the parameters passed into the closure
function($subject, $string1, $string2) {
    return false;
}
?>
```

In this example the value in `$string1` would be "test" and the value of `$string2` is "this". Each value defined in that third parameter on `allows` is passed in as an individual parameter. This Much like the pass/fail logic of the other checks in a normal policy, these closures should return a boolean indicating the result of the validation. Now, back to our example. In the `can-delete` closure above we've been given the `$subject` and `$post` values, objects in this case. It then checks to be sure that the subject's `username` value matches the post's `author` value and returns true if there's a match, passing the validation.

#### Updating the Laravel Configuration

You'll also need to update your Laravel app's configuration to load in this new provider. Because of how the provider system works in Laravel, this provider will be loaded on every request so the policies will be available, even on console commands. To add the provider, update the `config/app.php` and add it to the `$providers` array:

```php
<?php
$providers = [
    // Other Service Providers
    App\Providers\PolicyServiceProvider::class
];
?>
```

Remember, this will load this provider every time you execute a script, command line or web request, so be sure and think about any performance concerns there.

#### Using the Policy Checks in a Controller

Finally we get to the use of the policy checks in a controller. Remember how we set up the singleton in the provider's `register` method to create the policies and return an `Enforcer` as "policies"? We can use the `\App` facade to grab this singleton and use the `Enforcer` it returns to check our current user. This is obviously post-login on the application (otherwise the user is null) but because the default Laravel user handling allows us to directly access properties on the object, PropAuth works happily with it.

In this example we're using the policies defined above to evaluate the current user and current "Post" object. The `$post` here is just a made up `stdClass` instance but it gives you an idea of how things are handled.

```
<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Support\Facades\Auth as Auth;

class TestController extends Controller
{
	public function showTest()
	{
		$result = \App::make('policies')->allows('can-edit', Auth::user());
		var_export($result); // true for a normal check

		$post = (object)[ 'author' => 'ccornutt'];
		$result = \App::make('policies')->allows('can-delete', Auth::user(), [$post]);
		var_export($result); // true for a closure-based check

		return view('test/test');
	}
}

?>
```

In both checks we fetch the `Enforcer` with the `\App::make` call and call the `allows` method with two parameters: the name of the policy to evaluate and the user instance to use as the subject. But wait, what's that third parameter on the second check? That's the "additional information" that you want to pass into the closure call. If you'll remember our closure structure:

```
<?php
function($subject, $post) {
    return ($subject->username == 'ccornutt' && $post->author == 'ccornutt');
})
?>
```

You'll notice that the `$subject` is passed in (as it always is) but the `$post` value is too. Behind the scenes PropAuth calls the closure with a [call_user_func_array](http://php/call_user_func_array) and these additional options are passed in as normal parameters. You can pass in any number of values through that third parameter and they'll happily be pushed into the callable in the same order they're injected.

Now, back to our controller example. The `can-edit` check returns true because the policy was checking for a username value of "ccornutt" and the `can-delete` check also returns true because the author on the `$post` and the username on the subject match. Easy, right?

### Summary

So, to wrap things up - the [PropAuth](https://github.com/psecio/propauth) library provides you with a tool to define policies and easily reusable validation against properties on your subject (user) rather than being forced into a role-based access control model of permissions and groups. It's extremely flexible and, as I've shown here, can make for single-line checks in your code and consistency across the entire application.

This last point is particularly key as it's specifically called out as a major issue in authentication and authorization systems. If your system is overly complex and you forget to update even just one auth* check in one spot in your application, it's possible for a would-be attacker to find that hole and exploit it to their benefit. Remember, complexity is the enemy of security - simpler is better and with the help of [PropAuth](https://github.com/psecio/propauth) simplicity is more in reach.

#### Resources

- [PropAuth on GitHub](https://github.com/psecio/propauth)
- [OWASP on Broken Authentication and Session Management](https://www.owasp.org/index.php/Top_10_2013-A2-Broken_Authentication_and_Session_Management)


