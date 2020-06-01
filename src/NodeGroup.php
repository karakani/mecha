<?php


namespace Karakani\MeCab;


class NodeGroup implements \IteratorAggregate, \Countable, \ArrayAccess
{
    protected $nodes = [];

    public function __construct(array $nodes)
    {
        $this->nodes = $nodes;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->nodes);
    }

    public function count()
    {
        return count($this->nodes);
    }

    public function offsetExists($offset)
    {
        return isset($this->nodes[$offset]);
    }

    public function offsetGet($offset)
    {
        return $this->nodes[$offset] ?: null;
    }

    public function offsetSet($offset, $value)
    {
        throw new \Exception('You cannot update NodeGroup');
    }

    public function offsetUnset($offset)
    {
        throw new \Exception('You cannot update NodeGroup');
    }
}
