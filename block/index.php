<?php namespace _\lot\x;

if ($state = \state('block')) {
    \Block::$config = \extend(\Block::$config, (array) $state);
}

function block($content) {
    $c = \Block::$config;
    // No `[[` character(s) found, skip anyway…
    if (\strpos($content, $c[0][0]) === false) {
        return $content;
    }
    foreach (\g(\BLOCK, 'data') as $k => $v) {
        $content = \Block::alter($n = \Path::N($k), function($a, $b) use($n, $k) {
            $data = [
                0 => $n,
                1 => $a,
                2 => $b
            ];
            $data[2] = \json_encode($data[2]);
            $content = \file_get_contents($k);
            foreach (\array_replace($data, $b) as $k => $v) {
                $content = \str_replace('%' . $k, $v, $content);
            }
            return $content;
        }, $content);
    }
    foreach ((new \Anemon(\Block::get()))->sort([1, 'stack'], true) as $k => $v) {
        $content = \Block::alter($k, $v['fn'], $content);
    }
    return $content;
}

\Hook::set([
    'page.content',
    'page.css',
    'page.description',
    'page.image',
    'page.js',
    'page.link'
], __NAMESPACE__ . "\\block", 1);