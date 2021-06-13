# PHP standard library that we want

> Auto-included when used with composer

## Docs

### `head`

Returns first element of indexed array or string.

```php
<?php

head([3,4,5,6]); // -> 3
head([3]); // -> 3
head([]); // -> throw type error
head(''); // -> throw type error
head('hello'); // -> 'h'
head('h'); // -> 'h'

snd([3,4,5,6]); // -> 4
snd([3]); // -> throw type error
snd([]); // -> throw type error
snd(''); // -> throw type error
snd('hello'); // -> 'e'
snd('h'); // -> throw type error
```

### `tail`

Returns all elements except first of indexed array or string.

```php
<?php

tail([3,4,5,6]); // -> [4,5,6]
tail([3]); // -> []
tail([]); // -> []
tail(''); // -> ''
tail('hello'); // -> 'ello'
tail('h'); // -> ''
```

### `last`

Returns last element of indexed array or string.

```php
<?php

last([3,4,5,6]); // -> 6
last([3]); // -> 3
last([]); // -> throw type error
last(''); // -> throw type error
last('hello'); // -> 'o'
last('h'); // -> 'h'
```

### `init`

Returns all elements except last of indexed array or string.

```php
<?php

init([3,4,5,6]); // -> [3,4,5]
init([3]); // -> []
init([]); // -> []
init(''); // -> ''
init('hello'); // -> 'hell'
init('h'); // -> ''
```

### `len`

Returns length of array or string.

```php
<?php

len(['name' => 'John', 'age' => 21]); // -> 2
len([3,4,5,6]); // -> 4
len([3]); // -> 1
len([]); // -> 0
len(''); // -> 0
len('h'); // -> 1
```

### `reverse`

Returns reversed indexed array or string.

```php
<?php

reverse([3,4,5,6]); // -> [6,5,4,3]
reverse([3]); // -> [3]
reverse([]); // -> []
reverse(''); // -> ''
reverse('hello'); // -> 'olleh'
```

### `fold`

Accumulates values into value using a function and initial value.

```php
<?php

function sum($a, $b) {
    return $a + $b;
}

fold('sum', 0)([1,2,3,4]); // -> 10
```

### `reduce`

Accumulates values into value using a function.

```php
<?php

function sum($a, $b) {
    return $a + $b;
}

reduce('sum')([1,2,3,4]); // -> 10
```

### `filter`

Filters values using a predicate function.

```php
<?php

function isEven($a) {
    return $a % 2 === 0;
}

filter('isEven')([1,2,3,4]); // -> [2,4]
```

### `map`

Applies a function to each element in array.

```php
<?php

function addTwo($a) {
    return $a + 2;
}

map('addTwo')([1,2,3,4]); // -> [3,4,5,6]
```

### `partial`

Returns partial applied function.

```php
<?php

function sum($a, $b) {
    return $a + $b;
}

$addTen = partial('sum', 10);
map($addTen)([1,2,3,4,5]); // -> [11,12,13,14,15]
```

### `comp`

Returns functions composition.

```php
<?php

function double($a) {
    return $a * 2;
}

$getSecondDoubledElement = comp(
    'head',
    'tail',
    partial('map', 'double')
);

$getSecondDoubledElement([1,2,3,4]); // -> 4
```


## Undocumented (WIP)

### `set`
### `get`
### `slice`
### `all`
### `any`
### `has`
### `select`
### `concat`
### `pluck`
### `maybe`
### `chunk`
### `diff`
### `intersect`
### `uniq`
### `vals`
### `keys`
### `eq`
### `not`
### `lt`
### `lte`
### `gt`
### `gte`
### `id`
### `cnst`
### `flip`
### `tee`
### `apply`
### `applyTo`
### `spread`
### `flat`
### `each`
### `findKey`
### `sortBy`
