<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class NodeGroupTest extends TestCase
{
    public function testIterable()
    {
        $instance = new NodeGroup(range(0, 9));

        $count = 0;
        foreach ($instance as $item) {
            $count++;
        }

        $this->assertEquals(10, $count);
    }
}
