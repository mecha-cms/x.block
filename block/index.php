<?php

function fn_block_x($content) {
    $state = Extend::state(__DIR__, 'union');
    $ue = $state[1][0];
    $ux = $state[1][3];
    $ui = md5(__FILE__);
    if (strpos($content, $ux[0]) === false) {
        return $content;
    }
    return str_replace([$ux[0], $ux[1]], [X . $ui, $ui . X], $content);
}

function fn_block($content) {
    $state = Extend::state(__DIR__, 'union');
    $ue = $state[1][0];
    $ux = $state[1][3];
    $ui = md5(__FILE__);
    // no `[[` character(s) found, skip anyway…
    if (strpos($content, $ue[0]) === false && strpos($content, X . $ui) === false) {
        return $content;
    }
    foreach (Anemon::eat(Block::get(null, []))->sort(1, 'stack')->vomit() as $k => $v) {
        $content = call_user_func($v['fn'], $content);
    }
    return str_replace([X . $ui, $ui . X], [$ue[0], $ue[1]], $content);
}

Hook::set('page.content', 'fn_block_x', 0);
Hook::set('page.content', 'fn_block', 1);