<?php

if ($state = Extend::state('block', [])) {
    Block::$config = array_replace_recursive(Block::$config, $state);
}

function fn_block_x($content) {
    $union = Block::$config['union'];
    $hash = md5(__FILE__);
    $esc = $union[1][3];
    if (strpos($content, $esc[0]) === false) {
        return $content;
    }
    return str_replace([$esc[0], $esc[1]], [X . $hash, $hash . X], $content);
}

function fn_block($content) {
    $union = Block::$config['union'];
    $hash = md5(__FILE__);
    $block = $union[1][0];
    $esc = $union[1][3];
    // No `[[` character(s) found, skip anyway…
    if (strpos($content, $block[0]) === false && strpos($content, X . $hash) === false) {
        return $content;
    }
    foreach (g(BLOCK, 'data', GLOB_NOSORT) as $v) {
        $content = Block::replace($k = Path::N($v), function($a, $b) use($k, $v) {
            $data = [
                0 => $k,
                1 => $a,
                2 => $b
            ];
            $data[2] = json_encode($data[2]);
            return __replace__(file_get_contents($v), array_replace($data, $b));
        }, $content);
    }
    foreach (Anemon::eat(Block::get(null, []))->sort([1, 'stack'], true)->vomit() as $k => $v) {
        $content = Block::replace($k, $v['fn'], $content);
    }
    return str_replace([X . $hash, $hash . X], [$block[0], $block[1]], $content);
}

Hook::set([
    '*.content',
    '*.css',
    '*.description',
    '*.js'
], 'fn_block_x', 0);

Hook::set([
    '*.content',
    '*.css',
    '*.description',
    '*.js'
], 'fn_block', 1);