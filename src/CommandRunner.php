<?php


namespace Karakani\MeCab;


use Exception;

class CommandRunner
{
    const EXCEPTION_PROCESS_TIMEOUT = 10001;
    const EXCEPTION_COMMAND_TERMINATED = 10003;
    const EXCEPTION_COMMAND_INCORRECT = 10005;

    // テスト不能な例外
    const EXCEPTION_READ_FAILURE = 10002;
    const EXCEPTION_WRITE_FAILURE = 10004;
    const EXCEPTION_UNKNOWN = 10999;

    /** @var string[] */
    private $commands = null;
    /** @var CommandProcess */
    private $process;
    /** @var int */
    private $stdout_timeout_sec = 5;
    /** @var bool */
    private $dont_close_process_on_exit = false;

    public function __destruct()
    {
        $this->close();
    }

    /**
     * @param int $stdout_timeout_sec
     */
    public function setStdoutTimeoutSec(int $stdout_timeout_sec)
    {
        $this->stdout_timeout_sec = $stdout_timeout_sec;
    }

    static public function create(array $command = ['mecab']): CommandRunner
    {
        $executor = new self();
        $executor->commands = $command;
        return $executor;
    }

    /**
     * CommandProcess を使用してインスタンスを作成する。
     *
     * プロセスを共有して使用する場合に使用する。このメソッドを使用して作成した場合、 close後に
     * プロセスを停止させない。
     *
     * @param CommandProcess $process
     * @return CommandRunner
     */
    static public function createWithExistingProcess(CommandProcess $process): CommandRunner
    {
        $executor = new self();
        $executor->process = $process;
        $executor->dont_close_process_on_exit = true;
        return $executor;
    }

    private function activateProcess()
    {
        if ($this->process === null) {
            // mecab は標準エラー出力を行わない (標準出力にエラーを出力する)
            $this->process = new CommandProcess();
            $this->process->open($this->commands);
        }
    }

    /**
     * @param CommandProcess $process
     */
    public function setActiveProcess(CommandProcess $process)
    {
        $this->process = $process;
    }

    private function assertProcessAlive()
    {
        if ($this->process === null) {
            throw new Exception(
                '!!possible bug!! process is not running!',
                self::EXCEPTION_UNKNOWN);
        }

        $stat = $this->process->getStatus();
        if ($stat['running'] === false) {
            $this->close();
            if ($stat['exitcode'] == 0) {
                throw new Exception(
                    "unexpectedly process is terminated (exit: ${stat['exitcode']})",
                    self::EXCEPTION_COMMAND_TERMINATED
                );
            } else {
                throw new Exception(
                    "unexpectedly process is terminated (incorrect command. exit: ${stat['exitcode']})",
                    self::EXCEPTION_COMMAND_INCORRECT
                );
            }
        }
    }

    public function close()
    {
        if ($this->process === null) {
            return;
        } else if ($this->dont_close_process_on_exit) {
            return;
        }

        $this->process->close();
        $this->process = null;
    }

    /**
     * プロセスの標準入力にテキストを送信し、mecabに解析処理させる。
     *
     * テキストは改行ごとに1文とし、"EOS"が出力されるまで解析終了を待機する。
     *
     * 1文ごとに配列の結果として返す。
     *
     * @param string $text 解析対象のテキスト
     * @return \Generator 解析結果を返すジェネレーター。各要素ごとに文章の結果行の配列を含む。
     * @throws Exception 処理がタイムアウトとなった場合。
     */
    public function analyze(string $text): \Generator
    {
        // 改行コードを正規化する
        $text = self::normalizeText($text);

        // 文の数を数える
        $sentenceSize = mb_substr_count($text, PHP_EOL) + 1;

        // (もし起動していなければ)プロセスを起動する
        $this->activateProcess();

        // プロセスが動作しているか確認する
        $this->assertProcessAlive();

        // テキストを送信する
        $w = $this->process->fwrite($text . PHP_EOL);
        if ($w === false) {
            // 書き込みが失敗する可能性は低い: プロセスが終了している場合には、
            // 上の assertProcessAlive で例外が発生するため
            $this->close();
            throw new Exception(
                'Failed to write text into mecab process!!',
                self::EXCEPTION_WRITE_FAILURE
            );
        }

        // 結果を受け取る
        $pendingLines = [];
        $analyzed = 0;
        while ($sentenceSize > $analyzed) {
            // ストリームが呼び出し可能となるまで待機する
            $changed = $this->process->select($this->stdout_timeout_sec);
            if ($changed === 0) {
                /* 次の場合に取得結果が得られない可能性がある
                 * - タイムアウト
                 * - 結果の整合性が取れない場合 (=本来期待すべき回数を超えて fgets を呼び出し、タイムアウトとなる)
                 */
                $this->close();;
                throw new Exception(
                    "mecab does not respond in $this->stdout_timeout_sec secs.",
                    self::EXCEPTION_PROCESS_TIMEOUT
                );
            }

            // プロセスが動作しているか確認する
            $this->assertProcessAlive();

            $line = $this->process->fgets();

            if ($line === false) {
                // 通常はこの処理は行われない: もしデータが取得できない場合には stream_select が
                // 0 を返し、タイムアウト扱いとなるため
                $this->close();;
                throw new Exception(
                    'Failed to read output from mecab process',
                    self::EXCEPTION_READ_FAILURE
                );
            } else if ($line === "EOS\n") { // end of sentence
                $pendingLines[] = trim($line);

                // clear buffer
                $analysis = $pendingLines;
                $pendingLines = [];
                $analyzed++;

                yield $analysis;
            } else {
                $pendingLines[] = trim($line);
            }
        }
    }

    static private function normalizeText(string $text): string
    {
        $text = str_replace("\r\n", PHP_EOL, $text);
        $text = str_replace("\r", PHP_EOL, $text);

        return $text;
    }
}
