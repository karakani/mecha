<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class NodeTest extends TestCase
{
    public function testNormal()
    {
        $node = Node::createNodeFromLine("foo\tbar");

        $this->assertEquals('foo', $node->surface);
        $this->assertInstanceOf(Feature::class, $node->feature);

        $this->assertEquals('bar', $node->feature->raw);
    }
}
