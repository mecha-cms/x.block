<?php

$state = Extend::state(__DIR__, 'union');

Lot::set([
    'ue' => $state[1][0],
    'ux' => $state[1][3],
    'ui' => uniqid()
], __DIR__);

function fn_block_x($content) {
    extract(Lot::get(null, [], __DIR__));
    if (strpos($content, $ux[0]) === false) {
        return $content;
    }
    return str_replace([$ux[0], $ux[1]], [X . $ui, $ui . X], $content);
}

function fn_block($content) {
    extract(Lot::get(null, [], __DIR__));
    // no `[[` character(s) found, skip anyway…
    if (strpos($content, $ue[0]) === false && strpos($content, X . $ui) === false) {
        return $content;
    }
    foreach (Block::get(null, []) as $k => $v) {
        $content = call_user_func($v, $content);
    }
    return str_replace([X . $ui, $ui . X], [$ue[0], $ue[1]], $content);
}

Hook::set('page.content', 'fn_block_x', 0);
Hook::set('page.content', 'fn_block', 1);