<?php


namespace Karakani\MeCab;


class NodeGroup implements \IteratorAggregate, \Countable
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
}
