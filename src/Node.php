<?php


namespace Karakani\MeCab;

/**
 * Class Node
 * @package Karakani\MeCab
 * @property-read string $surface
 * @property-read Feature $feature
 * @property-read bool $isUnknown
 */
class Node
{
    /** @var string */
    protected $surface = null;
    /** @var Feature */
    protected $feature = null;
    /** @var bool */
    protected $isUnknown;

    /**
     * @param $name
     * @return mixed
     * @throws \Exception アクセスが許可されていないプロパティにアクセスしようとした場合
     */
    public function __get($name)
    {
        $allowed = ['surface', 'feature', 'isUnknown'];
        if (!in_array($name, $allowed))
            throw new \Exception("${name} is not accessible");

        return $this->$name;
    }

    public static function createNodeFromLine(string $line): Node
    {
        list($surface, $feature) = explode("\t", $line, 2);

        $node = new self();
        $node->surface = $surface;
        $node->isUnknown = $feature === CommandBuilder::UNK_STR;

        if ($feature !== null and !$node->isUnknown)
            $node->feature = Feature::fromCsv($feature);

        return $node;
    }
}
