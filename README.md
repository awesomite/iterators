# Iterators

[![Codacy Badge](https://api.codacy.com/project/badge/Grade/54a2ff29245e43fa9768edfe7495bd4b)](https://www.codacy.com/app/awesomite/iterators?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=awesomite/iterators&amp;utm_campaign=Badge_Grade)
[![Coverage Status](https://coveralls.io/repos/github/awesomite/iterators/badge.svg?branch=master)](https://coveralls.io/github/awesomite/iterators?branch=master)
[![Build Status](https://travis-ci.org/awesomite/iterators.svg?branch=master)](https://travis-ci.org/awesomite/iterators)

## CallbackIterator

`CallbackIterator` allows to simulate `yield` feature from PHP 5.5.

### PHP >= 5.5
```php
<?php

function getAllFromDatabase($tableName)
{
    $page = 0;
    $perPage = 1000;
    while ($rows = Db::getRows($tableName, $page, $perPage)) {
        foreach ($rows as $row) {
            yield $row;
        }
        $page++;
    }
}
```

### PHP < 5.5
```php
<?php

use Awesomite\Iterators\CallbackIterator;

function getAllFromDatabase($tableName)
{
    $page = 0;
    $perPage = 1000;
    $rows = [];

    return new CallbackIterator(function () use (&$rows, $tableName, &$page, $perPage) {
        if (!$rows) {
            $rows = Db::getRows($tableName, $page, $perPage);
            $page++;
        }

        if ($rows) {
            return array_shift($rows);
        }

        CallbackIterator::stopIterate();
    });
}
```