<?php

/*
 * This file is part of the awesomite/iterators package.
 *
 * (c) Bartłomiej Krukowski <bartlomiej@krukowski.me>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Awesomite\Iterators;

/**
 * Use instead of "yield" keyword to keep backward compatibility
 */
class CallbackIterator implements \Iterator
{
    private $callback;

    private $current;

    private $key;

    private $valid = true;

    private $started = false;

    /**
     * @throws StopIterateException
     */
    public static function stopIterate()
    {
        throw new StopIterateException();
    }

    /**
     * @param callable $callback
     */
    public function __construct($callback)
    {
        if (!\is_callable($callback)) {
            throw new \InvalidArgumentException("Callback must be callable!");
        }

        $this->callback = $callback;
        $this->next();
        $this->started = false;
        $this->key = 0;
    }

    public function current()
    {
        return $this->current;
    }

    public function key()
    {
        return $this->key;
    }

    public function next()
    {
        try {
            $this->key++;
            $this->started = true;
            $this->current = \call_user_func($this->callback);
        } catch (StopIterateException $exception) {
            $this->valid = false;
        }
    }

    public function rewind()
    {
        if ($this->started) {
            throw new \LogicException(\sprintf('Cannot rewind an already opened instance of %s', __CLASS__));
        }
    }

    public function valid()
    {
        return $this->valid;
    }
}
