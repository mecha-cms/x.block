<?php

$state = Extend::state(__DIR__, 'union');

Lot::set([
    'ue' => $state[1][0],
    'ux' => $state[1][3],
    'ui' => uniqid()
], __DIR__);

function fn_block_x($data) {
    extract(Lot::get(null, [], __DIR__));
    if (empty($data['content'])) {
        return $data;
    }
    $content = $data['content'];
    if (strpos($content, $ux[0]) === false) {
        return $data;
    }
    $data['content'] = str_replace([$ux[0], $ux[1]], [X . $ui, $ui . X], $content);
    return $data;
}

function fn_block($data) {
    extract(Lot::get(null, [], __DIR__));
    if (!empty($data['content'])) {
        $content = $data['content'];
        // no `[[` character(s) found, skip anyway …
        if (strpos($content, $ue[0]) === false && strpos($content, X . $ui) === false) {
            return $data;
        }
        foreach (Block::get(null, []) as $k => $v) {
            $content = call_user_func($v, $content);
        }
        $data['content'] = str_replace([X . $ui, $ui . X], [$ue[0], $ue[1]], $content);
    }
    return $data;
}

Hook::set('page.input', 'fn_block_x', 0);
Hook::set('page.input', 'fn_block', 1);