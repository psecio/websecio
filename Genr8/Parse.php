<?php

namespace Genr8;
use dflydev\markdown\MarkdownExtraParser;

class Parse
{
    protected $twig         = null;
    private $templateData   = array();
    public $template        = '';

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
            $data = $this->twig->render($template,array('content'=>$data));
        }
        return $data;
    }

    public function compile($data)
    {
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

        $data = $this->applyTemplate($data);

        $data = $this->formatCode($data);

        if (method_exists($this, 'postCompile')) {
            $data = $this->postCompile($data);
        }

        return $data;
    }

    private function formatCode($data)
    {
        // see if we have code and un-escape the content
        preg_match_all('#<code>(.*?)<\/code>#ms',$data,$matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $ct = count(explode("\n",$match));
                if ($ct>2) {
                    $data = str_replace(
                        $match,
                        '<pre class="code">'.str_replace(
                            array('<code>','</code>'),
                            array('',''),
                            $match
                        ).'</pre>',
                        $data
                    );
                }
            }
        }

        return $data;
    }
}
