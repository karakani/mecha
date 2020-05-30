<?php


namespace Karakani\MeCab;


class Tagger
{
    /** @var CommandRunner */
    static protected $staticExecutor;

    /** @var CommandRunner */
    protected $executor;

    /**
     * MeCabインスタンスを作成する。
     *
     * $executor を指定しない場合、単一の mecab プロセスを使用するシングルトンの
     * CommandExecutor を使用する。コマンドラインオプションを指定して使用する場合には、
     * setDefaultExecutor() メソッドを使用して CommandExecutor を使用すると良い。
     *
     * コマンドラインオプションが異なる場合にのみ、このメソッドで $executor を指定すると良い。
     *
     * @param CommandRunner|null $executor
     * @return Tagger
     */
    static public function create(CommandRunner $executor = null)
    {
        $mecab = new self();

        $mecab->executor = $executor ?: self::getDefaultStaticExecutor();

        return $mecab;
    }

    /**
     * @return CommandRunner
     */
    static private function getDefaultStaticExecutor()
    {
        if (!self::$staticExecutor) {
            self::$staticExecutor = CommandRunner::create((new CommandBuilder())->build());
        }

        return self::$staticExecutor;
    }

    /**
     * デフォルトで使用される CommandExecutor を設定する
     * @param CommandRunner $executor
     */
    static public function setDefaultExecutor(CommandRunner $executor)
    {
        self::$staticExecutor = $executor;
    }

    /**
     * @param string $text
     * @return NodeGroup[]
     */
    public function parse(string $text): \Generator
    {
        $sentences = $this->executor->analyze($text);

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
