<?php


namespace Karakani\MeCab;


class Tagger
{
    /** @var CommandRunner */
    static protected $staticRunner;

    /** @var CommandRunner */
    protected $runner;

    /**
     * MeCabインスタンスを作成する。
     *
     * $runner を指定しない場合、単一の mecab プロセスを使用するシングルトンの
     * CommandRunner を使用する。コマンドラインオプションを指定して使用する場合には、
     * setDefaultRunner() メソッドを使用して CommandRunner を使用すると良い。
     *
     * コマンドラインオプションが異なる場合にのみ、このメソッドで $runner を指定すると良い。
     *
     * @param CommandRunner|null $runner
     * @return Tagger
     */
    static public function create(CommandRunner $runner = null)
    {
        $mecab = new self();

        $mecab->runner = $runner ?: self::getDefaultStaticRunner();

        return $mecab;
    }

    /**
     * @return CommandRunner
     */
    static private function getDefaultStaticRunner()
    {
        if (!self::$staticRunner) {
            self::$staticRunner = CommandRunner::create((new CommandBuilder())->build());
        }

        return self::$staticRunner;
    }

    /**
     * デフォルトで使用される CommandRunner を設定する
     * @param CommandRunner $runner
     */
    static public function setDefaultRunner(CommandRunner $runner)
    {
        self::$staticRunner = $runner;
    }

    /**
     * @param string $text
     * @return NodeGroup[]
     */
    public function parse(string $text): \Generator
    {
        $sentences = $this->runner->analyze($text);

        foreach ($sentences as $sentence) {
            $nodes = [];
            foreach ($sentence as $line) {
                if ($line === "EOS")
                    continue;

                $node = Node::createNodeFromLine($line);
                $nodes[] = $node;
            }

            yield new NodeGroup($nodes);
        }
    }
}
