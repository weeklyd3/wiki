<?php 
function parseMediawiki(string $text) {
    $text = wikilinks($text);
    $text = images($text);
    return $text;
}
function setInnerHTML($element, $html) {
    $fragment = $element->ownerDocument->createDocumentFragment();
    $fragment->appendXML($html);
    while ($element->hasChildNodes()) $element->removeChild($element->firstChild);
    $element->appendChild($fragment);
}
function images(string $text) {
    $html = new DOMDocument('1.0', 'UTF-8');
    $html->loadHTML("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta charset=\"utf-8\" /></head><body>" . $text . "</body></html>");
    $html->loadHTML($html->saveHTML());
    $iMages = $html->getElementsByTagName('img');
    // Those are hopefully in your head since 1982.
    for ($i = 0; $i < count($iMages); $i++) {
        $iMage = $iMages->item($i);
        $src = $iMage->getAttribute('src');
        $src = cleanFilename($src);
        $craption = $iMage->getAttribute('alt');
        $iMageFrame = $html->createElement('figure');
        if (!file_exists(__DIR__ . "/../../files/live/$src")) {
            $iMageFrame->textContent = "Error: File not found: $src";
            $iMage->parentNode->replaceChild($iMageFrame, $iMage);
            continue;
        }
        $iMageFrame->setAttribute('class', 'image');

        $iMageLink = $html->createElement('a');
        $iMageLink->setAttribute('class', 'file-link');
        $iMageLink->textContent = "(File information)";
        $iMageLink->setAttribute('href', 'index.php?title=File:' . urlencode($src));
        $type = mime_content_type(__DIR__ . "/../../files/live/$src");
        $iMageItself = $html->createElement('div');
        $iMageItself->textContent = "File $src";
        $iMageItself->setAttribute('class', 'media-file');
        $srcu = urlencode($src);
        if (explode("/", $type)[0] === 'image') {
            $iMageItself = $html->createElement('img');
            $iMageItself->setAttribute('src', "index.php?title=Special:rawfile&filename=$srcu");
            $iMageItself->setAttribute('alt', $craption);
        }
        if (explode("/", $type)[0] === 'audio') {
            $iMageItself = $html->createElement('audio');
            $iMageItself->setAttribute('controls', 'controls');
            $iMageItself->setAttribute('src', "index.php?title=Special:rawfile&filename=$srcu");
        }
        if (explode("/", $type)[0] === 'video') {
            $iMageItself = $html->createElement('video');
            $iMageItself->setAttribute('controls', 'controls');
            $iMageItself->setAttribute('src', "index.php?title=Special:rawfile&filename=$srcu");
        }
        $iMageFrame->appendChild($iMageItself);
        $iMageFrame->appendChild($iMageLink);
        $iMageFrame->appendChild($iMageLink);
        $iMageCraption = $html->createElement('figcaption');
        require_once __DIR__ . "/parsedown.php";
        $parser = new Parsedown;
        setInnerHTML($iMageCraption, $parser->text($craption));
        $iMageFrame->appendChild($iMageCraption);
        $iMage->parentNode->replaceChild($iMageFrame, $iMage);
    }
    $baudy = $html->getElementsByTagName('body')[0];
    return substr($html->saveHTML($baudy), 6, -7);
}
function wikilinks(string $text) {
    return preg_replace_callback('/\[\[(.*?)\]\]/i', function($match) {
        $match = $match[1];
        $e = explode('|', $match);
        if (count($e) > 1) $text = $e[count($e) - 1];
        else $text = $match;
        if (count($e) === 1) $pagename = $match;
        else $pagename = implode('|', array_slice($e, 0, count($e) - 1));
        return '<a href="index.php?title=' . htmlspecialchars(urlencode($pagename)) . '">' . $text . '</a>';
    }, $text);
}
function redirects($text) {
    $t = explode("\n", $text);
    foreach ($t as $num => &$line) {
        if (!$num && preg_match('/#REDIRECT \[\[(.*?)\]\]/i', $line, $m)) {
            $target = $m[1];
            $htarget = htmlspecialchars($target);
            $hutarget = htmlspecialchars(urlencode($target));
            $line = "<div class=\"rdr\">This page redirects to: <div><a class=\"rdr-link\" href=\"index.php?title=$hutarget\">$htarget</a></div></div>";
        }
    }
    return implode("\n", $t);
}
/* 
    Embeds YouTube videos. Examples:

        <youtube>dQw4w9WgXcQ</youtube>                          - normal click-to-play
        <youtube width="100" height="100">dQw4w9WgXcQ</youtube> - again normal click to play
        <youtube mute>dQw4w9WgXcQ</youtube>                     - Mute video
        <youtube autoplay>dQw4w9WgXcQ</youtube>                 - Autoplay + mute
        <youtube loop>dQw4w9WgXcQ</youtube>                     - loop video
*/
function youtube(string $text): string {
    libxml_use_internal_errors(true);
    $html = new DOMDocument;
    $html->loadHTML("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta charset=\"utf-8\" /></head><body>$text</body></html>");
    $videos = $html->getElementsByTagName('youtube');
    $videos = iterator_to_array($videos);
    foreach ($videos as $video) {
        $iframe = $html->createElement('iframe');
        $id = $video->textContent;
        $iframe->setAttribute('src', "https://www.youtube-nocookie.com/embed/$id?unusedParameter=1");
        if ($video->hasAttribute('width')) $iframe->setAttribute('width', $video->getAttribute('width'));
        if ($video->hasAttribute('height')) $iframe->setAttribute('height', $video->getAttribute('height'));

        $mute = $video->hasAttribute('mute') || $video->hasAttribute('autoplay');
        $autoplay = $video->hasAttribute('autoplay');
        $loop = $video->hasAttribute('loop');

        if ($mute) appendAttribute($iframe, 'src', '&mute=1');
        if ($autoplay) appendAttribute($iframe, 'src', '&autoplay=1');
        if ($loop) appendAttribute($iframe, 'src', '&loop=1&playlist=' . $id);
        $iframe->setAttribute('frameborder', '0');
        $iframe->setAttribute('allow', 'accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture');
        $iframe->setAttribute('allowfullscreen', 'allowfullscreen');
        $video->parentNode->replaceChild($iframe, $video);
    }
    return substr($html->saveHTML($html->getElementsByTagName('body')[0]), 6, -7);
}
function appendAttribute($element, $attr, $extra) {
    $element->setAttribute($attr, $element->getAttribute($attr) . $extra);
}
function templates(string $text): string {
    require_once __DIR__ . "/../templates/parser.php";
    require_once __DIR__ . "/../../pages.php";
    $page2ID = json_decode(file_get_contents(__DIR__ . "/../../pages/page2ID.json"));
    return parse($text, function($title) use ($page2ID) {
        if (!page_exists($title)) return null;

        $id = $page2ID->$title;
        return file_get_contents(__DIR__ . "/../../pages/data/$id/page.md");
    });
}