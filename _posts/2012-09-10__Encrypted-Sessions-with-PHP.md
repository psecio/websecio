---
layout: default
author: Chris Cornutt
email: ccornutt@phpdeveloper.org
title: Encrypted Sessions with PHP
---

Encrypted Sessions with PHP
--------------

{{ byline }}

When working with sessions in PHP, the data that lives inside of them needs to be taken
into account. Depending on the application, there may or may not be sensitive data living
inside of them. If there is, you'll need to come up with a better way of protecting that
data than just the regular session handling methods - something with a little cryptography
involved.

> **NOTE:** This method uses a pre-PHP 5.4 release kind of session handler. In 5.4 the
[SessionHandler](http://www.php.net/manual/en/class.sessionhandler.php) and
[SessionHandlerInterface](http://www.php.net/manual/en/class.sessionhandlerinterface.php) classes
were introduced as a way to standardize (more formally) how custom session handlers should work.

By default, PHP takes the values in your sessions and serializes them to be put in a text
file in the `session.save_path` for your application. This is fine if you're only putting
non-sensitive data into your sessions, but could be a major issue is there's something you
need to protect. It's even worse on a shared hosting environment (as was [mentioned in another
post](/2012/08/24/Shared-Hosting-PHP-Session-Security.html)) where anyone running under the
same web server user can potentially access your session files...or even inject content into
them, bypassing security restrictions completely.

So, what's a good solution that's relatively simple and doesn't involve a lot of effort on your
part to accomplish? Easy - encrypting the data that goes into and comes out of your session!
To get the ball rolling, here's an example of an ecrypted session handler that protects the
data with Rijndael 256 encryption. While these last two aren't to be relied on solely as
protection from session hijacking, they're a simple layer of protection that can help block
some of the more basic attacks on your site.

#### The Code

Here's the code, an explanation will follow:

`
<?php
class Session extends Base
{
    /**
     * Path to save the sessions to
     * @var string
     */
    private $savePathRoot = '/tmp';

    /**
     * Save path of the saved path
     * @var string
     */
    private $savePath = '';

    /**
     * Salt for hashing the session data
     * @var string
     */
    private $key = '282edfcf5073666f3a7ceaa5e748cf8128bd53359b6d8269ba2450404face0ac';

    /**
     * Init the object, set up the session config handling
     *
     * @return null
     */
    public function __construct()
    {
        session_set_save_handler(
            array($this, "open"), array($this, "close"),  array($this, "read"),
            array($this, "write"),array($this, "destroy"),array($this, "gc")
        );

        $this->savePathRoot = ini_get('session.save_path');
    }

    /**
     * Encrypt the given data
     *
     * @param mixed $data Session data to encrypt
     * @return mixed $data Encrypted data
     */
    private function encrypt($data)
    {
        $ivSize  = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $iv      = mcrypt_create_iv($ivSize, MCRYPT_RAND);
        $keySize = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $key     = substr(sha1($this->key), 0, $keySize);

        // add in our IV and base64 encode the data
        $data    = base64_encode(
            $iv.mcrypt_encrypt(
                MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv
            )
        );
        return $data;
    }

    /**
     * Decrypt the given session data
     *
     * @param mixed $data Data to decrypt
     * @return $data Decrypted data
     */
    private function decrypt($data)
    {
        $data    = base64_decode($data, true);

        $ivSize  = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $keySize = mcrypt_get_key_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_CBC);
        $key     = substr(sha1($this->key), 0, $keySize);

        $iv   = substr($data, 0, $ivSize);
        $data = substr($data, $ivSize);

        $data = mcrypt_decrypt(
            MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_CBC, $iv
        );

        return $data;
    }

    /**
     * Set the key for the session encryption to use (default is set)
     *
     * @param string $key Key string
     * @return null
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * Write to the session
     *
     * @param integer $id   Session ID
     * @param mixed   $data Data to write to the log
     * @return null
     */
    public function write($id, $data)
    {
        $path = $this->savePathRoot.'/'.$id;
        $data = $this->encrypt($data);

        file_put_contents($path, $data);
    }

    /**
     * Read in the session
     *
     * @param string $id Session ID
     * @return null
     */
    public function read($id)
    {
        $path = $this->savePathRoot.'/'.$id;
        $data = null;

        if (is_file($path)) {
            // get the data and extract the IV
            $data = file_get_contents($path);
            $data = $this->decrypt($data);
        }
        return $data;
    }

    /**
     * Open the session
     *
     * @param string $savePath  Path to save the session file locally
     * @param string $sessionId Session ID
     * @return null
     */
    public function open($savePath, $sessionId)
    {
        // open session, do nothing by default
    }

    /**
     * Close the session
     *
     * @return boolean Default return (true)
     */
    public function close()
    {
        return true;
    }

    /**
     * Perform garbage collection on the session
     *
     * @param int $maxlifetime Lifetime in seconds
     * @return null
     */
    public function gc($maxlifetime)
    {
        $path = $this->savePathRoot.'/*';

        foreach (glob($path) as $file) {
            if (filemtime($file) + $maxlifetime < time() && file_exists($file)) {
                unlink($file);
            }
        }

        return true;
    }

    /**
     * Destroy the session
     *
     * @param string $id Session ID
     * @return null
     */
    public function destroy($id)
    {
        $path = $this->savePathRoot.'/'.$id;
        if (is_file($path)) {
            unlink($path);
        }
        return true;
    }
}
?>
`

If you've never done any kind of custom session handling in PHP before, this setup might
look a little foreign to you. In most situations, the default session handling is "good enough"
for what an application needs. There's not really anything sensitive happening there. Thankfully,
though, when you need a little bit more out of your session handling than the defaults, PHP
is there to lend a hand. The language allows for custom session handling classes that let
you redefine the default methods for things like read, write or create with your own methods
for dealing with the data.

In the example above, you can see that there are definitions for functions like `read`, `write`,
`close` and `gc` - all default session handling methods. These are all connected to the current
application's session handling method via that call to `session_set_save_handler` in the
constructor of the class. This assigns the different methods of the class to the different actions
of the process. PHP then knows to run the data read/write for session information through these
methods.

#### The Methods

Let's start from the beginning and work our way out - the `open` method is one of the simplest in
the class, thankfully. It doesn't really do much as opening a session doesn't have much to do
with encrypting the contents of it. The actual file for the encrypted contents is created elsewhere,
otherwise we'd put that part in there. PHP passes in a path to save the file to (auto-generated) and
the new session ID if you want to do some custom handling there.

The next step in the handling of the session is the `read` method. This is where some of the magic
of the encryption comes in. As you can see, the `read` method takes the session ID and reads in the
file data that's inside. This data is then passed off to the `decrypt` method for handling and checking.

#### A bit about Rijndael (256)

Let's stop for a second and describe the architecture of how we're using Rijndael 256 in our handling.
This level of encrypton (let's just call it "R256" for brevity's sake) was inevented by two Bellgian
cyrptographers, Joan Daemen & Vincent Rijmen, as a part of the [Advanced Encryption Standard (AES)](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard) project. This project, the successor to the DES standard, is
a U.S. Federal Government standard level of encryption and is used in some cases by the NSA.

The block size in Rinjndael is 128 bits and the block size can be either 128, 192 or - what we're using -
256 bits (as used in AES). There's a lot of factors that go into the calculation of the algorithm and,
if you're interested, you can check out [this page on Wikipedia](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
for all the gory details. There are some known attack vectors on this encryption but the actions it performs
operate on a low RAM, high speed level so it's pretty well suited to work in our session handler.

#### Now, back to your regularly scheduled code

Now that you have a little bit of context around the R256 level of encryption, let's take a look at how
it's implemented. When our session is opened, the handler immediately tries to read from the data that may
or may not be there. Obviously, with the initializing of a new session, the file won't be there, so
there's nothing to read from. The custom session handler doesn't care about this and keeps moving
on if there's nothing there.

The first time it tries to write to the session, though, the data passes through the `encrypt` method before
it finally ends up in the file. Here's what that method does before it finishes:

1. Gets the correct [Initialization Vector size](http://en.wikipedia.org/wiki/Initialization_vector) for our
level of encryption. In the case of R256, that IV size (with the CBC mode) is `32`.
2. This length is then used to randomly generate the IV with the `mcrypt_create_iv`function.
3. The keysize for R256 is then found (`32` again) and is used, along with the key defined in the class, to
create a key for use in the [mcrypt_encrypt](http://php.net/mcrypt_encrypt) function call.
4. The generated IV and R256-encrypted data is then appended together and `base64_encoded` and returned to
the session.

Why, do you ask, did we include the IV along with the data in the session? Well, the trick is that, when you
use the code to generate the IV from the IV key length each time, you're going to get a different IV value.
Since one IV was used to encrypt the data in the first place, you need that same one to be able to decrypt
it on the other side.

> **NOTE:** Several of the major frameworks handle their session encryption this way too. They embed the IV into
the session data to make it easier to retrieve when the data is pulled back out. Since it's only a small piece of
the information used to encrypt the data, it's relatively safe.

Then we move along to the other major operation of the session handler - the `read`. It's pretty much just doing
what we did with the `write` only in reverse. In the `read` method, the session handler [base64_decodes](http://php.net/base64_decode)
the data and, based on another call to [get the key size](http://php.net/mcrypt_get_key_size)
to get the size of the key, the string of that length is split off of the data as the IV and the rest is the
actual session data.

There's a few other getters and setters that could probably be thrown into the class to help with
customizing some of the settings like the key used for the encryption or the session save path, but
it's a pretty complete example. The way that it's set up, you can also switch out the type of encryption
with whatever you want and the IV size will be automatically detected and appended correctly.

##### Resources
- PHP manual for [session_set_save_handler](http://www.php.net/manual/en/function.session-set-save-handler.php)
- Details about [Rinjndael & AES](http://en.wikipedia.org/wiki/Advanced_Encryption_Standard)
- [Initialization vector](http://en.wikipedia.org/wiki/Initialization_vector)

