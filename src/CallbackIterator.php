<?php

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
     * CallbackIterator constructor.
     * @param callable $callback
     */
    public function __construct($callback)
    {
        if (!is_callable($callback)) {
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
            $this->current = call_user_func($this->callback);
        } catch (StopIterateException $exception) {
            $this->valid = false;
        }
    }

    public function rewind()
    {
        if ($this->started) {
            throw new \LogicException('Cannot traverse an already closed iterator');
        }
    }

    public function valid()
    {
        return $this->valid;
    }

    public function stopIterate()
    {
        throw new StopIterateException();
    }
}