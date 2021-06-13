<?php

if (!function_exists('id')) {
    function id($x)
    {
        return $x;
    }
}

if (!function_exists('cnst')) {
    function cnst($x): callable
    {
        return static fn ($y) => $x;
    }
}

if (!function_exists('flip')) {
    function flip($f): callable
    {
        return static fn ($a) =>
            static fn ($b) => $f($b)($a);
    }
}

if (!function_exists('spread')) {
    function spread(callable $f): callable
    {
        return static fn (array $xs) => $f(...$xs);
    }
}

if (!function_exists('apply')) {
    function apply(callable $f, ...$x)
    {
        return $f(...$x);
    }
}

if (!function_exists('applyTo')) {
    function applyTo(...$x): callable
    {
        return static fn (callable $f) => $f(...$x);
    }
}

if (!function_exists('compose')) {
    function compose(callable $f, callable $g): callable
    {
        return static fn (...$x) => $f($g(...$x));
    }
}

if (!function_exists('partial')) {
    function partial($f, ...$xs): callable {
        return static fn (...$zs) => $f(...$xs, ...$zs);
    }
}

if (!function_exists('tee')) {
    function tee($f): callable
    {
        return static function ($x) use ($f) {
            $f($x);

            return $x;
        };
    }
}


if (!function_exists('pair')) {
    function pair($a, $b): array
    {
        return [$a, $b];
    }
}

//
// Type checking
//
if (!function_exists('isErr')) {
    function isErr($x): bool {
        return $x instanceof \Throwable;
    }
}

if (!function_exists('isString')) {
    function isString($x): bool {
        return is_string($x);
    }
}

if (!function_exists('isNumber')) {
    function isNumber($x): bool {
        return is_int($x) || is_float($x);
    }
}

if (!function_exists('isBool')) {
    function isBool($x): bool {
        return is_bool($x);
    }
}

if (!function_exists('isInt')) {
    function isInt($x): bool {
        return is_int($x);
    }
}

if (!function_exists('isFloat')) {
    function isFloat($x): bool {
        return is_float($x);
    }
}

if (!function_exists('isFun')) {
    function isFun($x): bool {
        return is_callable($x);
    }
}

if (!function_exists('isNone')) {
    function isNone($x): bool {
        return is_null($x);
    }
}

if (!function_exists('isDict')) {
    function isDict($x): bool {
        return is_array($x)
            && empty(array_filter($x, 'is_numeric', ARRAY_FILTER_USE_KEY));
    }
}

if (!function_exists('isList')) {
    function isList($x): bool {
        return is_array($x)
            && array_search(
                false,
                array_map('is_numeric', array_keys($x)),
                true
            ) === false;
    }
}

if (!function_exists('isIterable')) {
    function isIterable($x): bool
    {
        return is_array($x) || is_string($x);
    }
}

if (!function_exists('isObject')) {
    function isObject($x): bool {
        return !is_callable($x) && is_object($x);
    }
}

if (!function_exists('maybe')) {
    function maybe($x): callable {
        return static fn ($z) => isNone($x) ? $x : $z;
    }
}

//
// Logic
//
if (!function_exists('eq')) {
    function eq($x, $z): bool {
        return $x === $z;
    }
}

if (!function_exists('gt')) {
    function gt($x, $z): bool {
        return $x > $z;
    }
}

if (!function_exists('gte')) {
    function gte($x, $z): bool {
        return $x >= $z;
    }
}

if (!function_exists('lt')) {
    function lt($x, $z): bool {
        return $x < $z;
    }
}

if (!function_exists('lte')) {
    function lte($x, $z): bool {
        return $x <= $z;
    }
}

if (!function_exists('not')) {
    function not(callable $p): callable {
        return static fn (...$xs): bool => !$p(...$xs);
    }
}


//
// Err
//
if (!function_exists('err')) {
    function err(string $msg, array $reason = []): void
    {
        throw new class($msg, $reason) extends \Error
        {
            private $reason;
            function __construct($m, $r)
            {
                $err = $this->getTrace()[0];
                if ($err) {
                    $this->file = join(
                        "/",
                        [
                            basename(dirname($err['file'])),
                            basename($err['file'])
                        ]
                    );
                    $this->line = $err['line'];
                }

                $this->message = $m;
                $this->reason = $r;
            }
        };
    }
}

if (!function_exists('typeErr')) {
    function typeErr(string $msg, $arg): void
    {
        throw new class($msg, $arg) extends \TypeError {
            function __construct($_msg, $_arg)
            {
                $err = $this->getTrace()[1] ?? null;
                if ($err) {
                    $this->file = join(
                        "/",
                        [
                            basename(dirname($err['file'])),
                            basename($err['file'])
                        ]
                    );
                    $this->line = $err['line'];
                }

                $this->message = str_replace(
                    '{arg}',
                    json_encode($_arg, JSON_UNESCAPED_UNICODE),
                    $_msg
                );
            }
        };
    }
}


//
// List
//
if (!function_exists('len')) {
    function len($xs): int
    {
        switch (true) {
            case isString($xs): return strlen($xs);
            case isList($xs) || isDict($xs): return count($xs);
            default:
                typeErr('len({arg}): arg1 expected list/dict or string', $xs);
        }
    }
}

if (!function_exists('reverse')) {
    function reverse($xs)
    {
        switch (true) {
            case isString($xs): return strrev($xs);
            case isList($xs): return array_reverse($xs);
            default:
                typeErr('reverse({arg}): arg1 expected list or string', $xs);
        }
    }
}

if (!function_exists('get')) {
    function get($path, $dflt = null): callable
    {
        return static function ($xs) use ($path, $dflt) {
            if (!isIterable($xs))
                typeErr(
                    'get(... {arg}): arg2 expected list/dict or string',
                    $xs
                );

            if (isString($xs) && !isInt($path))
                typeErr(
                    'get({arg} ...): arg1 expected int when arg2 is string',
                    $path
                );

            switch (true) {
                case isList($path):
                    $_xs = &$xs;
                    foreach ($path as $k) {
                        if (isset($_xs[$k])) $_xs = &$_xs[$k];
                        else return $dflt;
                    }
                    return $_xs;
                case isString($path) || isInt($path): return $xs[$path] ?? $dflt;
                default:
                    typeErr(
                        'get({arg} ...): arg1 expected string/integer or list',
                        $path
                    );
            }
        };
    }
}

if (!function_exists('set')) {
    function set($path, callable $f): callable
    {
        return static function ($xs) use ($path, $f): array {
            if (!(isInt($path) || isString($path) || isList($path)))
                typeErr(
                    'set({arg} ...) arg1 expected string/int or list',
                    $path
                );

            if (!isList($path)) $path = [$path];

            $_set = static function ($v) use ($path, $f) {
                $_xs = &$v;
                foreach ($path as $k) {
                    if (!isset($_xs[$k])) $_xs[$k] = [];
                    $_xs = &$_xs[$k];
                }
                $_xs = $f(empty($_xs) ? null : $_xs);

                return $v;
            };

            switch (true) {
                case isList($xs) || isDict($xs): return $_set($xs);
                case isString($xs):
                    if (!isInt($path)) typeErr(
                        'set({arg} ...) arg1 expected int when arg3 is string',
                        $path
                    );
                    return join("", $_set(str_split($xs)));
                default:
                    typeErr(
                        'set(... {arg}): arg3 expected list/dict or string',
                        $xs
                    );
            }
        };
    }
}

if (!function_exists('head')) {
    function head($xs)
    {
        switch (true) {
            case !(isString($xs) || isList($xs)):
                typeErr('head({arg}): arg1 expected list or string', $xs);
            case eq(len($xs), 0):
                typeErr(
                    'head({arg}): arg1 expected not empty list or string',
                    $xs
                );
            case isString($xs): return $xs[0];
            default: return array_slice($xs, 0, 1)[0];
        }
    }
}

if (!function_exists('snd')) {
    function snd($xs)
    {
        switch (true) {
            case !(isString($xs) || isList($xs)):
                typeErr('snd({arg}): arg1 expected list or string', $xs);
            case lt(len($xs), 2):
                typeErr(
                    'snd({arg}): arg1 expected at least two elems of list or string',
                    $xs
                );
            case isString($xs): return $xs[1];
            default: return array_slice($xs, 1, 1)[0];
        }
    }
}

if (!function_exists('tail')) {
    function tail($xs)
    {
        switch (true) {
            case isList($xs): return array_slice($xs, 1);
            case isString($xs): return (string) substr($xs, 1);
            default: typeErr('tail({arg}): arg1 expected list or string', $xs);
        }
    }
}

if (!function_exists('last')) {
    function last($xs)
    {
        switch (true) {
            case !(isString($xs) || isList($xs)):
                typeErr('last({arg}): arg1 expected list or string', $xs);
            case eq(len($xs), 0):
                typeErr(
                    'last({arg}): arg1 expected not empty list or string',
                    $xs
                );
            default: return head(reverse($xs));
        }
    }
}

if (!function_exists('init')) {
    function init($xs)
    {
        switch (true) {
            case isList($xs): return array_slice($xs, 0, -1);
            case isString($xs): return (string) substr($xs, 0, -1);
            default: typeErr('init({arg}): arg1 expected list or string', $xs);
        }
    }
}

if (!function_exists('findKey')) {
    function findKey($x, array $xs): int
    {
        $res = array_search($x, $xs, true);
        return $res === false ? -1 : $res;
    }
}

if (!function_exists('vals')) {
    function vals(array $xs): array
    {
        return array_values($xs);
    }
}

if (!function_exists('keys')) {
    function keys(array $xs): array
    {
        return array_keys($xs);
    }
}

if (!function_exists('each')) {
    function each(callable $f): callable
    {
        return static function (array $xs) use ($f) {
            foreach ($xs as $key => $val) {
                $f($val, $key);
            }
        };
    }
}

if (!function_exists('fold')) {
    function fold(callable $f, $v): callable
    {
        return static function (array $xs) use ($f, $v) {
            $isDict = isDict($xs);
            $x = $v;
            foreach ($xs as $_k => $_v) {
                $x = $f($x, $isDict ? [$_k, $_v] : $_v);
            }
            return $x;
        };
    }
}

if (!function_exists('reduce')) {
    function reduce(callable $f): callable
    {
        return static function (array $xs) use ($f) {
            if (empty($xs))
                typeErr('reduce(... {arg}): arg2 expected not empty list', $xs);

            if (isDict($xs))
                typeErr('reduce(... {arg}): arg2 expected list', $xs);

            return fold($f, head($xs))(tail($xs));
        };
    }
}

if (!function_exists('filter')) {
    function filter(callable $p): callable
    {
        return fold(
            static function ($x, $z) use ($p) {
                if ($p($z)) {
                    if (isList($z)) $x[$z[0]] = $z[1];
                    else $x[] = $z;
                }
                return $x;
            },
            []
        );
    }
}

if (!function_exists('any')) {
    function any(callable $p): callable
    {
        return fold(static fn ($x, $v) => $x || $p($v), false);
    }
}

if (!function_exists('all')) {
    function all(callable $p): callable
    {
        return fold(static fn ($x, $v) => $x && $p($v), true);
    }
}

if (!function_exists('either')) {
    function either(callable $onLeft, callable $onRight): callable
    {
        return static function ($xs) use ($onLeft, $onRight) {
            if (!(isList($xs) && eq(len($xs), 2)))
                typeErr(
                    'either(... {arg}): arg3 expected pair [left, right]',
                    $xs
                );

            return empty($xs[0]) ? $onLeft($xs[0]) : $onRight($xs[1]);
        };
    }
}

if (!function_exists('comp')) {
    function comp(...$fs): callable
    {
        return static fn (...$x) => fold('compose', 'id')($fs)(...$x);
    }
}

if (!function_exists('concat')) {
    function concat(...$xs)
    {
        switch (true) {
            case all('isString')($xs): return join("", $xs);
            case all('isDict')($xs):
            case all('isList')($xs): return array_merge(...$xs);
            default:
                typeErr(
                    'concat({arg}): expected all elements the same type - list/dict or string',
                    $xs
                );
        }
    }
}

if (!function_exists('map')) {
    function map(callable $f): callable
    {
        return static function (...$xs) use ($f): array {
            switch (true) {
                case all('isList')($xs):
                    $min = min(array_map('count', $xs));

                    if (eq($min, 0)) return [];

                    return fold(
                        static function ($x, $n) use ($f, $xs) {
                            $x[] = $f(...array_column($xs, $n));
                            return $x;
                        },
                        []
                    )(range(0, $min - 1));
                default: typeErr("map({arg}): expected homogeneous list", $xs);
            }
        };
    }
}

if (!function_exists('flat')) {
    function flat(int $depth = PHP_INT_MAX): callable
    {
        return static function (array $xs) use ($depth): array {
            return isList($xs)
                ? array_reduce(
                    $xs,
                    static fn ($a, $b) =>
                        $depth === -1
                            ? $a
                            : array_merge(
                                $a,
                                is_array($b) ? flat($depth - 1)($b) : [$b]
                            ),
                    []
                )
                : typeErr('flat(... {arg}): arg2 expected list', $xs);
        };
    }
}

if (!function_exists('sortBy')) {
    function sortBy(callable $f): callable
    {
        return static function (array $xs) use ($f): array {
            if (isDict($xs))
                typeErr('sortBy(... {arg}): arg2 expected list', $xs);

            $zs = $xs;
            usort($zs, $f);
            return $zs;
        };
    }
}

if (!function_exists('slice')) {
    function slice(int $limit, int $offset = 0): callable
    {
        return static function ($xs) use ($limit, $offset) {
            switch (true) {
                case isString($xs):
                    return (string) substr($xs, $offset, $limit);
                case isList($xs) || isDict($xs):
                    return array_slice($xs, $offset, $limit);
                default:
                    typeErr(
                        'slice(... {arg}): arg3 expected list/dict or string',
                        $xs
                    );
            }
        };
    }
}

if (!function_exists('uniq')) {
    function uniq($xs): array
    {
        switch (true) {
            case isList($xs): return vals(array_unique($xs));
            case isDict($xs): return array_unique($xs);
            default: typeErr('uniq({arg}): arg1 expected list or dict', $xs);
        }
    }
}

if (!function_exists('has')) {
    function has($x): callable
    {
        return static function ($xs) use ($x): bool {
            if (isString($xs) && !isString($x))
                typeErr(
                    'has({arg} ...): arg1 expected string when arg2 is string',
                    $x
                );

            switch (true) {
                case isString($xs):
                    return strpos($xs, $x) === false ? false : true;
                case isList($xs): return in_array($x, $xs, true);
                default:
                    typeErr(
                        'has(... {arg}): arg2 expected list or string',
                        $xs
                    );
            }
        };
    }
}

if (!function_exists('chunk')) {
    function chunk(int $n): callable
    {
        return static function ($xs) use ($n): array {
            $_chunk = static function ($xs, $zs = []) use (&$_chunk, $n) {
                if (empty($xs)) return $zs;

                return $_chunk(
                    array_slice($xs, $n),
                    [...$zs, array_slice($xs, 0, $n)]
                );
            };

            switch (true) {
                case isString($xs):
                    return comp(
                        map(partial('join', '')),
                        $_chunk,
                        'str_split'
                    )($xs);
                case isList($xs): return $_chunk($xs);
                default:
                    typeErr(
                        'chunk(... {arg}): arg2 expected list or string',
                        $xs
                    );
            }
        };
    }
}

if (!function_exists('pluck')) {
    function pluck($x): callable
    {
        if (!(isInt($x) || isString($x)))
            typeErr('pluck({arg} ...): arg1 expected integer or string', $x);

        return static fn (array $xs): array =>
            all('isList')($xs) || all('isDict')($xs)
                ? array_column($xs, $x)
                : typeErr(
                    'pluck(... {arg}): arg2 expected list with homogeneous lists or dicts',
                    $xs
                );
    }
}

if (!function_exists('select')) {
    function select(array $keys): callable
    {
        return static fn (array $xs): array =>
            isList($xs) && (all('isList')($xs) || all('isDict')($xs))
                ? fold(
                    static function ($_xs, $k) use ($xs) {
                        $_xs[$k] = array_column($xs, $k);
                        return $_xs;
                    },
                   []
                )($keys)
                : typeErr(
                    'select(... {arg}): arg2 expected list with homogeneous lists or dicts',
                    $xs
                );
    }
}

if (!function_exists('diff')) {
    function diff(array $xs): callable
    {
        return static function (array $zs) use ($xs): array {
            switch (true) {
                case isList($xs) && isList($zs):
                    return array_diff($xs, $zs);
                case isDict($xs) && isDict($zs):
                    return array_diff_assoc($xs, $zs);
                default:
                    typeErr(
                        'diff(... {arg}): arg2 must be the same type as arg1',
                        $zs
                    );
            }
        };
    }
}

if (!function_exists('intersect')) {
    function intersect(array $xs): callable
    {
        return static function (array $zs) use ($xs): array {
            switch (true) {
            case isList($xs) && isList($zs):
                return array_intersect($xs, $zs);
            case isDict($xs) && isDict($zs):
                return array_intersect_assoc($xs, $zs);
            default:
                typeErr(
                    'intersect(... {arg}): arg2 must be the same type as arg1',
                    $zs
                );
            }
        };
    }
}

