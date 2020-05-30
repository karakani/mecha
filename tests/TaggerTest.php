<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class TaggerTest extends TestCase
{
    public function testSumomo()
    {
        $tagger = Tagger::create();

        $result = $tagger->parse('すもももももももものうち');

        $count = 0;
        $sentences = [];
        foreach ($result as $item) {
            $count++;
            $sentences[] = $item;
        }
        $this->assertEquals(1, $count);

        $this->assertCount(7, $sentences[0]);
    }
}
