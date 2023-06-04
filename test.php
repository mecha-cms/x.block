<?php

$content = file_get_contents(__DIR__ . D . 'test.txt');

if (array_key_exists('hook', $_GET)) {
    $hook = $_GET['hook'];
    if (!empty($hook)) {
        if (is_array($hook)) {
            foreach ($hook as $v) {
                Hook::set('block.' . $v, function ($content, $data) {
                    return '<mark style="background:rgba(0,0,0,.125);border:1px solid;border-color:inherit;color:inherit;display:inline-block;margin:.25em;padding:.25em;" title="' . $data[0] . '">' . ($data[1] ?: '<em role="status">empty</em>') . '</mark>';
                });
            }
        } else {
            Hook::set('block.' . $hook, function ($content, $data) {
                return '<mark style="background:rgba(0,0,0,.125);border:1px solid;border-color:inherit;color:inherit;display:inline-block;margin:.25em;padding:.25em;" title="' . $data[0] . '">' . ($data[1] ?: '<em role="status">empty</em>') . '</mark>';
            });
        }
    } else {
        Hook::set('block', function ($content, $data) {
            return '<mark style="background:rgba(0,0,0,.125);border:1px solid;border-color:inherit;color:inherit;display:inline-block;margin:.25em;padding:.25em;" title="' . $data[0] . '">' . ($data[1] ?: '<em role="status">empty</em>') . '</mark>';
        });
    }
}

echo '<h1>Notes</h1>';
echo '<ul>';
echo '<li>';
echo 'Add <a href="?hook"><code>?hook</code></a> in URL to test as if a hook is available for any blocks.';
echo '</li>';
echo '<li>';
echo 'Add <a href="?hook=aaa"><code>?hook=aaa</code></a> in URL to test as if a hook is available for the <code>aaa</code> blocks.';
echo '</li>';
echo '<li>';
echo 'Add <a href="?hook[]=list&amp;hook[]=list-item"><code>?hook[]=list&amp;hook[]=list-item</code></a> in URL to test as if a hook is available for the <code>list</code> and <code>list-item</code> blocks.';
echo '</li>';
echo '<li>';
echo 'If no hooks are applied to the block, the default response is to show the block syntax as-is in the page content.';
echo '</li>';
echo '</ul>';

echo '<pre style="background:#ccc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;padding:.5em .75em;white-space:pre-wrap;word-wrap:break-word;">' . $content . '</pre>';
echo '<pre style="background:#cfc;border:1px solid rgba(0,0,0,.25);color:#000;font:normal normal 100%/1.25 monospace;padding:.5em .75em;white-space:pre-wrap;word-wrap:break-word;">' . x\block\page__content($content) . '</pre>';

exit;