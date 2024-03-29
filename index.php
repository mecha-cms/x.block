<?php namespace x\block;

function page__content($content) {
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
        if (isset($m[2]) && \preg_match_all('/\s+([^\s"\'\/=\[\]]+)(?>=("(?>\\.|[^"])*"|\'(?>\\.|[^\'])*\'|[^\s\/\]]*))?/', $m[2], $mm)) {
            if (!empty($mm[1])) {
                foreach ($mm[1] as $i => $k) {
                    $v = $mm[2][$i];
                    if (0 === \strpos($v, '"') && '"' === \substr($v, -1) || 0 === \strpos($v, "'") && "'" === \substr($v, -1)) {
                        $v = \strtr(\substr($v, 1, -1), [
                            '\\"' => '"',
                            '\\\'' => "'"
                        ]);
                    }
                    $out[2][$k] = $v === $k || isset($mm[0][$i]) && false === \strpos($mm[0][$i], '=') ? true : $v;
                }
            }
        }
        // Use the named block hook if available …
        if (\Hook::get('block.' . $out[0])) {
            if (\is_string($out[1]) && false !== \strpos($out[1], '[[')) {
                $out[1] = \fire(__NAMESPACE__ . "\\page__content", [$out[1]], $that); // Recurse!
            }
            return \Hook::fire('block.' . $out[0], [$m[0], $out], $that);
        }
        // … or use the un-named block hook if available …
        if (\Hook::get('block')) {
            if (\is_string($out[1]) && false !== \strpos($out[1], '[[')) {
                $out[1] = \fire(__NAMESPACE__ . "\\page__content", [$out[1]], $that); // Recurse!
            }
            return \Hook::fire('block', [$m[0], $out], $that);
        }
        // … or else, return the block syntax as-is!
        return $m[0];
    };
    // Prioritize container block(s) over void block(s). First, try to capture `[[asdf]]asdf[[/asdf]]`, then try to
    // capture `[[asdf/]]`
    $content = \preg_replace_callback('/\[\[([^\s"\'\/=\[\]]+)(\s(?>"(?>\\.|[^"])*"|\'(?>\\.|[^\'])*\'|[^\/\]])*)?(?>\]\]((?>(?R)|[\s\S])*?)\[\[\/(\1)\]\]|\/\]\])/', $r, $content);
    // Check for `[[` character(s) again after the previous capture(s); If the character(s) still exists, we may have
    // some void block(s)…
    if (false !== \strpos($content, '[[')) {
        // Try to capture `[[asdf]]`
        $content = \preg_replace_callback('/\[\[([^\s"\'\/=\[\]]+)(\s(?>"(?>\\.|[^"])*"|\'(?>\\.|[^\'])*\'|[^\]])*)?\]\]/', $r, $content);
    }
    return $content;
}

function page__description($description) {
    return \fire(__NAMESPACE__ . "\\page__content", [$description], $this);
}

function page__script($script) {
    return \fire(__NAMESPACE__ . "\\page__content", [$script], $this);
}

function page__style($style) {
    return \fire(__NAMESPACE__ . "\\page__content", [$style], $this);
}

function page__title($title) {
    return \fire(__NAMESPACE__ . "\\page__content", [$title], $this);
}

\Hook::set('page.content', __NAMESPACE__ . "\\page__content", 1);
\Hook::set('page.description', __NAMESPACE__ . "\\page__description", 1);
\Hook::set('page.script', __NAMESPACE__ . "\\page__script", 1); // `.\lot\x\art`
\Hook::set('page.style', __NAMESPACE__ . "\\page__style", 1); // `.\lot\x\art`
\Hook::set('page.title', __NAMESPACE__ . "\\page__title", 1);

function shift($content) {
    $content = \strtr($content, ["\t" => \str_repeat(' ', 4)]);
    $content = \rtrim(\trim($content, "\n"));
    if (($dent = \strspn($content, ' ')) > 0) {
        $content = \substr(\strtr($content, [
            "\n" . \str_repeat(' ', $dent) => "\n"
        ]), $dent);
    }
    return $content;
}

if (\is_dir($folder = \LOT . \D . 'block')) {
    foreach (\g($folder, 'php') as $k => $v) {
        if (!\Hook::get('block.' . ($n = \basename($k, '.php')))) {
            (static function ($k) use ($n) {
                \extract(\lot(), \EXTR_SKIP);
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

if (\defined("\\TEST") && 'x.block' === \TEST && \is_file($test = __DIR__ . \D . 'test.php')) {
    require $test;
}