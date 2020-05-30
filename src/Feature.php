<?php


namespace Karakani\MeCab;

/**
 * Class Feature
 * @package Karakani\MeCab
 * @property-read string $raw
 * @property-read string $pos
 * @property-read string $pos_sub1
 * @property-read string $pos_sub2
 * @property-read string $pos_sub3
 * @property-read string $conjugation_form
 * @property-read string $conjugation
 * @property-read string $lexical
 * @property-read string $yomi
 * @property-read string $pronunciation
 * @property-read string $additionalParams
 */
class Feature
{
    protected $raw;

    protected $pos;
    protected $pos_sub1;
    protected $pos_sub2;
    protected $pos_sub3;
    protected $conjugation_form;
    protected $conjugation;
    protected $lexical;
    protected $yomi;
    protected $pronunciation;
    protected $additionalParams;

    public static function fromCsv(string $input): Feature
    {
        $feature = new self();
        $feature->raw = $input;

        $components = explode(',', $input);
        if (count($components) < 8)
            // 既定の件数に見たない場合にはこれ以上処理を行わない
            return $feature;

        $fixed = array_splice($components, 0, 9);

        list(
            $feature->pos,
            $feature->pos_sub1,
            $feature->pos_sub2,
            $feature->pos_sub3,
            $feature->conjugation_form,
            $feature->conjugation,
            $feature->lexical,
            $feature->yomi,
            $feature->pronunciation
            ) = $fixed;

        $keys = [
            'raw',
            'pos',
            'pos_sub1',
            'pos_sub2',
            'pos_sub3',
            'conjugation_form',
            'conjugation',
            'lexical',
            'yomi',
            'pronunciation',
        ];
        foreach ($keys as $key) {
            if ($feature->$key == '*')
                $feature->$key = null;
        }

        $feature->additionalParams = count($components) ? $components : null;

        return $feature;
    }

    public function __get($name)
    {
        $allowed = [
            'raw',
            'pos',
            'pos_sub1',
            'pos_sub2',
            'pos_sub3',
            'conjugation_form',
            'conjugation',
            'lexical',
            'yomi',
            'pronunciation',
            'additionalParams'
        ];

        if (!in_array($name, $allowed))
            throw new \Exception("${name} is not accessible");

        return $this->$name;
    }
}
