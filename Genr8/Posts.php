<?php

namespace Genr8;

class Posts
{
    private $postDir  = null;
    private $postData = array();

    public function __construct()
    {
        $this->postDir = APPPATH.'/_posts';

        $loader = new \Twig_Loader_String();
        $this->twig = new \Twig_Environment($loader, array('autoescape'=>false));
    }

    /**
     * Find the posts
     *
     * @param int $after Timestamp to find posts after
     *
     * @return null
     */
    public function find($after=null)
    {
        $dir = new \DirectoryIterator($this->postDir);
        foreach ($dir as $fileinfo) {
            if (!$fileinfo->isDot() && $fileinfo->getExtension() == 'md') {

                $fileParts = explode('__', $fileinfo->getFilename());
                $postPath  = implode('/', explode('-', $fileParts[0]));

                // only find the ones that are recent (and not future dated)
                if ($after == null) { $after = strtotime('-7 days'); }
                /*if (strtotime($fileParts[0]) < $after) {
                    continue;
                }*/
                if (ENV !== 'dev' && strtotime($fileParts[0]) > time()) {
                    continue;
                }

                // parse the header
                $contents = file_get_contents($this->postDir.'/'.$fileinfo->getFilename());
                list($data,$options) = $this->parseHeader($contents);

                $tags = (isset($options['tags'])) ? explode(',',$options['tags']) : array();
                foreach ($tags as $index => $tag) {
                    $tags[$index] = trim($tag);
                }

                $byline = $this->twig->render(file_get_contents(APPPATH.'/_layouts/byline.html'),
                    array(
                        'author' => $options['author'],
                        'posted' => $fileParts[0],
                        'tags'   => $tags
                    )
                );

                // populate the data
                $pub = strtotime($fileParts[0]);

                $this->postData[$pub] = array(
                    'title'    => $options['title'],
                    'file'     => $this->postDir.'/'.$fileinfo->getFilename(),
                    'posted'   => $fileParts[0],
                    'url'      => '/'.$postPath.'/'.str_replace('.md','.html',$fileParts[1]),
                    'content'  => $data,
                    'author'   => $options['author'],
                    'email'    => $options['email'],
                    'byline'   => $byline,
                    'pubdate'  => date('r',strtotime($fileParts[0])),
                    'tags'     => (isset($options['tags'])) ? explode(',',$options['tags']) : null,
                    'summary'  => (isset($options['summary'])) ? $options['summary'] : null
                );
            }
        }

        krsort($this->postData);
        return $this->postData;
    }

    //---------
    private function parseHeader($data)
    {
        $options = array();
        $ret = preg_match('#^[\-]{3}(.*?)[\-]{3}#ms',$data,$matches);

        if ($ret == 1) {
            $data   = str_replace($matches[0],'',$data);
            $header = explode("\n",trim($matches[1]));

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
}
