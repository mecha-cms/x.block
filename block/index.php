<?php namespace fn\block;

if ($state = \Extend::state('block', [])) {
    \Block::$config = extend(\Block::$config, $state);
}

function x($content) {
    $union = \Block::$config['union'];
    $hash = md5(__FILE__);
    $esc = $union[1][3];
    if (strpos($content, $esc[0]) === false) {
        return $content;
    }
    return str_replace([$esc[0], $esc[1]], [X . $hash, $hash . X], $content);
}

function v($content) {
    $union = \Block::$config['union'];
    $hash = md5(__FILE__);
    $block = $union[1][0];
    $esc = $union[1][3];
    // No `[[` character(s) found, skip anyway…
    if (strpos($content, $block[0]) === false && strpos($content, X . $hash) === false) {
        return $content;
    }
    foreach (\g(BLOCK, 'data', GLOB_NOSORT) as $v) {
        $content = \Block::replace($k = \Path::N($v), function($a, $b) use($k, $v) {
            $data = [
                0 => $k,
                1 => $a,
                2 => $b
            ];
            $data[2] = json_encode($data[2]);
            return \candy(file_get_contents($v), extend($data, $b));
        }, $content);
    }
    foreach (\Anemon::eat(\Block::get(null, []))->sort([1, 'stack'], true)->vomit() as $k => $v) {
        $content = \Block::replace($k, $v['fn'], $content);
    }
    return str_replace([X . $hash, $hash . X], [$block[0], $block[1]], $content);
}

\Hook::set([
    '*.content',
    '*.css',
    '*.description',
    '*.js'
], __NAMESPACE__ . '\x', 0);

\Hook::set([
    '*.content',
    '*.css',
    '*.description',
    '*.js'
], __NAMESPACE__ . '\v', 1);