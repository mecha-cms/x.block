<?php namespace _\lot\x;

if ($state = \State::get('x.block', true)) {
    \Block::$state = \array_replace_recursive(\Block::$state, (array) $state);
}

function block($content) {
    $c = \Block::$state;
    // No `[[` character(s) found, skip anyway…
    if (false === \strpos($content, $c[0][0])) {
        return $content;
    }
    foreach (\g(\LOT . \DS . 'block', 'data') as $k => $v) {
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
    'page.css', // `.\lot\x\art`
    'page.description',
    'page.excerpt', // `.\lot\x\excerpt`
    'page.image', // `.\lot\x\image`
    'page.js', // `.\lot\x\art`
    'page.link'
], __NAMESPACE__ . "\\block", 1);