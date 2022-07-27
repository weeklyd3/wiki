<?php
/*
This file is part of weeklyd3's wiki software.

weeklyd3's wiki software is free software: you can redistribute 
it and/or modify it under the terms of the GNU General Public 
License as published by the Free Software Foundation, either 
version 3 of the License, or (at your option) any later version.

weeklyd3's wiki software is distributed in the hope that it will 
be useful, but WITHOUT ANY WARRANTY; without even the implied 
warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public 
License along with weeklyd3's wiki software. If not, see 
<https://www.gnu.org/licenses/>. 
*/
 
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
/**
 * Parses a string. It only handles templates, anything else should use Parsedown.
 * 
 * @return string $parsedText The parsed template text.
 * @param string $text The text to parse.
 * @param callback $getTemplate The function that gets the value of the template. Returns null if none could be found.
 * @param bool $doNest If nesting should be handled.
 */
function parse(string $text, callable $getTemplate, bool $doNest = true) {
    $parsed = parseTemplates($text);
    $parsedText = $text;
    foreach ($parsed as $index => &$parse) {
        $templateFull = explode('|', $parse->text);
        $name = $templateFull[0];
        array_shift($templateFull);
        $args = $templateFull;
        $invocation = $parse->text;
        $template = call_user_func(__FUNCTION__, $name, $getTemplate);
        $templateText = call_user_func($getTemplate, "Template:$template", ...$args);
        if (!isset($templateText)) $templateText = "Error: Template not found: Template:$template";
        $orig = $parsedText;
        $parsedText = substr_replace($parsedText, $templateText, $parse->start, 2 + ($parse->end - $parse->start));
        array_shift($parsed);
        foreach ($parsed as &$thing) {
            $thing->start += strlen($parsedText) - strlen($orig);
            $thing->end += strlen($parsedText) - strlen($orig);
        }
    }
    if ($doNest) {
        $count = count(parseTemplates($parsedText));
        while ($count) {
            $parsedText = parse($parsedText, $getTemplate, false);
            $count = count(parseTemplates($parsedText));
        }
    }
    return $parsedText;
}