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


Instllation:

```
composer.phar require psecio/propauth
```

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

...explain each piece


### Implementing in a Laravel Provider

First we define the provider in our application to set up our policies. This can include simple checks with the "has" functions or more complex evaluation with the use of a closure:

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

- The second policy is a bit more complex. The `can-delete` policy makes use of a closure for its evaluation


```
// And now it in use in a simple controller
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