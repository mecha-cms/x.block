<?php

class Block extends Genome {

    protected static $lot = [];

    public static function set($id, $fn) {
        self::$lot[$id] = $fn;
        return true;
    }

    public static function get($id = null, $fail = false) {
        if (isset($id)) {
            return array_key_exists($id, self::$lot) ? self::$lot[$id] : $fail;
        }
        return !empty(self::$lot) ? self::$lot : $fail;
    }

    public static function reset($id = null) {
        if (isset($id)) {
            unset(self::$lot[$id]);
        } else {
            self::$lot = [];
        }
        return true;
    }

    public static function replace($id, $fn, $content) {
        $state = Extend::state(Path::D(__DIR__, 2), 'union');
        $union = new Union($state);
        $d = '#';
        $u = $state[1];
        $ueo = $u[0][0]; // `[[`
        $uec = $u[0][1]; // `]]`
        $uee = $u[0][2]; // `/`
        $uas = $u[1][3]; // ` `
        $ueo_x = x($u[0][0], $d);
        $uec_x = x($u[0][1], $d);
        $uee_x = x($u[0][2], $d);
        $uas_x = x($u[1][3], $d);
        $id_x = x($id, $d);
        // quick replace with static output…
        if (!is_callable($fn)) {
            $fn = function() use($fn) {
                return $fn;
            };
        }
        // no `[[` character(s) found, skip anyway…
        if (strpos($content, $ueo) === false) {
            return $content;
        }
        // no `[[id]]`, `[[id/]]` and `[[id /]]` character(s) found, skip…
        if (
            strpos($content, $ueo . $id . $uec) === false &&
            strpos($content, $ueo . $id . $uee . $uec) === false &&
            strpos($content, $ueo . $id . $uas . $uee . $uec) === false
        ) {
            return $content;
        }
        // prioritize container block(s) over void block(s)
        // check for `[[/` character(s) …
        if (strpos($content, $ueo . $uee) !== false) {
            // `[[id]]content[[/id]]`
            $s = $ueo_x . $id_x . '(?:' . $uas_x . '.*?)?(?:' . $uee_x . $uec_x . '|' . $uec_x . '(?:[\s\S]*?' . $ueo_x . $uee_x . $id_x . $uec_x . ')?)';
            $content = preg_replace_callback($d . $s . $d, function($m) use($union, $fn) {
                $data = $union->apart(array_shift($m));
                array_shift($data); // remove “node name” data
                return call_user_func_array($fn, array_merge($data, $m));
            }, $content);
        }
        // check for `[[` character(s) after doing the previous parsing process
        // if the character(s) still exists, it means we may have some void block(s) …
        if (strpos($content, $ueo) !== false) {
            // check for `[[id ` character(s), if the character(s) exists, then we may
            // have some void block(s) with attribute(s) in it …
            if (strpos($content, $ueo . $id . $uas) !== false) {
                // `[[id foo="bar"]]` or `[[id foo="bar"/]]`
                $s = $ueo_x . $id_x . '(' . $uas_x . '.*?)?' . $uas_x . '*' . $uee_x . $uec_x;
                $content = preg_replace_callback($d . $s . $d, function($m) use($union, $fn) {
                    $data = $union->apart(array_shift($m));
                    array_shift($data); // remove “node name” data
                    return call_user_func_array($fn, array_merge($data, $m));
                }, $content);
            // else, void block(s) with no attribute(s)
            // we can replace them as quick as possible …
            } else {
                // `[[id]]`, `[[id/]]` and `[[id /]]`
                $content = str_replace([
                    $ueo . $id . $uec,
                    $ueo . $id . $uee . $uec,
                    $ueo . $id . $uas . $uee . $uec
                ], call_user_func_array($fn, [false, [], []]), $content);
            }
        }
        return $content;
    }

}