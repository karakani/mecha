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

    public function testArrayAccess()
    {
        $instance = new NodeGroup(range(0, 9));

        $this->assertTrue(isset($instance[9]));
        $this->assertFalse(isset($instance[10]));
    }

    public function testOverrideItems()
    {
        $instance = new NodeGroup(range(0, 9));

        $this->expectException(\Exception::class);
        $instance[5] = -1; // 更新できず例外が投げられる
    }

    public function testOverrideItem2()
    {
        $instance = new NodeGroup(range(0, 9));

        $this->expectException(\Exception::class);
        $instance[] = 1; // 更新できず例外が投げられる
    }

    public function testDeleteItems()
    {
        $instance = new NodeGroup(range(0, 9));

        $this->expectException(\Exception::class);
        unset($instance[9]);
    }
}
