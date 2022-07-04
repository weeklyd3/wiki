<?php 
global $originalPageName;
global $useTemplates;
function startsWith($haystack, $needle) {
    $length = strlen($needle);
    return substr($haystack, 0, $length) === $needle;
}
function endsWith( $haystack, $needle ) {
   $length = strlen($needle);
   if (!$length) {
       return true;
   }
   return substr( $haystack, -$length ) === $needle;
}

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
    $doc = new DOMDocument;
    libxml_use_internal_errors(true);
    $doc->loadHTML("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta charset=\"utf-8\" /></head><body>$text</body></html>");
    $xpath = new DOMXPath($doc);
    $textnodes = $xpath->query('//text()');
    foreach ($textnodes as $node) {
        $text = $node->textContent;
        $parentnode = $node->parentNode;
        while ($parentnode) {
        	if (isset($parentnode->doctype)) break;
            if ($parentnode->tagName === 'pre' || $parentnode->tagName === 'code') continue 2;
            $parentnode = $parentnode->parentNode;
        }
        $text = preg_replace_callback("/\[\[(.*?)\]\]/", function($thing) {
            $link = $thing[1];
            $exploded = explode('|', $link);
            if (count($exploded) === 1) {
                $href = $link;
                $linktext = $link;
            } else {
                $href = array_shift($exploded);
                $linktext = implode('|', $exploded);
            }
            require_once __DIR__ . "/../../pages.php";
            require_once __DIR__ . "/../../pageRender.php";
            $rdr = false;
            $exists = true;
            if (!page_exists($href)) $exists = false;
            $hrefnospecial = substr($href, strlen('Special:'));
            if (substr($href, 0, strlen('Special:')) === 'Special:') $exists = file_exists(__DIR__ . "/../../special/$hrefnospecial.php");
            else {
                if (substr(page_get_contents($href), 0, strlen('#REDIRECT [[')) === '#REDIRECT [[') $rdr = true;
            }
            $link = '<a href="index.php?title=' . htmlspecialchars(urlencode($href)) . '" class="wikilink ';
            if ($rdr) $link .= 'redirect';
            if ($rdr && !$exists) $link .= ' ';
            if (!$exists) $link .= 'redlink';
            $link .= '">' . $linktext . '</a>';
            return $link;
        }, $text);
        $docFrag = $doc->createDocumentFragment();
        $docFrag->appendXML($text);
        $node->parentNode->replaceChild($docFrag, $node);
    }
    return substr($doc->saveHTML($doc->getElementsByTagName('body')->item(0)), 6, -7);
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
    global $originalPageName;
    require_once __DIR__ . "/../templates/parser.php";
    require_once __DIR__ . "/../../pages.php";
    $page2ID = json_decode(file_get_contents(__DIR__ . "/../../pages/page2ID.json"));
    return parse($text, function($title, ...$args) use ($page2ID, $originalPageName) {
        $parsedArgs = array();
        $ctr = 1;
        foreach ($args as $a) {
            $name = explode('=', $a)[0];
            $parsed = explode('=', $a);
            if (count(explode('=', $a)) === 1) $name = $ctr++;
            array_shift($parsed);
            if (!count($parsed)) $parsed = explode('=', $a);
            $parsedArgs[$name] = implode('=', $parsed);
        }
        switch ($title) {
            case 'Template:FULLPAGENAME':
                return $originalPageName;
                break;
            case 'Template:BASEPAGENAME':
                error_log($originalPageName);
                $pagename = explode(':', $originalPageName);
                if (count($pagename) > 1) array_shift($pagename);
                return implode(':', $pagename);
                break;
            default:
                if (!page_exists($title)) return null;
                $id = $page2ID->$title;
                $contents = parseTemplates(file_get_contents(__DIR__ . "/../../pages/data/$id/page.md"));
                $text = file_get_contents(__DIR__ . "/../../pages/data/$id/page.md");
                foreach ($contents as $tag) {
                    if (!startsWith($tag->text, 'arg')) continue;
                    $argText = substr($tag->text, 3);
                    $start = $tag->start;
                    $length = 2 + $tag->end - $tag->start;
                    $argName = explode('|', $argText)[0];
                    $fallbackParts = explode('|', $argText);
                    array_shift($fallbackParts);
                    $fallback = implode('|', $fallbackParts);

                    if (isset($parsedArgs[$argName])) $toReplace = $parsedArgs[$argName];
                    else $toReplace = $fallback;
                    $orig = $text;

                    $text = substr_replace($text, $toReplace, $start, $length);
                    array_shift($contents);
                    foreach ($contents as $content) {
                        $content->start += strlen($text) - strlen($orig);
                        $content->end += strlen($text) - strlen($orig);
                    }
                }
                libxml_use_internal_errors(true);
                $doc = new DOMDocument;
                $doc->loadHTML("<!DOCTYPE html><html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" /><meta charset=\"utf-8\" /></head><body>$text</body></html>");
                $noincludes = $doc->getElementsByTagName('noinclude');
                foreach ($noincludes as $no) {
                    $no->parentNode->removeChild($no);
                }
                $text = substr($doc->saveHTML($doc->getElementsByTagName('body')->item(0)), 6, -7);
                return $text;
                break;
        }
    });
}