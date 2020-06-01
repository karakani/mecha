<?php

namespace Karakani\MeCab;

require_once __DIR__ . '/CallAndResponseMock.php';

use PHPUnit\Framework\TestCase;

class TaggerTest extends TestCase
{
    public function testSumomo()
    {
        $tagger = Tagger::create(CommandRunner::createWithExistingProcess(new CallAndResponseMock()));

        $result = $tagger->parse('すもももももももものうち');

        $count = 0;
        $sentences = [];
        foreach ($result as $item) {
            $count++;
            $sentences[] = $item;
        }
        $this->assertEquals(1, $count);

        $this->assertCount(7, $sentences[0]);
        $this->assertEquals(false,  $sentences[0][0]->isUnknown);
    }

    public function testUnknownKeyword()
    {
        exec('command -v mecab', $out, $exitcode);

        $useMecabCommand = ($exitcode === 0);

        $runner = $useMecabCommand
            ? CommandRunner::create((new CommandBuilder())->outputUnknownKeyword()->build())
            : CommandRunner::createWithExistingProcess(new CallAndResponseMock());

        $tagger = Tagger::create($runner);

        $result = $tagger->parse('思い出のタキファソを探して');

        $count = 0;
        $sentences = [];
        foreach ($result as $item) {
            $count++;
            $sentences[] = $item;
        }
        $this->assertEquals(1, $count);
        $this->assertCount(6, $sentences[0]);

        $this->assertEquals('タキファソ',  $sentences[0][2]->surface);
        $this->assertEquals(true,  $sentences[0][2]->isUnknown);

    }
}
