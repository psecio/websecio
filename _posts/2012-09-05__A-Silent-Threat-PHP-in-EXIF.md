---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: A Silent Threat - PHP in EXIF
---

A Silent Threat - PHP in EXIF
--------------

{{ byline }}

<div class="notice"><b>!</b> Despite several of the resources for this issue being older, it is still **very much** an issue
to consider when allowing file uploads.</div>

Anyone who has done anything with file uploads knows that there's a lot to take into
consideration when you're allowing your users to upload their own files up to the server.
We've [already covered some of the things](2012/08/21/Effective-Upload-Handling-Tips.html)
you can do to help prevent some of the most common problems (bad MIME types, whitelisting
file types, etc) but there's another one to consider that wasn't mentioned before. This
"silent threat" comes in the form of PHP code embedded into the EXIF data on an image - jpg,
gif, whatever. Since PHP only really has detection for things like MIME type, checking into
the headers of uploaded images is difficult. Thankfully, there's a simple way to protect
you and your application - don't use `include` (or the like) to load images into your site.

#### But first, the hack...

This sort of attack takes a little preparation on the part of the attacker, but not very
much. It's a minimal amount of effort for something that could lead to just about any
kind of security vulnerability - XSS, RFI, even arbitrary code execution. Really, that
last one is where the problem lies. Because of the way that PHP handles things with the
[include](http://php.net/include) statement and its cousins, there's a trick that you
can do with the image's EXIF data to have it execute whatever PHP code you choose.

1. **Grab the image of your choice:** The image itself dosen't have to be anything special.
As long as you can edit the EXIF data, this exploit will work (as long as `include` is used).

2. **Open the image in an EXIF editor and add a new tag:** With your editor of choice,
open up the file's current metadata and add one of your own - a tag called `DocumentName`
and give it the value of the PHP you'd like to execute. So, for example, with the `exiftool`
command line utility, you could use:

  `exiftool -documentname='<?php echo "foobarbaz"; ?>'`

  This will add the new tag to the image and save it back out, ready for the upload.

3. **Upload the image to the server and access:** You can test things out to see if the
service is just using `include` to pull in the image file or if they're using a more
secure method, like something using [file_get_contents](http://php.net/file_get_contents).

That's it...this deceptive and simple hack could cause some pretty big headaches for you,
as a developer and as a sysadmin. Because the image file is included by the web server user,
your code embedded in the image file is executed with those permissions. This is usually more
than enough to cause some problems, though.

#### Preventing the Problem

The quick and dirty solution to the problem is, thankfully, just as simple as the attack itself.
When pulling in image content into your pages, avoid using the inclusion functions (`include`,
`require`, `include_once`, etc).

The reasoning behind this is cited in the [PHP manual](http://us3.php.net/manual/en/function.include.php):

> When a file is included, parsing drops out of PHP mode and into HTML mode at the beginning of the
> target file, and resumes again at the end. For this reason, any code inside the target file which
> should be executed as PHP code must be enclosed within valid PHP start and end tags.

This handling causes PHP to see the injected code as something to be executed and runs it immediately.
So, how can you get the contents of your images into your pages without including them directly?
Fortunately, it's another easy answer: using the filesystem functions (like `file_get_contents`) to
read the image data and import it directly.

Here's a simple example of one way to do it - to use a data URI to embed the content directly into
the page:

`
<?php
$contents = file_get_contents('/path/to/files/image1.jpg');
echo '<img src="data:image/jpeg;base64,'.base64_encode($contents).'">';
?>
`

Of course, you should be careful not to allow the user to specify the path to the file. This
could open the door to LFI/RFI or other possible exploits. The code contained inside the image
is executed as if it was a local PHP script. Whatever permissions the web server user has, this
script will as well - including the ability to pull in other, more dapaging scripts and execute
them locally.

Some articles suggest the use of the [getimagesize](http://php.net/getimagesize) function as a
first line of defense. This is a good way to detect if an image is corrupted or not, but does
nothing to prevent the EXIF injection described here.

There's another approach that could be taken as well if you're very concerned about anything getting
past - you could use the [exif_read_data](http://php.net/manual/en/function.exif-read-data.php) function
in PHP to go through all of the headers and see if there's PHP code in any of them. This is great...if
it's available. The exif extension isn't one of the defaults for a lot of the distributions out there
so take that into consideration when planning your application's upload functionality.

#### Uploads in the Document Root (a Worst Practice)

This EXIF topic also brings up a related something that can be considered a very bad security practice when
it comes to storing user-provided content. **NEVER** place user uploaded content anywhere inside of the document
root of the application. Having the files where they can be accessed directly offers no protection from
the potential contents of the file. All user input, files or otherwise, should be sanitized and sectioned
off to prevent any possible exploits. This also gives you a chance to include some sort of access control
to the files too...


##### Resources
- [PHP Image Upload Security: How Not to Do It](http://nullcandy.com/php-image-upload-security-how-not-to-do-it/)
- [PHP Security Exploit with GIF Images](http://www.phpclasses.org/blog/post/67-PHP-security-exploit-with-GIF-images.html)
