#!/usr/bin/env php
<?php
/**
 * Use the "genr8" command to build the posts and static pages
 * @author Chris Cornutt <ccornutt@phpdeveloper.org>
 */
include_once 'vendor/autoload.php';
include_once 'Genr8/Loader.php';

define('APPPATH', __DIR__);

$loader = new Genr8\Loader();

class Build extends \Genr8\Parse
{
    public function preCompile($data)
    {
        list($data,$this->options) = $this->parseHeader($data);

        if (isset($this->options['layout'])) {
            // apply the layout
            $path = APPPATH.'/_layouts/'.$this->options['layout'].'.html';

            if (is_file($path)) {
                $this->template = $path;
            }
        }

        return $data;
    }
    public function postCompile($data)
    {
        return $data;
    }
    public function export($data, $filename)
    {
        echo '['.date('m.d.Y H:i:s').'] > Exporting to '.APPPATH.'/'.$filename.'!'."\n";

        $this->makePostDir(APPPATH.'/'.$filename);
        file_put_contents(APPPATH.'/'.$filename, $data);
        $this->template = '';
    }
    //-----------
    private function parseHeader($data)
    {
        $options = array();
        $ret = preg_match('#^[\-]{3}(.*?)[\-]{3}#ms', $data, $matches);

        if ($ret == 1) {
            $data   = str_replace($matches[0], '', $data);
            $header = explode("\n", trim($matches[1]));

            foreach ($header as $line) {
                $pos = strpos($line, ':');
                $p[0] = substr($line, 0, $pos);
                $p[1] = substr($line, $pos+1);

                //$p = explode(':',$line);
                $options[trim($p[0])] = trim($p[1]);
            }
        }

        return array($data,$options);
    }
    private function makePostDir($path)
    {
        $p = explode('/', str_replace(APPPATH, '', $path));
        $filename = array_pop($p);
        $c = '';

        foreach ($p as $dir) {
            if (empty($dir)) {
                continue;
            }
            $c .= $dir.'/';
            if (!is_dir($c)) {
                mkdir($c);
            }
        }
    }
}

// LET'S GO! ---------------
$env = (isset($_SERVER['argv'][1])) ? $_SERVER['argv'][1] : 'prod';

echo '['.date('m.d.Y H:i:s').'] Generating site!'."\n";
define('ENV', $env);

// make the "_site" directory and copy over the .htaccess
if (is_dir('_site')) { exec('rm -rf _site'); }
mkdir('_site');
copy('_static/.htaccess','_site/.htaccess');

// copy over the assets
exec('cp -r _static/assets _site/assets');

// look in _posts and file the *.md files
$p = new Genr8\Posts();
$posts = $p->find();

// build each of the posts
foreach ($posts as $post) {
    $bp = new Build();
    $bp->includeComments = true;

    foreach ($post as $name => $d) {
        $bp->addData($name, $d);
    }
    $result = $bp->compile($post['file']);
    $bp->export($result, '_site'.$post['url']);
}

// populate the index page with links
$b = new Build();
$b->addData('links', $posts);
$b->export($b->compile('_static/index.md'), '_site/index.php');

// build our "About" page
$b->export($b->compile('_static/about.md'), '_site/about.php');

// build the "resources" page
$b->export($b->compile('_static/resources.md'), '_site/resources.php');

// build the "Glossary" page
$b->export($b->compile('_static/glossary.md'), '_site/glossary.php');

// build the 404 page
$b->export($b->compile('_static/notfound.md'), '_site/notfound.html');

// build the feed with the latest report details
$b->addData('pubDate', date('r'));

foreach ($posts as $index => $post) {
    $posts[$index]['title'] = htmlentities($post['title']);
}

$b->addData('links', $posts);
$b->export($b->compile('_static/feed.md'), '_site/feed.xml');

echo '['.date('m.d.Y H:i:s').'] Generation complete!'."\n";
?>
