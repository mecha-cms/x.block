<?php

class Block extends Union {

    protected static $lot = [];

    public static $config = self::config;

    public static function set($id, $fn, $stack = null) {
        if (!isset(self::$lot[0][$id])) {
            self::$lot[1][$id] = [
                'fn' => $fn,
                'stack' => (float) (isset($stack) ? $stack : 10)
            ];
            return true;
        }
        return false;
    }

    public static function get($id = null, $fail = false) {
        if (isset($id)) {
            return array_key_exists($id, self::$lot[1]) ? self::$lot[$id][1] : $fail;
        }
        return !empty(self::$lot[1]) ? self::$lot[1] : $fail;
    }

    public static function reset($id = null) {
        if (isset($id)) {
            self::$lot[0][$id] = 1;
            unset(self::$lot[1][$id]);
        } else {
            self::$lot = [];
        }
        return true;
    }

    public static function replace($id, $fn, $content) {
        $id_x = x($id, $d = '#');
        $state = static::$config;
        $block = new static;
        $u = $state['union'][1];
        $x = isset($state['union'][0]) ? $state['union'][0] : [];
        $block_open = $u[0][0]; // `[[`
        $block_close = $u[0][1]; // `]]`
        $block_end = $u[0][2]; // `/`
        $block_separator = $u[1][3]; // ` `
        $block_open_x = isset($x[0][0]) ? $x[0][0] : x($block_open, $d);
        $block_close_x = isset($x[0][1]) ? $x[0][1] : x($block_close, $d);
        $block_end_x = isset($x[0][2]) ? $x[0][2] : x($block_end, $d);
        $block_separator_x = isset($x[1][3]) ? $x[1][3] : x($block_separator, $d);
        // Quick replace with static output…
        if (!is_callable($fn)) {
            $fn = function() use($fn) {
                return $fn;
            };
        }
        // No `[[` character(s) found, skip anyway…
        if (strpos($content, $block_open) === false) {
            return $content;
        }
        // No `[[id]]`, `[[id/]]`, `[[id /]]` and `[[id ` character(s) found, skip…
        if (
            strpos($content, $block_open . $id . $block_close) === false &&
            strpos($content, $block_open . $id . $block_end . $block_close) === false &&
            strpos($content, $block_open . $id . $block_separator . $block_end . $block_close) === false &&
            strpos($content, $block_open . $id . $block_separator) === false
        ) {
            return $content;
        }
        // Prioritize container block(s) over void block(s)
        // Check for `[[/id]]` character(s)…
        if (strpos($content, $block_open . $block_end . $id . $block_close) !== false) {
            // `[[id]]content[[/id]]`
            $pattern = $block_open_x . $id_x . '(?:' . $block_separator_x . '.*?)?(?:' . $block_end_x . $block_close_x . '|' . $block_close_x . '(?:[\s\S]*?' . $block_open_x . $block_end_x . $id_x . $block_close_x . ')?)';
            $content = preg_replace_callback($d . $pattern . $d, function($m) use($block, $fn, $state) {
                $m[0] = str_replace('&quot;', '"', $m[0]); // TODO: Set proper fix for `markdown` plugin that replace(s) `"` with `&quot;`
                $data = $block->apart($m[0]);
                array_shift($data); // Remove “Element.nodeName” data
                return call_user_func($fn, ...concat($data, [$m]));
            }, $content);
        }
        // Check for `[[id` character(s) after doing the previous parsing process;
        // If the character(s) still exists, it means we may have some void block(s)…
        if (strpos($content, $block_open . $id) !== false) {
            // Check for `[[id ` character(s), if the character(s) exists,
            // then we may have some void block(s) with attribute(s) in it…
            if (strpos($content, $block_open . $id . $block_separator) !== false) {
                // `[[id foo="bar"]]` or `[[id foo="bar"/]]`
                $pattern = $block_open_x . $id_x . '(' . $block_separator_x . '.*?)?' . $block_separator_x . '*' . $block_end_x . '?' . $block_close_x;
                $content = preg_replace_callback($d . $pattern . $d, function($m) use($block, $fn) {
                    $m[0] = str_replace('&quot;', '"', $m[0]); // TODO: Set proper fix for `markdown` plugin that replace(s) `"` with `&quot;`
                    $data = $block->apart($m[0]);
                    array_shift($data); // Remove “Element.nodeName” data
                    return call_user_func($fn, ...concat($data, [$m]));
                }, $content);
            // else, void block(s) with no attribute(s)
            // we can replace them quickly…
            } else {
                // `[[id]]`, `[[id/]]` and `[[id /]]`
                $content = str_replace([
                    $block_open . $id . $block_close,
                    $block_open . $id . $block_end . $block_close,
                    $block_open . $id . $block_separator . $block_end . $block_close
                ], call_user_func($fn, false, [], [""]), $content);
            }
        }
        return $content;
    }

}