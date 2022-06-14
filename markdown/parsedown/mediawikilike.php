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
    $html = new DOMDocument;
    $html->loadHTML("<html><body>" . $text . "</body></html>");
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
        $iMageLink->setAttribute('href', 'index.php?title=File:' . urlencode($src));
        $type = mime_content_type(__DIR__ . "/../../files/live/$src");
        $iMageItself = $html->createElement('div');
        $iMageItself->textContent = "File $src";
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
        $iMageLink->appendChild($iMageItself);
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