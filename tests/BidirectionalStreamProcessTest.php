<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class BidirectionalStreamProcessTest extends TestCase
{

    public function testGetStatus()
    {
        $fp = fopen('php://memory', 'r+');

        $proc = new BidirectionalStreamProcess($fp);

        $stat = $proc->getStatus();

        $this->assertTrue($stat['running']);
        $this->assertNull($stat['exitcode']);

        $proc->close();;

        $stat = $proc->getStatus();

        $this->assertFalse($stat['running']);
        $this->assertEquals(0, $stat['exitcode']);
    }
}
