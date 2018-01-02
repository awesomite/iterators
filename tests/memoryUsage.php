<?php

/*
 * This file is part of the awesomite/iterators package.
 *
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Awesomite\Iterators\CallbackIterator;

require \implode(DIRECTORY_SEPARATOR, array(__DIR__, '..', 'vendor', 'autoload.php'));

$createArrayIterator = function ($length) {
    $data = array();
    for ($i = 0; $i < $length; $i++) {
        $data[] = 0;
    }

    return new \ArrayIterator($data);
};

$createCallbackIterator = function ($length) {
    return new CallbackIterator(function () use (&$length) {
        if (!$length) {
            CallbackIterator::stopIterate();
        }
        $length--;

        return 0;
    });
};

$length = (int)$argv[2];
switch ($argv[1]) {
    case 'callback':
        $iterator = $createCallbackIterator($length);
        break;
    case 'array':
        $iterator = $createArrayIterator($length);
        break;
    default:
        throw new \RuntimeException('Invalid argument!');
}

echo \memory_get_peak_usage();
