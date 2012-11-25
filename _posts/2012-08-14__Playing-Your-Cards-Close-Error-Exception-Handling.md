---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Playing Your Cards Close - Custom Error & Exception Handling
summary: Default PHP error handling shares way too much information - learn how to use custom handlers to prevent it.
---

Playing Your Cards Close - Custom Error & Exception Handling
------------

{{ byline }}

By default, PHP is an over-sharer. It loves to just hand out information to anyone that
comes knocking and will tell you just about anything about the errors that are thrown.
As developers, having these verbose error messages is great - it helps pin down the exact
spot that the problem is happening, right down to the line number. To a security professional,
though, it's a nightmare. The common error messages in PHP share things like:

- line number where the error happened
- the full path to the file in question
- what kind of error it was

To a would-be attacker, this is a practical goldmine of information about the application.
So, being the resourceful developers we are, there has to be something that we can do 
about it. Thankfully, PHP has some handy functionality built in that can help mitigate
some of these issues - custom error and exception handling.

Thanks to the `set_error_handler` and `set_exception_handler` functions, we can customize
how we want our errors shown to the rest of the world and only expose the information 
we want.

I'm going to start with an example of a class that will redefine both pieces of functionality
at the same time, then come back and do a little explainng:


`
<?php
class ErrorsAreAwesome
{
    private $errorConstants = array(
        1       => 'Error',
        2       => 'Warning',
        4       => 'Parse error',
        8       => 'Notice',
        16      => 'Core Error',
        32      => 'Core Warning',
        256     => 'User Error',
        512     => 'User Warning',
        1024    => 'User Notice',
        2048    => 'Strict',
        4096    => 'Recoverable Error',
        8192    => 'Deprecated',
        16384   => 'User Deprecated',
        32767   => 'All'
    );

    public function __construct()
    {
        set_error_handler(array($this, 'errorHandler'));
        set_exception_handler(array($this, 'exceptionHandler'));
    }

    public function exceptionHandler($exception)
    {
        $message = $exception->getMessage().' [code: '.$exception->getCode().']';
        echo $message;
    }
    public function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $errString = (array_key_exists($errno, $this->errorConstants))
            ? $this->errorConstants[$errno] : $errno;

        echo '<b>'.$errString.':</b> '.$errstr.'<br/>';
        error_log($errString.' ['.$errno.']: '.$errstr.' in '.$errfile.' on line '.$errline);
    }
}

$e = new ErrorsAreAwesome();
?>
`

In the above code example, we use the two PHP functions in our constructor of the `ErrorsAreAwesome`
class to define our custom error and exception handling. If you're not using a class, 
you can just pass in the name of the function that will do the handling. In our case, though.
we're handling things OOP-style, so we need to use the `array()` method for referencing the methods 
in our class. 

The `exceptionHandler` method takes in one parameter - the an object instance of the exception 
that was thrown. If you're using custom exceptions, you could call a `get_class` on this exception
to see which kind it was. We're taking the simple route and just extracting the message and 
error code from the exception and reformatting it to display the minimal content we can.

The `errorHandler` method is a little different - there's a bit more going on here. It takes in 
a lot more values incldding the error code (number), error string that was thrown, the file
that the error occurred in and the line of the code where it all went down. Obviously, these
details aren't somethng you'd liked shared with the average Joe User out there, so I've put in
a bit of reformatting to be sure any error output is sanitized from useful information. Bascially,
when an error is thrown with this handling in place, the user will get a brief description that
"something went wrong" while the web server error log gets an earfull via the `error_log` call.
This gives the web developer a way to still make their diagnosis without having to sacrifice 
the tracability that comes with the full message.

#### Disclaimer: Fatal Errors

One thing to keep under consideration, though - when a **fatal error** is thrown in PHP, the interpreter
doesn't get to those "add custom error and exception handling" setup methods. Fatal errors die 
right when they're found and will still show the full information about the error - file path,
line number and all.

#### Resources

- [PHP.net for set_error_handler](http://us.php.net/set_error_handler)
- [PHP.net for set_exception_handler](http://us.php.net/set_exception_handler)


