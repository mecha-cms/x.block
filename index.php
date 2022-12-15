<?php

namespace x {
    function block($content) {
        // Content is empty or does not contain `[[` token(s), skip!
        if (!$content || false === \strpos($content, '[[')) {
            return $content;
        }
        $that = $this;
        $r = static function ($m) use ($that) {
            $out = [
                0 => $m[1],
                1 => isset($m[4]) ? \x\block\shift($m[3]) : false,
                2 => []
            ];
            if (isset($m[2]) && \preg_match_all('/\s+([^\s"\'\/=\[\]]+)(?:=("(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[^\s\/\]]*))?/', $m[2], $mm)) {
                if (!empty($mm[1])) {
                    foreach ($mm[1] as $i => $k) {
                        $v = $mm[2][$i];
                        $v = \strtr(0 === \strpos($v, '"') && '"' === \substr($v, -1) || 0 === \strpos($v, "'") && "'" === \substr($v, -1) ? \substr($v, 1, -1) : $v, [
                            '\\"' => '"',
                            '\\\'' => "'"
                        ]);
                        $out[2][$k] = $v === $k || isset($mm[0][$i]) && false === \strpos($mm[0][$i], '=') ? true : $v;
                    }
                }
            }
            // Use the named block hook if available …
            if (\Hook::get('block.' . $out[0])) {
                if (\is_string($out[1]) && false !== \strpos($out[1], '[[')) {
                    $out[1] = \fire(__NAMESPACE__ . "\\block", [$out[1]], $that); // Recurse!
                }
                return \Hook::fire('block.' . $out[0], [$m[0], $out], $that);
            }
            // … or use the unnamed block hook if available …
            if (\Hook::get('block')) {
                if (\is_string($out[1]) && false !== \strpos($out[1], '[[')) {
                    $out[1] = \fire(__NAMESPACE__ . "\\block", [$out[1]], $that); // Recurse!
                }
                return \Hook::fire('block', [$m[0], $out], $that);
            }
            // … or else, return the block syntax!
            return $m[0];
        };
        // Prioritize container block(s) over void block(s)
        // First, try to capture `[[asdf]]asdf[[/asdf]]`, then try to capture `[[asdf/]]`
        $content = \preg_replace_callback('/\[\[([^\s"\'\/=\[\]]+)(\s(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[^\/\]])*)?(?:\]\]((?:(?R)|[\s\S])*?)\[\[\/(\1)\]\]|\/\]\])/', $r, $content);

        // Check for `[[` character(s) again after the previous capture(s);
        // If the character(s) still exists, we may have some void block(s)…
        if (false !== \strpos($content, '[[')) {
            // Try to capture `[[asdf]]`
            $content = \preg_replace_callback('/\[\[([^\s"\'\/=\[\]]+)(\s(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\'|[^\]])*)?\]\]/', $r, $content);
        }
        return $content;
    }
    \Hook::set([
        'page.content',
        'page.css', // `.\lot\x\art`
        'page.description',
        'page.js', // `.\lot\x\art`
        'page.title'
    ], __NAMESPACE__ . "\\block", 1);
}

namespace x\block {
    function shift($content) {
        $content = \rtrim(\trim($content, "\n"));
        if (\preg_match('/^[ \t]+/', $content, $m)) {
            $out = "";
            foreach (\explode("\n", $content) as $v) {
                if (0 === \strpos($v, $m[0])) {
                    $out .= \substr($v, \strlen($m[0])) . "\n";
                    continue;
                }
                $out .= $v . "\n";
            }
            return \substr($out, 0, -1);
        }
        return $content;
    }
    if (\defined("\\TEST") && 'x.block' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
        require $test;
    }
    if (\is_dir($folder = \LOT . \D . 'block')) {
        foreach (\g($folder, 'php') as $k => $v) {
            if (!\Hook::get('block.' . ($n = \basename($k, '.php')))) {
                (static function ($k) use ($n) {
                    \extract($GLOBALS, \EXTR_SKIP);
                    if (!\is_callable($fn = require $k)) {
                        $fn = function () use ($fn) {
                            return $fn;
                        };
                    }
                    \Hook::set('block.' . $n, $fn, 10);
                })($k);
            }
        }
    }
}