<?php
$layout = file_get_contents('../_layouts/default.html');

// find items matching tagged value
$parts = explode('/', $_SERVER['REQUEST_URI']);
$tag = htmlspecialchars($parts[count($parts)-1], ENT_QUOTES, "UTF-8");

$lines = file('tagged.txt');
$content = '<h2>Matches for "'.$tag.'"</h2><br />';
$matches = array();

foreach ($lines as $line) {
    $parts = explode('|', $line);
    if (strtoupper($parts[0]) == strtoupper($tag)) {
        $matches[] = array(
            'title' => $parts[1],
            'url' => $parts[2]
        );
    }
}

foreach ($matches as $match) {
    $url = $match['url'];
    $title = $match['title'];

    // parse out the URL to get the date
    $parts = explode('/', $url);
    $date = '@'.$parts[1].'-'.$parts[2].'-'.$parts[3];

    $content .= <<<EOD
        <span class="post_date">$date</span>&nbsp;&nbsp;
        <a class="post_title" href="$url">$title</a>
EOD;
}

$layout = str_replace('{{ content }}', $content.'<br /><br />', $layout);
$layout = str_replace('{{ title }}', 'Tagged with "'.$tag.'"', $layout);

// remove any other templating stuff
$layout = preg_replace('/\{\{.+?\}\}/', '', $layout);
$layout = preg_replace('/\{% if showComments.*endif %}/s', '', $layout);
?>

<?php echo $layout; ?>