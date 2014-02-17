---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Versioning Data Validation
tags: version,validation,data
summary: Input validation is a must for any application but changing rules can make it tricky.
---

Versioning Data Validation
--------------

{{ byline }}

If there's one thing about keeping PHP-based web applications safe that bears repeating it's input filtering and validation. There's still a large number of applications out there that don't bother to filter the data coming in from their users. They assume that their front-end controls or examples are good enough to get the user to input the right data. They drop things directly in their database without even considering what it might contain and end up with problems like cross-site scripting and wonder why. Sure, PHP's [prepared statements](http://us3.php.net/manual/en/pdo.prepare.php) can help protect you from things like SQL injection but it can't prevent bad data from making it through.

So, what's a developer to do? Even implementing some simple validation (length, alpha-numeric, is valid email address) can go a long way to helping to improve the data you're storing. There's plenty of validation libraries out there to help make this a simpler task, many that you can install via [Composer](http://getcomposer.org) including one I've mentioned in another post, [Respect's Validation library](/2013/04/01/Effective-Validation-with-Respect.html). While more validation is always better, you have to be careful that you're not making your rules too strict or focusing too much time on edge cases. Remember, the point of validation is to be able to kick back what the user has given you if it's not *exactly* what you're expecting.

#### Tricky validation

There's some data that it's pretty easy to write validation rules for. This is data with formats that won't change over time like phone numbers, zip code formats, etc. You can confidently put these rules in place and not have to worry about updating them. Back in the real world, though, most data will have changing requirements around the data it tries to validate. Take URLs as a common example - they've evolved quite a bit over the years including the addition of several four letter top level domains (TLDs) where they'd only been three letters before. Any validation that wanted to be able to accept these new four-letter TLDs would have to be updated, whether it be a regular expression or some other kind of string handling.

In this example, it's not such a bad update. Expanding a check to be more permissive has a relatively low impact on the data already stored. As long as it's kept backwards compatible, you don't have to worry too much about frustrated users. There's going to be validation rules that aren't this easy, though. It's natural to have validation rules that change over the life of the application. As your code grows and you learn more about the nature of your data more rules will be put in place and more and more checks will need to be updated. Some can just be expanded but others will break.

Knowing this, how can we handle these validations that need to be updated and/or broken to improve the data validation of the application? I've come up with a few suggestions which are by no means the only alternatives. They're just the most popular options I've seen recently.

#### Backwards Compatible

The easiest road to go down when updating validations is to enhance what's already there. If at all possible, take the check that you already have an expand it out to allow for a wider range of matches to take place. Obviously you'll need to figure out how broad is too broad, but that entirely depends on the data. Here's an example of a regular expression for checking US phone numbers, first without the country code and then enhanced to check with it:

`
<?php
// first we'll just match the simple number
$phone = '214-555-1234';
preg_match('/[0-9]{3}\-[0-9]{3}\-[0-9]{4}/', $phone, $matches);

// returns: array(0 => '214-555-1234')
print_r($matches);

// now we match with the country code
$phone = '1-214-564-1014';
preg_match('/([0-9]+\-)?([0-9]{3}\-[0-9]{3}\-[0-9]{4})/', $phone, $matches);

// returns: array(0 => '214-555-1234', 1 => '1-', 2 => '214-564-1014')
print_r($matches);

?>
`

You'll notice that our second regular expression check has been written in such a way that the country code is optional. This way we prevent any breakage from phone numbers currently in the system when the user's information is saved. If the country code does happen to be there, we can validate it accordingly. While this sort of "[progressive enhancement](https://en.wikipedia.org/wiki/Progressive_enhancement)" won't guarantee as much consistency in the data you're storing, it will still all get validated prior to insert/update.

This is also th best option when it comes to user experience. Forcing users to update data they're not currently changing is a bit of a hassle and could leave a bad taste in some users' mouths.

#### A New Version

Another option when the need to change a validation arises is to simply create a completely new version of it and execute both. It's not the most ideal solution but you could create `validateUrl` and `validateUrl1` and if either one passes then the data is valid.

Obviously this isn't an ideal solution either because it requires more computing power to run more than one validation on any given piece of data. And what's to say that it stops with two versions? If the data being verified is something that changes formats quite often (more free-form text has a habit of doing this) you could end up with a long list of `validate*` methods, all running on one piece of data.

Another problem with this solution is the multiple points of failure that's possible. Say you're validating a hostname but it has to be checked against multiple data sources. The first version of your validation only validated the hostname off of one source, but the check needed to be updated and run queries on two other sources. What happens if the check in the first validation fails? What's the most correct way of handling that failure? If you fully subscribe to the ideals of the [fail fast securely](/2012/10/22/Fail-Fast-Securely.html) mentality, you'll fail the validation regardless of the reason. If the service is a remote one, this could even mean that the connection couldn't be made and have nothing to do with the actual data being validated.

#### Replace it with Another

If the idea of running more than one version of the same kind of check on one piece of data isn't overly appealing, there's another option. I've seen several applications that have taken the first version of the validation completely out of the picture and replaced it with a whole other version. Usually this ends up happening when there's a major set of new requirements that come in and updating the old version isn't possible.

"But it's all in version control! Why can't I just update the old one?" you might be thinking. Well, consider this: validation is a tricky thing and sometimes one validation can rely on another to do some of the data checking (in keeping with the [DRY](https://en.wikipedia.org/wiki/Don't_repeat_yourself) development ideals). If this is the case in your application and you make major changes to an existing validation method, there's the possibility that you could break something else and not know it. In a case like this keeping the old validation method around makes sense, at least until the dependencies can be resolved and updated in the other validation classes.

All new data would, of course, be validated using this new method, but you'll still need to be sure it can be validated by the pre-existing function just in case (see the section about backwards compatibility above).

Another replacement option is to shift the validation over to another existing filter. For example, if you had a validation for `maxLength` but wanted to simplify things, you might hand off that responsibility to a current `length` filter and set a maximum length for the string. This could either be made transparent with the `maxLength` being an alias or simply replaced in all instances of `maxLength`.

#### Update the Current Validation

Finally, if all else fails and there's no other alternative, you're going to have to make a choice - leave the check how it is and run more secondary validations or break it and force users to update. It's not a popular option, but it can be for the best. Users can get frustrated pretty quickly by having to update data that might not seem connected ("company URL is required now? but I just wanted to update my password!").

In this kind of situation, you might want to consider a path taken by several validation engines out there, especially those attached to models. The object that contains the data will have a "dirty" flag attached to it. If any data in the set on the object is changed, that flag will be marked as `true` and the field(s) that had changes can be determined. With these fields singled out, you can then only run the validation on those fields, preventing any strangeness that might come from validating the entire object.

#### Storing Versions

One thing that I've seen talked about in some posts and discussion threads is the concept of storing the validation version right along with the data. For example, if you had an older URL you previously validated, it would have a record that contained both the URL and the version number of the "validate URL" check that it passed. This adds in some serious complexity as it requires at least one more piece of meta-data to be stored per piece of data. Add to that the fact that you have to keep *all* of the previous versions of the validations around if you ever want to call them back up and revalidate.

If you already have this kind of system in place, make every effort you can to get users to update their information to the latest validation level. Trust me, you'll thank me for it someday.

#### And finally...

What's my personal preference? Honestly, I'm more in favor of the forced update method of validation. I'd *much* rather always have validate data in my system then have to guess. This may mean upsetting and possible confusing some users when they're presented with a "you must update" message on login, but it's well worth it in my opinion.

#### Resources

[Versioning Does Not Make Validation Irrelevant](http://www.25hoursaday.com/weblog/2006/12/15/VersioningDoesNotMakeValidationIrrelevant.aspx)
