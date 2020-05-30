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

    public function testUnexpected()
    {
        $input = 'one,two';

        $feature = Feature::fromCsv($input);

        $this->assertNull($feature->pos);
        $this->assertEquals($input, $feature->raw);
    }
}
