<?php

final class Block extends SGML {

    const state = [
        0 => ['[[', ']]', '/'],
        1 => ['"', '"', '=']
    ];

    private static $block;

    public $strict = false;

    public static $state = self::state;

    public static function alter(string $id, $fn, string $content) {
        $c = static::$state;
        $open = $c[0][0]; // `[[`
        $close = $c[0][1]; // `]]`
        $end = $c[0][2]; // `/`
        $open_x = x($open);
        $close_x = x($close);
        $end_x = x($end);
        $id_x = x($id);
        // Quick replace with static output…
        if (!is_callable($fn)) {
            $fn = function() use($fn) {
                return $fn;
            };
        }
        // No `[[` character(s) found, skip anyway…
        if (false === strpos($content, $open)) {
            return $content;
        }
        // No `[[id]]`, `[[id/]]`, `[[id /]]` and `[[id ` character(s) found, skip…
        if (
            false === strpos($content, $open . $id . $close) &&
            false === strpos($content, $open . $id . $end . $close) &&
            false === strpos($content, $open . $id . ' ' . $end . $close) &&
            false === strpos($content, $open . $id . ' ')
        ) {
            return $content;
        }
        // Prioritize container block(s) over void block(s)
        // Check for `[[/id]]` character(s)…
        if (false !== strpos($content, $open . $end . $id . $close)) {
            // `[[id]]content[[/id]]`
            $content = preg_replace_callback('/' . $open_x . $id_x . '(?:[ ][^' . $close_x . ']*)?(?:' . $end_x . $close_x . '|' . $close_x . '(?:(?:[\s\S](?!' . $open_x . $id_x . '(?: |' . $close_x . ')))*?' . $open_x . $end_x . $id_x . $close_x . ')?)/', function($m) use($fn) {
                $data = new static($m[0]);
                return call_user_func($fn, $data[1], e($data[2]), $m);
            }, $content);
        }
        // Check for `[[id` character(s) after doing the previous parsing process;
        // If the character(s) still exists, it means we may have some void block(s)…
        if (false !== strpos($content, $open . $id)) {
            // Check for `[[id ` character(s); If the character(s) still exists,
            // then we may have some void block(s) with attribute(s) in it…
            if (false !== strpos($content, $open . $id . ' ')) {
                // `[[id foo="bar"]]` or `[[id foo="bar"/]]`
                $content = preg_replace_callback('/' . $open_x . $id_x . '([ ].*?)?[ ]*' . $end_x . '?' . $close_x . '/', function($m) use($fn) {
                    $data = new static($m[0]);
                    return call_user_func($fn, $data[1], e($data[2]), $m);
                }, $content);
            // Else; void block(s) with no attribute(s), replace them quickly…
            } else {
                // `[[id]]`, `[[id/]]` and `[[id /]]`
                $content = str_replace([
                    $open . $id . $close,
                    $open . $id . $end . $close,
                    $open . $id . ' ' . $end . $close
                ], call_user_func($fn, false, [], [""]), $content);
            }
        }
        return $content;
    }

    public static function get(string $id = null) {
        if (isset($id)) {
            return self::$block[1][$id] ?? null;
        }
        return self::$block[1] ?? [];
    }

    public static function let(string $id = null) {
        if (isset($id)) {
            self::$block[0][$id] = 1;
            unset(self::$block[1][$id]);
        } else {
            self::$block[1] = [];
        }
    }

    public static function set(string $id, $fn, float $stack = 10) {
        if (!isset(self::$block[0][$id])) {
            self::$block[1][$id] = [
                'fn' => $fn,
                'stack' => (float) $stack
            ];
            return true;
        }
    }

}
