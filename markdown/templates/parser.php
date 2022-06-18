<?php 
/** Parses a template.
 * @param $text: string
 */
function parseTemplates(string $text) {
    $chars = str_split($text);
    $depth = 0;
    $start = 0;
    $matches = array();
    $currentmatch = '';
    $skipNext = false;
    foreach ($chars as $index => $char) {
        if ($skipNext) {
            $skipNext = false;
            continue;
        }
        $notlast = isset($chars[$index + 1]);
        $nextTwo = $char . ($notlast ? $chars[$index + 1] : "");
        if ($nextTwo === '{{') {
            if ($depth === 0) $start = $index;
            // Template starts
            if ($depth) $currentmatch .= '{{';
            $depth++;
            $skipNext = true;
            continue;
        }
        if ($nextTwo === '}}' && $depth) {
            // Template ends
            $depth--;
            if ($depth) {
                $currentmatch .= '}}';
                $skipNext = true;
                continue;
            } else {
                $match = new stdClass;
                $match->text = $currentmatch;
                $match->start = $start;
                $match->end = $index;
                array_push($matches, $match);
                $currentmatch = '';
                $skipNext = true;
                continue;
            }
        }
        if ($depth) $currentmatch .= $char;
    }
    return $matches;
}
// Function for getting templates. Callback should return null if the template
// was not found or the text of the template.
//
// For purposes of testing, you may use a callback that returns random data.
function parse(string $text, callable $getTemplate) {
    $parsed = parseTemplates($text);
}