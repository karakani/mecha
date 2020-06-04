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

    public function testStreamWrite()
    {
        $fp = fopen('php://memory', 'r+');

        $proc = new BidirectionalStreamProcess($fp);

        $text = "これはストリームへの書き込みテストです\n";

        $proc->fwrite($text);

        rewind($fp);

        $retrieved = fgets($fp);

        $this->assertEquals($text, $retrieved);
    }

    public function testStreamRead()
    {
        $fp = fopen('php://memory', 'r+');

        $proc = new BidirectionalStreamProcess($fp);

        $text = "これはストリームへの読み込みテストです\n";

        fwrite($fp, $text);
        rewind($fp);

        $retrieved = $proc->fgets();

        $this->assertEquals($text, $retrieved);
    }
}
