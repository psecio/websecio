---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Effective File Upload Handling Tips
---

Effective File Upload Handling Tips
--------------

{{ byline }}

Taking input from a user is tricky. It gets even more difficult when the input you're
taking isn't just a string of text, but a file. There's a whole host of other security 
issues consider when you're planning on letting someone push their own file content up
and onto your server. I wanted to write up a summary of things, a few reminders and maybe
some things you hadn't thought about before, when it comes to handling file uploads in
your application.

I've split it up into a few sections - the ones that I consider *MUSTS* that you absolutely
have to do to be anything close to secure, the *MAYBES* that you can implement if it
fits your situation (but don't have to) and the *DON'T* list. These are the things that
you should **never** do in your file handling code.

#### MUST
1. **Restrict file types**: Chances are you know what kind of content you want your 
users to be able to upload. If your answer to "what file types should I restrict it to?"
is "I'm not sure" you need to sit down and have a good think on it. You're implementing
the file upload for a specific reason and letting users define whatever kinds of files
they want is asking for trouble. Here's an example of checking for the extension in PHP:

    `
    <?php
    $filename = 'test.txt';
    $ext = substr($filename,'-'.(strrpos($filename,'.')+1));
    echo 'Extension: '.$ext;
    ?>
    `

    Obviously this is a pretty simple example and could be pretty open to exploitation with
    things like multiple extensions or strange filenames. So, what's next that can help 
    protect us from the bad guys?

2. **Check the MIME type**: Thankfully PHP has your back on this one too. There's some
built in functions that can take a look at a file (post-upload) and detect what their 
current MIME type is. With the help of the [Fileinfo](http://www.php.net/manual/en/ref.fileinfo.php)
PECL extension, you can find out all sorts of good things about the file the user's just
given you:

    `
    <?php
    $path = '/var/myuploads/test1.txt';
    $mime = finfo_file(finfo_open(FILEINFO_MIME_TYPE),'test1.txt');

    echo 'MIME Type: '.$mime;
    ?>
    `

    In the above example, since the file we're working with is actually a text file,
    we'd get back `text/plain`. This way, even if we rename the `test1.txt` file to something
    like `test1.jpg`, the mime type will stay the same. (You'll need PHP 5.3.x or later
    to make native use of this functionality. If you're on an older version, check out
    the [PECL page](http://pecl.php.net/package/fileinfo) for the extension.)

3. **Validate the filename**: Yes, I know this sounds similar to #1, but hear me out. 
When the user submits a file to be uploaded, it's just a POST request with some extra
multipart field and content handling. Since it's mostly a text-based request, there's 
nothing saying that a malicious user couldn't fake one and set the name of the file to
something to further their evil plans. Be sure that what they're giving you is a real
filename and not something like:

    ~~~
    "../../../tmp/test1.txt"
    OR
    "php://input"
    ~~~

    Chances are, if you see a filename that's not really a filename, someone's messing
    with your form, trying to either make things break or extract as much info as they
    can. Be sure to watch for anything that doesn't look like a filename and filter it
    accordingly. You don't want to accidently call a 
    [move_uploaded_file](http://php.net/move_uploaded_file) and overwrite some key system
    file in the process, now do you...

4. **Put uploaded files outside the docroot**: As a genral rule, it's a really good idea
to move the files out of the `/tmp` directory they're in and to a place designated for 
user uploads. This definitely needs to be outside of the document root of the site itself
and can use a "gateway" script to pull in the file's contents and output it back to the 
browser when requested.

    If someone managed to slip a PHP file in on you and they had direct access to it 
    from the browser, you'd be opening up a huge person-sized hole for anyone to walk
    through and gather as much information as they'd like about your application.

5. **Specifically block dangerous files**: This is sort of an addition to #1 and #3 but
a bit more specific than that. There are certain files, like an `.htaccess` file for example,
that could cause some pretty bad damage if they ended up in the wrong spot. Be sure that
you filter this and other files that might directly effect the software the site runs on
and filter them out.

#### MAYBE

1. **Check the referrer**: Since it's not an all together reliable way to determine where
the POST request is coming from - your site or another - it can't be considered a primary 
prevention method, but it can be helpful as a "first check" to be sure it's coming from
the right place. Obviously, since the Referrer is 
[something the user can change](/2012/08/11/Can't-Trust-the-$_SERVER.html) you can't trust
it very far, but it can help cut down on spam submissions if you're getting bombarded
from other sites.

2. **Rename the files**: Depending on your application, you might want to consider renaming
the files once they've been uploaded. Using some sort of internal naming scheme not only
prevents a user from directly accessing the file, but it can also help to make your
app easier to maintain since you don't have to worry so much about the filename they
give you anymore. Now your only worry is in getting the right extension for the file 
and tracking its relation to the user.


#### DON'T

1. **Trust the MIME type**: Remember what I said up in "MUST #2" about checking the MIME
type to see what the kind of file truely is when it's uploaded? Well, here's a caveat -
absolutely do not treat that file differently based on the contents of that MIME type.
If you care anything about the security of your application, you'll force the user's 
browser to download the file's contents for every file you serve:

    `
    <?php
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="uploaded.pdf"');
    readfile('source.pdf');
    ?>
    `

    In this example, we're working with what we've been told is a PDF file but we can't 
    be 100% sure. So, we take the (filtered, of course) information we know about the file 
    - it's name and the supposed Content-Type - and use the `Content-Disposition: attachment;`
    header to force a download. This code would be placed in the proxy script you'd use
    to pull the file data from a non-docroot directory (remember MUST #4).

2. ***Allow the user to upload any file**: I know this was mentioned in MUST #1, but it 
bears repeating. You have to be absolutely sure that you're limiting your users to the types
of files they should be uploading. Dropping an upload form element onto a page without 
restrictions might as well be asking for you to turn your server into a fileshare.

#### Summary

There's a few other things to worry about when uploading files, some related to PHP specifically
and how it handles its uploads, but that's a topic for another day. Hopefully this has been
a useful list of things to consider when you're setting up your file uploads for your
application. If you have any other suggestions, please leave them in the comments below -
shared knowledge is better than [walled gardens](http://en.wikipedia.org/wiki/Walled_garden_(technology))
any day.

#### Resources

* [Fileinfo extension](http://docs.php.net/fileinfo)
* [Handling uploads in PHP](http://php.net/manual/en/features.file-upload.php)
* [OWASP on File Upload Security](https://www.owasp.org/index.php/Unrestricted_File_Upload)
* [The 1x1 Jpeg Hack](http://josephkeeler.com/2009/04/php-upload-security-the-1x1-jpeg-hack/)
