<?php


namespace Karakani\MeCab;


abstract class StreamProcess
{
    /** @var resource */
    protected $stdin;
    /** @var resource */
    protected $stdout;

    /**
     * ストリームを安全に終了するためのメソッド
     * @return void
     */
    abstract public function close();

    /**
     * 接続状態を含む連想配列を返す
     *
     * この連想配列には次を含む
     * - running: (boolean) 実行中であるかどうか
     * - exitcode: 終了コード. 正常時=0, 異常時=それ以外の値, 利用不能=-1
     *
     * @return array
     */
    abstract public function getStatus(): array;

    public function fwrite($text)
    {
        return fwrite($this->stdin, $text);
    }

    public function fgets()
    {
        return fgets($this->stdout);
    }

    public function select(int $timeoutSec): int
    {
        $_r = [$this->stdout];
        $_w = [];
        $_e = [];
        return stream_select($_r, $_w, $_e, $timeoutSec);
    }
}
