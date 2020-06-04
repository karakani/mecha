<?php


namespace Karakani\MeCab;


class BidirectionalStreamProcess
{
    public function __construct($bidi)
    {
        $this->stdin = $bidi;
        $this->stdout = $bidi;
    }

    public function close()
    {
        // stdin も stdout も同一ストリームのため一方のみを閉鎖する
        fclose($this->stdin);

        $this->stdin = null;
        $this->stdout = null;
    }

    public function getStatus(): array
    {
        return [
            'running' => $this->stdin !== null, // クローズしていない場合には常にオープンされているものと見做す
            'exitcode' => $this->stdin === null ? 0 : null, // すでにクローズ済みの場合には常に0を返す
        ];
    }
}
