<?php

namespace Karakani\MeCab;

require_once __DIR__ . '/CallAndResponseMock.php';

use PHPUnit\Framework\TestCase;

class CommandProcessTest extends TestCase
{
    public function testCat()
    {
        $proc = new CommandProcess();
        $proc->open(['cat']);

        $stat = $proc->getStatus();

        // プロセスは起動していること
        $this->assertTrue($stat['running']);

        $input = "message\n"; // 改行コードはバッファをクリアさせるために設定が必要

        $proc->fwrite($input);
        $wait = $proc->select(1);

        $this->assertEquals(1, $wait);

        $output = $proc->fgets();

        $this->assertEquals($input, $output);

        $proc->close();
    }
}
