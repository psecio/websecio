---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Casting Your Net: Securing Your Site with Skipfish
summary: See how to use this simple tool to help find flaws in your applications.
---

Casting Your Net: Securing Your Site with Skipfish
--------------

{{ byline }}

Google has released a very handy tool that can help make the arsenal of any web developer 
trying to make the security of their applicatons more robust - [Skipfish](http://code.google.com/p/skipfish/).
Here's the official description:

> Skipfish is an active web application security reconnaissance tool. It prepares an 
> interactive sitemap for the targeted site by carrying out a recursive crawl and 
> dictionary-based probes. The resulting map is then annotated with the output from a 
> number of active (but hopefully non-disruptive) security checks. The final report 
> generated by the tool is meant to serve as a foundation for professional web 
> application security assessments.

So, basically what Skipfish gives us is an automated way to run some of the most common
security checks against our applications quickly and easily, complete with some detailed
reporting too boot. It's a compiled tool, so its nice and zippy and has [loads of options](http://code.google.com/p/skipfish/wiki/SkipfishDoc) so you can configure it to behave just how you'd like.

Since this is just an introduction, I'll just be leading you through some of the first 
steps with the tool. I'm going to walk you through getting it installed and running a
scan against two sites - one with a good passing score and another that has issues and
will trip the scanner's red flags.

Let's get on with the setup:

* **Download:** Head over to [the Skipfish site](http://code.google.com/p/skipfish/) on Google Code
and grab the latest version. (At the time of this writing, they're at 2.07b). Download
this latest release and unpack the archive on your local machine.

* **Compile Skipfish:** Hopefully you're at least a little familiar with the command line
and know how to run these commands, but thankfully it's a pretty painless process:

1. `cd` into the skipfish directory
2. Build the software by typing `make`
3. Wait. You'll be returned to the prompt when it's done.

> **NOTE:** If you're running on OSX like I am, you might need to install [libdin](http://www.gnu.org/software/libidn/)
> if it complains about it. You'll have to compile it as well.

If all goes well, you'll end up with a `skipfish` executable in the current directory. 
You're ready to use it to test your app. Here's a sample command to get you started:

`./skipfish -o output_dir -S dictionaries/complete.wl -W new_dict.wl http://mysite.localhost`

This will use some of the default dictionaries and run its battery of tests. You'll get a 
"Welcome to skipfish..." message and be prompted to hit any key to start the scan. Once you do
you'll be able to see it executing and finish off with the comment "This was a great day
for science!". You can then look in the output directory (in our case `output_dir`) in
the current directory at the results. It's HTML output, so drag and drop it into your
browser. You should see the Skipfish Results page with some helpful data on it.

Some of the basic information can be found if you click on the address of your site in the 
top first area. This will show you things like any XSS issues, external content, HTTP codes
and lots more. Hopefully, your results will be pretty small.

You can get more detail on the items it found under the "Issue Type Overview" section
near the bottom of the page.

#### Summary

Hopefully this gives you a good idea of how to use the Skipfish scanner to evaluate your site
for some of the most common web application security issues. There's lots of other scanners
out there, but this is one of the simpler and easier to use ones I've seen.

#### Resources

* [Skipfish project page](http://code.google.com/p/skipfish/)