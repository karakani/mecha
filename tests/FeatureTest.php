<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class FeatureTest extends TestCase
{
    public function testNormal()
    {
        $feature = Feature::fromCsv('名詞,一般,*,*,*,*,すもも,スモモ,スモモ');

        $this->assertEquals('名詞', $feature->pos);
        $this->assertEquals('一般', $feature->pos_sub1);
        $this->assertEquals(null, $feature->pos_sub2);
        $this->assertEquals(null, $feature->pos_sub3);
        $this->assertEquals(null, $feature->conjugation_form);
        $this->assertEquals(null, $feature->conjugation);
        $this->assertEquals('すもも', $feature->lexical);
        $this->assertEquals('スモモ', $feature->yomi);
        $this->assertEquals('スモモ', $feature->pronunciation);
    }

    public function testWithAdditionalParam()
    {
        $feature = Feature::fromCsv('名詞,一般,*,*,*,*,すもも,スモモ,スモモ,追加1,追加2');

        $this->assertEquals('名詞', $feature->pos);
        $this->assertEquals('一般', $feature->pos_sub1);
        $this->assertEquals(null, $feature->pos_sub2);
        $this->assertEquals(null, $feature->pos_sub3);
        $this->assertEquals(null, $feature->conjugation_form);
        $this->assertEquals(null, $feature->conjugation);
        $this->assertEquals('すもも', $feature->lexical);
        $this->assertEquals('スモモ', $feature->yomi);
        $this->assertEquals('スモモ', $feature->pronunciation);

        $this->assertEquals(['追加1', '追加2'], $feature->additionalParams);
    }

    public function testContainsLatinWord()
    {
        $feature = Feature::fromCsv('名詞,固有名詞,組織,*,*,*,*');

        $this->assertEquals('名詞', $feature->pos);
        $this->assertEquals('固有名詞', $feature->pos_sub1);
        $this->assertEquals('組織', $feature->pos_sub2);
        $this->assertNull($feature->pos_sub3);
        $this->assertNull($feature->conjugation_form);
        $this->assertNull($feature->conjugation);
        $this->assertNull($feature->lexical);
        $this->assertNull($feature->yomi);
        $this->assertNull($feature->pronunciation);
        $this->assertNull($feature->additionalParams);
    }

    /**
     * 通常とは異なる形式のデータが存在する場合の取り扱いのテスト
     *
     * Featureはカラム位置のみによりデータの設定を行う。
     *
     */
    public function testUnexpected()
    {
        $input = 'one,two';

        $feature = Feature::fromCsv($input);

        $this->assertEquals('one', $feature->pos);
        $this->assertEquals('two', $feature->pos_sub1);
        $this->assertEquals($input, $feature->raw);
    }

    public function testPropertyAccessViolation()
    {
        $feature = Feature::fromCsv('名詞,一般,*,*,*,*,すもも,スモモ,スモモ,追加1,追加2');

        $this->expectException(\Exception::class);
        $feature->noSuchProperty++;
    }
}
