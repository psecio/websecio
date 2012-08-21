<?php

namespace Genr8;
use dflydev\markdown\MarkdownExtraParser;

class Parse
{
    protected $twig         = null;
    private $templateData   = array();
    public $template        = '';
    public $includeComments = false;

    public function __construct()
    {
        $loader = new \Twig_Loader_String();
        $this->twig = new \Twig_Environment($loader, array('autoescape'=>false));
    }

    private function load($data)
    {
        // see if it's abolute first
        if (is_file($data)) {
            $data = file_get_contents($data);
        } elseif (is_file(APPPATH.'/'.$data)) {
            $data = file_get_contents($data);
        }
        return $data;
    }

    public function addData($name,$value)
    {
        $this->templateData[$name] = $value;
    }

    public function applyTemplate($data)
    {
        if (!empty($this->template)) {
            $template = file_get_contents($this->template);

            // see if we need to show comments too...
            if ($this->includeComments == true) {
                $data['showComments'] = true;
            }
            $data = $this->twig->render($template,$data);
        }
        return $data;
    }

    public function compile($data)
    {
        echo '['.date('m.d.Y H:i:s').'] Compiling '.$data.'!'."\n";

        $data = $this->load($data);

        // see if we have a preCompile
        if (method_exists($this, 'preCompile')) {
            $data = $this->preCompile($data);
        }

        // compile the data from Markdown to HTML
        $mp = new MarkdownExtraParser();
        $data = trim($mp->transformMarkdown($data));

        // run it through twig first
        $data = $this->twig->render(
            $data,
            $this->templateData
        );

        // merge in our other options
        $d = array_merge(
            array('content'=>$data), 
            $this->options
        );
//print_r($d);

        $data = $this->applyTemplate($d);
//print_r($data);

        $data = $this->formatCode($data);
        $data = $this->formatRss($data);

        if (method_exists($this, 'postCompile')) {
            $data = $this->postCompile($data);
        }

        return $data;
    }

    private function formatCode($data)
    {
        // see if we have code and un-escape the content
        if (is_array($data)) {
            $data = $data['content'];
        }
        preg_match_all('#<code>(.*?)<\/code>#ms',$data,$matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $ct = count(explode("\n",$match));
                if ($ct>2) {
                    $data = str_replace(
                        $match,
                        '<pre class="code">'.str_replace(
                            array('<code>','</code>','<?php'),
                            array('','','&lt?php'),
                            $match
                        ).'</pre>',
                        $data
                    );
                }
            }
        }

        return $data;
    }
    private function formatRss($data)
    {
        return str_replace(
            array('<p><?xml','</rss></p>'),
            array('<?xml','</rss>'),
            $data
        );
    }
}
