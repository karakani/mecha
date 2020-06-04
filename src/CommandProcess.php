<?php


namespace Karakani\MeCab;


/**
 * proc_* wrapper
 * @package Karakani\MeCab
 */
class CommandProcess
{
    const STDIN = 0;
    const STDOUT = 1;

    /** @var resource */
    protected $fp;
    /** @var resource */
    protected $stdout;
    /** @var resource */
    protected $stdin;

    public function open(array $command)
    {
        $safeCommand = array_map(function ($str) {
            return escapeshellarg($str);
        }, $command);

        $pipes = [];

        $this->fp = proc_open(implode(' ', $safeCommand), [
            0 => ['pipe', 'r'],
            1 => ['pipe', 'w'],
        ], $pipes);

        $this->stdout = $pipes[self::STDOUT];
        $this->stdin = $pipes[self::STDIN];
    }

    public function getStatus()
    {
        return proc_get_status($this->fp);
    }

    public function fgets()
    {
        return fgets($this->stdout);
    }

    public function fwrite($text)
    {
        return fwrite($this->stdin, $text);
    }

    public function select(int $timeoutSec): int
    {
        $_r = [$this->stdout];
        $_w = [];
        $_e = [];
        return stream_select($_r, $_w, $_e, $timeoutSec);
    }

    public function close()
    {
        if ($this->fp === null)
            return;

        proc_terminate($this->fp);

        fclose($this->stdout);
        fclose($this->stdin);
        proc_close($this->fp);

        $this->pipes = null;
        $this->fp = null;
    }

    public function __destruct()
    {
        $this->close();
    }
}
