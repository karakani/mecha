<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class CommandRunnerTest extends TestCase
{
    /**
     * @var bool
     */
    private $useMecabCommand;

    protected function setUp()
    {
        exec('command -v mecab', $out, $exitcode);

        $this->useMecabCommand = ($exitcode === 0);
    }

    protected function createMeCabRunner()
    {
        if ($this->useMecabCommand) {
            return CommandRunner::create(['mecab']);
        } else {
            return CommandRunner::createWithExistingProcess(new CallAndResponseMock());
        }
    }

    public function testSingleSentence()
    {
        $executor = $this->createMeCabRunner();

        $result = $executor->analyze("すもももももももものうち");

        // 分析行が配列として帰っていること
        $this->assertEquals([
            "すもも	名詞,一般,*,*,*,*,すもも,スモモ,スモモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "の	助詞,連体化,*,*,*,*,の,ノ,ノ",
            "うち	名詞,非自立,副詞可能,*,*,*,うち,ウチ,ウチ",
            "EOS",
        ], $result->current());
    }

    public function testMultipleAnalyzeInvocation()
    {
        $executor = $this->createMeCabRunner();

        $result = $executor->analyze("すもももももももものうち");

        // 文の数が1件であること
        $this->assertEquals([
            "すもも	名詞,一般,*,*,*,*,すもも,スモモ,スモモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "の	助詞,連体化,*,*,*,*,の,ノ,ノ",
            "うち	名詞,非自立,副詞可能,*,*,*,うち,ウチ,ウチ",
            "EOS",
        ], $result->current());

        $result = $executor->analyze("山田太郎");

        // 文の数が1件であること/2回目の呼び出しが正常終了すること
        $this->assertEquals([
            "山田	名詞,固有名詞,人名,姓,*,*,山田,ヤマダ,ヤマダ",
            "太郎	名詞,固有名詞,人名,名,*,*,太郎,タロウ,タロー",
            "EOS",
        ], $result->current());
    }

    public function testMultipleSentences()
    {
        $executor = $this->createMeCabRunner();

        $result = $executor->analyze(implode(PHP_EOL, ["山田太郎", "すもももももももものうち"]));

        $this->assertEquals([
            "山田	名詞,固有名詞,人名,姓,*,*,山田,ヤマダ,ヤマダ",
            "太郎	名詞,固有名詞,人名,名,*,*,太郎,タロウ,タロー",
            "EOS",
        ], $result->current());

        $result->next();

        $this->assertEquals([
            "すもも	名詞,一般,*,*,*,*,すもも,スモモ,スモモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "も	助詞,係助詞,*,*,*,*,も,モ,モ",
            "もも	名詞,一般,*,*,*,*,もも,モモ,モモ",
            "の	助詞,連体化,*,*,*,*,の,ノ,ノ",
            "うち	名詞,非自立,副詞可能,*,*,*,うち,ウチ,ウチ",
            "EOS",
        ], $result->current());
    }

    public function testProcessResponseTimeout()
    {
        $executor = CommandRunner::create(['sleep', 10]);
        $executor->setStdoutTimeoutSec(1);

        $this->expectExceptionCode(CommandRunner::EXCEPTION_PROCESS_TIMEOUT);
        $result = $executor->analyze("すもももももももものうち");

        // generatorであるので、処理を動かすために current を呼び出す
        $result->current();
    }

    public function testUnexpectedResponse()
    {
        $executor = CommandRunner::create(['sh', '-c', "echo \"unexpected\nresponse\"; sleep 10"]);
        $executor->setStdoutTimeoutSec(1);

        $this->expectExceptionCode(CommandRunner::EXCEPTION_PROCESS_TIMEOUT);
        $result = $executor->analyze("すもももももももものうち");

        // generatorであるので、処理を動かすために current を呼び出す
        $result->current();
    }

    public function testIncorrectCommand()
    {
        $executor = CommandRunner::create(['false']);
        $executor->setStdoutTimeoutSec(1);

        $this->expectExceptionCode(CommandRunner::EXCEPTION_COMMAND_INCORRECT);
        $result = $executor->analyze("すもももももももものうち");

        // generatorであるので、処理を動かすために current を呼び出す
        $result->current();
    }

    public function testFastExit()
    {
        $executor = CommandRunner::create(['true']);
        $executor->setStdoutTimeoutSec(1);

        $this->expectExceptionCode(CommandRunner::EXCEPTION_COMMAND_TERMINATED);
        $result = $executor->analyze("すもももももももものうち");

        // generatorであるので、処理を動かすために current を呼び出す
        $result->current();
    }
}
