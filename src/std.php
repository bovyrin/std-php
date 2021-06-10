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
        return static fn ($a): callable =>
            static fn ($b) => $f($b, $a);
    }
}

if (!function_exists('apply')) {
    function apply(callable $f): callable
    {
        return static fn ($x = null) => $f($x);
    }
}

if (!function_exists('applyTo')) {
    function applyTo($x = null): callable
    {
        return static fn (callable $f) => $f($x);
    }
}

if (!function_exists('compose')) {
    function compose($f, $g): callable
    {
        return static fn ($x = null) => $f($g($x));
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
            && !empty($x)
            && count(
                array_filter($x, 'is_numeric', ARRAY_FILTER_USE_KEY)
            ) === 0;
    }
}

if (!function_exists('isList')) {
    function isList($x): bool {
        return is_array($x) && count(
            array_filter($x, 'is_numeric', ARRAY_FILTER_USE_KEY)
        ) === count($x);
    }
}

if (!function_exists('isObject')) {
    function isObject($x): bool {
        return !is_callable($x) && is_object($x);
    }
}

if (!function_exists('getOr')) {
    function getOr($x): callable {
        return static fn ($z) => isNone($x) ? $x : $z;
    }
}

//
// Logic
//
if (!function_exists('either')) {
    function either(callable $p, callable $f, callable $g): callable
    {
        return static fn($x) => $p($x) ? $f($x) : $g($x);
    }
}

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
if (!function_exists('raiseErr')) {
    function raiseErr(string $msg, array $reason = []): void
    {
        throw new class($msg, $reason) extends \Error
        {
            private $payload;
            function __construct($m, $r)
            {
                $place = $this->getTrace()[0];

                $this->message = join(
                    "",
                    [
                        $m,
                        ' in ',
                        basename(dirname($place['file'])),
                        '/',
                        basename($place['file']),
                        ":{$place['line']}."
                    ]
                );

                $this->reason = $r;
            }
        };
    }
}

if (!function_exists('raiseTypeErr')) {
    function raiseTypeErr(string $msg, int $argN): void
    {
        throw new class($msg, $argN) extends \TypeError {
            function __construct($m, $n)
            {
                $err = $this->getTrace()[1] ?? [];

                $this->message = empty($err)
                    ? $m
                    : $this->message = join(
                        "",
                        [
                            "{$err['function']}() ",
                            "arg {$n}: {$m}. Given ",
                            json_encode(
                                $err['args'][$n - 1] ?? null,
                                JSON_UNESCAPED_UNICODE
                            ),
                            ' in ',
                            basename(dirname($err['file'])),
                            '/',
                            basename($err['file']),
                            ":{$err['line']}."
                        ]
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
            default: raiseTypeErr('expected list/dict or string', 1);
        }
    }
}

if (!function_exists('reverse')) {
    function reverse($xs)
    {
        switch (true) {
            case isString($xs): return strrev($xs);
            case isList($xs): return array_reverse($xs);
            default: raiseTypeErr('expects list or string', 1);
        }
    }
}

if (!function_exists('path')) {
    function path($path): callable
    {
        return static function ($xs) use ($path) {
            if (!(isList($xs) || isDict($xs) || isString($xs))) {
                raiseTypeErr('expected list/dict or string', 2);
            }

            if (isString($xs) && !isInt($path)) {
                raiseTypeErr('expected int when arg2 is string', 1);
            }

            switch (true) {
                case isList($path):
                    $_xs = &$xs;
                    foreach ($path as $k) {
                        if (isset($_xs[$k])) $_xs = &$_xs[$k];
                        else return null;
                    }
                    return $_xs;
                case isString($path) || isInt($path): return $xs[$path] ?? null;
                default: raiseTypeErr('expected string/integer or list', 1);
            }
        };
    }
}

if (!function_exists('assoc')) {
    function assoc($path, callable $f): callable
    {
        return static function ($xs) use ($path, $f): array {
            if (!(isInt($path) || isString($path) || isList($path)))
                raiseTypeErr('expected string/int or list', 1);

            if (!isList($path)) $path = [$path];

            $_assoc = static function ($v) use ($path, $f) {
                $_xs = &$v;
                foreach ($path as $k) {
                    if (!isset($_xs[$k])) $_xs[$k] = [];
                    $_xs = &$_xs[$k];
                }
                $_xs = $f($_xs);

                return $v;
            };

            switch (true) {
                case isList($xs) || isDict($xs): return $_assoc($xs);
                case isString($xs):
                    if (!isInt($path))
                        raiseTypeErr('expected int when arg3 is string', 1);
                    return join("", $_assoc(str_split($xs)));
                default: raiseTypeErr('expected list/dict or string', 3);
            }
        };
    }
}

if (!function_exists('head')) {
    function head($xs)
    {
        switch (true) {
            case !(isString($xs) || isList($xs)):
                raiseTypeErr('expected list or string', 1);
            case eq(len($xs), 0):
                raiseTypeErr('expected not empty list or string', 1);
            case isString($xs): return $xs[0];
            default: return array_slice($xs, 0, 1)[0];
        }
    }
}

if (!function_exists('tail')) {
    function tail($xs)
    {
        switch (true) {
            case isList($xs): return array_slice($xs, 1);
            case isString($xs): return (string) substr($xs, 1);
            default: raiseTypeErr('expected list or string', 1);
        }
    }
}

if (!function_exists('last')) {
    function last($xs)
    {
        switch (true) {
            case !(isString($xs) || isList($xs)):
                raiseTypeErr('expected list or string', 1);
            case eq(len($xs), 0):
                raiseTypeErr('expected not empty list or string', 1);
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
            default: raiseTypeErr('expected list or string', 1);
        }
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

if (!function_exists('forEvery')) {
    function forEvery(callable $f): callable
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
            $x = $v;
            foreach ($xs as $key => $val) {
                $x = $f($x, $val, $key);
            }
            return $x;
        };
    }
}

if (!function_exists('reduce')) {
    function reduce(callable $f): callable
    {
        return static function (array $xs) use ($f) {
            if (eq(len($xs), 0)) raiseTypeErr('expected not empty list/dict', 2);
            return fold($f, head($xs))(tail($xs));
        };
    }
}

if (!function_exists('filter')) {
    function filter(callable $p): callable
    {
        return fold(
            static function ($x, $v, $k) use ($p) {
                if ($p($v, $k)) $x[$k] = $v;
                return $x;
            },
            []
        );
    }
}

if (!function_exists('any')) {
    function any(callable $p): callable
    {
        return fold(
            static fn ($x, $v, $k) => $x || $p($v, $k),
            false
        );
    }
}

if (!function_exists('all')) {
    function all(callable $p)
    {
        return fold(
            static fn ($x, $v, $k) => $x && $p($v, $k),
            true
        );
    }
}

if (!function_exists('comp')) {
    function comp(...$fs): callable
    {
        return fold('compose', 'id')($fs);
    }
}

if (!function_exists('concat')) {
    function concat(...$xs)
    {
        switch (true) {
            case all('isString')($xs): return join("", $xs);
            case all(static fn ($v) => isList($v) || isDict($v))($xs):
                return reduce(static fn ($a, $b) => array_merge($a, $b))($xs);
            default:
                raiseTypeErr('expected all args either list/dict or string', 1);
        }
    }
}

if (!function_exists('map')) {
    function map(callable $f): callable
    {
        return static fn (...$xs): array =>
            fold(
                static function ($x, $n) use ($f, $xs) {
                    $x[] = $f(...array_column($xs, $n));
                    return $x;
                },
                []
            )(range(0, min(array_map('count', $xs)) - 1));
    }
}

if (!function_exists('sortBy')) {
    function sortBy(callable $f, array $xs): array
    {
        $zs = $xs;
        usort($zs, $f);
        return $zs;
    }
}

if (!function_exists('slice')) {
    function slice(int $n1, int $n2, $xs)
    {
        return isList($xs)
            ? array_slice($xs, $n1, $n2)
            : raiseTypeErr('expected list', 3);
    }
}

if (!function_exists('uniq')) {
    function uniq($xs)
    {
        switch (true) {
            case isString($xs):
                return join('', vals(array_unique(str_split($xs))));
            case isList($xs): return vals(array_unique($xs));
            case isDict($xs): return array_unique($xs);
            default: raiseTypeErr('expected list/dict or string', 1);
        }
    }
}

if (!function_exists('has')) {
    function has($x, $xs): bool
    {
        if (!(isList($x) || isString($x)))
            raiseTypeErr('expected list or string', 1);

        $_strCase = static fn ($_x) =>
            strpos($xs, $_x) === false ? false : true;
        $_listCase = static fn ($_x) => in_array($_x, $xs, true);

        switch (true) {
            case isString($xs) && isList($x): return all($_strCase)(uniq($x));
            case isString($xs): return $_strCase($x);
            case isList($xs) && isList($x): return all($_listCase)(uniq($x));
            case isList($xs): return $_listCase($x);
            default: raiseTypeErr('expected list/dict or string', 2);
        }
    }
}

if (!function_exists('chunk')) {
    function chunk(int $n, $xs): array
    {
        switch (true) {
            case isString($xs): $xs = str_split($xs);
            case isList($xs):
                return fold(
                    static fn ($a, $b) =>
                        empty($a) || eq(len(last($a)), $n)
                            ? [...$a, [$b]]
                            : [...init($a), [...last($a), $b]],
                    [],
                )($xs);
            default: raiseTypeErr('expected list or string', 2);
        }
    }
}

if (!function_exists('pluck')) {
    function pluck($x, array $xs): array
    {
        if (isInt($x) || isString($x)) return array_column($xs, $x);

        raiseTypeErr('expected integer or string', 1);
    }
}

if (!function_exists('pick')) {
    function pick(array $keys, array $xs): array
    {
        if (isList($xs) && (all('isDict')($xs) || all('isList')($xs))) {
            return map(
                partial('filter', static fn ($v, $k) => has($k, $keys)),
            )($xs);
        }

        raiseTypeErr('expected nested list or dict', 2);
    }
}

if (!function_exists('diff')) {
    function diff($xs, $zs): array
    {
        switch (true) {
            case isList($xs) && isList($zs): return array_diff($xs, $zs);
            case isDict($xs) && isDict($zs): return array_diff_assoc($xs, $zs);
            case !(isList($xs) || isDict($xs)):
                raiseTypeErr('expected list/dict and the same type as arg 2', 1);
            default:
                raiseTypeErr('expected list/dict and the same type as arg 1', 2);
        }
    }
}

if (!function_exists('intersect')) {
    function intersect($xs, $zs): array
    {
        switch (true) {
            case isList($xs) && isList($zs): return array_intersect($xs, $zs);
            case isDict($xs) && isDict($zs): return array_intersect_assoc($xs, $zs);
            case !(isList($xs) || isDict($xs)):
                raiseTypeErr('expected list/dict and the same type as arg 2', 1);
            default:
                raiseTypeErr('expected list/dict and the same type as arg 1', 2);
        }
    }
}

if (!function_exists('spread')) {
    function spread(callable $f): callable
    {
        return static fn (array $xs) => $f(...$xs);
    }
}

