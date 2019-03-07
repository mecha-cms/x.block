<?php namespace fn;

if ($state = \Extend::state('block')) {
    \Block::$config = \extend(\Block::$config, (array) $state);
}

function block($content) {
    $c = \Block::$config;
    // No `[[` character(s) found, skip anyway…
    if (\strpos($content, $c[0][0]) === false) {
        return $content;
    }
    foreach (\g(BLOCK, 'data') as $v) {
        $content = \Block::replace($k = \Path::N($v), function($a, $b) use($k, $v) {
            $data = [
                0 => $k,
                1 => $a,
                2 => $b
            ];
            $data[2] = \json_encode($data[2]);
            return \candy(\file_get_contents($v), \extend($data, $b));
        }, $content);
    }
    foreach (\Anemon::eat(\Block::get())->sort([1, 'stack'], true) as $k => $v) {
        $content = \Block::replace($k, $v['fn'], $content);
    }
    return $content;
}

\Hook::set([
    '*.content',
    '*.css',
    '*.description',
    '*.image',
    '*.js',
    '*.link'
], __NAMESPACE__ . "\\block", 1);