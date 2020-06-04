<?php

namespace Karakani\MeCab;

class CallAndResponseMock extends CommandProcess
{
    protected $q = 0;

    protected $respQ = [];

    private $myKnowledge = [
        "すもももももももものうち" => <<<EOM
すもも	名詞,一般,*,*,*,*,すもも,スモモ,スモモ
も	助詞,係助詞,*,*,*,*,も,モ,モ
もも	名詞,一般,*,*,*,*,もも,モモ,モモ
も	助詞,係助詞,*,*,*,*,も,モ,モ
もも	名詞,一般,*,*,*,*,もも,モモ,モモ
の	助詞,連体化,*,*,*,*,の,ノ,ノ
うち	名詞,非自立,副詞可能,*,*,*,うち,ウチ,ウチ
EOS
EOM
        ,
        "山田太郎" => <<<EOM
山田	名詞,固有名詞,人名,姓,*,*,山田,ヤマダ,ヤマダ
太郎	名詞,固有名詞,人名,名,*,*,太郎,タロウ,タロー
EOS
EOM
        ,
        "思い出のタキファソを探して" => <<<EOM
思い出	名詞,一般,*,*,*,*,思い出,オモイデ,オモイデ
の	助詞,連体化,*,*,*,*,の,ノ,ノ
タキファソ	未知語(UNKNOWN)
を	助詞,格助詞,一般,*,*,*,を,ヲ,ヲ
探し	動詞,自立,*,*,五段・サ行,連用形,探す,サガシ,サガシ
て	助詞,接続助詞,*,*,*,*,て,テ,テ
EOS
EOM
        ,
    ];

    public function fwrite($input)
    {
        foreach (explode(PHP_EOL, trim($input)) as $text) {
            if (!isset($this->myKnowledge[$text])) throw new \Exception('Unknown input: ' . $text);

            foreach (explode(PHP_EOL, $this->myKnowledge[$text]) as $line) {
                $this->q++;
                array_push($this->respQ, $line . PHP_EOL);
            }
        }
    }

    public function fgets()
    {
        $this->q--;

        return array_shift($this->respQ);
    }

    public function select(int $timeoutSec): int
    {
        return $this->q > 0 ? 1 : 0;
    }

    /**
     * always response with dummy
     * @return array
     */
    public function getStatus(): array
    {
        return [
            'running' => true
        ];
    }
}
