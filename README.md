# mecha-mocha
PHP Wrapper for MeCab

[![Build Status](https://travis-ci.org/karakani/mecha-mocha.svg?branch=master)](https://travis-ci.org/karakani/mecha-mocha)
[![Coverage Status](https://coveralls.io/repos/github/karakani/mecha-mocha/badge.svg?branch=master)](https://coveralls.io/github/karakani/mecha-mocha?branch=master)

## About

mecab コマンドのラッパー用スクリプトです。

php-mecab extension を使うと設定ミスなどでSegmentation Faultが発生して困る場合などに使ってください。

複数回連続して呼び出す場合のために、バックグラウンドでプロセスを起動させて再利用しています。

## インストール

```shell script
composer require karakani/mecha-mocha
```

## 使い方

### 基本的な使い方

```php
$tagger = \Karakani\MeCab\Tagger::create();
$nodeGroups = $tagger->parse("すもももももももものうち");

foreach ($nodeGroups as $nodeGroup) {
    foreach ($nodeGroup as $node) {
        printf("%s(%s)\n", $node->surface, $node->feature->pos);
    }
}

// 次のように出力されます:
//
// すもも(名詞)
// も(助詞)
// もも(名詞)
// も(助詞)
// もも(名詞)
// の(助詞)
// うち(名詞)
```

### コマンドラインオプションを指定する

デフォルトのコマンドラインオプションを指定する場合。

```php
$command = (new \Karakani\MeCab\CommandBuilder())
    ->setBinPath('/usr/local/bin/mecab')
    ->setUserDic('/usr/local/lib/mecab/dic/mecab-ipadic-neologd')
    ->build();
$runner = \Karakani\MeCab\CommandRunner::create($command);

\Karakani\MeCab\Tagger::setDefaultRunner($runner);

$tagger = \Karakani\MeCab\Tagger::create();
```

複数のインスタンスで異なるコマンドラインオプションを使用する場合。

```php
$runnerWithDefaultOption = \Karakani\MeCab\CommandRunner::create();
$taggerA = \Karakani\MeCab\Tagger::create($runnerWithDefaultOption);

$runnerWithCustomOption = \Karakani\MeCab\CommandRunner::create(
    (new \Karakani\MeCab\CommandBuilder())
        ->setBinPath('/usr/local/bin/mecab')
        ->setUserDic('/usr/local/lib/mecab/dic/mecab-ipadic-neologd')
        ->build()
);
$taggerB = \Karakani\MeCab\Tagger::create($runnerWithCustomOption);
```

コマンドラインの作成に `CommandBuilder` を使用しない場合。

```php
$runner = \Karakani\MeCab\CommandRunner::create([
    '/usr/local/bin/mecab',
    '--dicdir=/usr/local/lib/mecab/dic/mecab-ipadic-neologd',
]);

\Karakani\MeCab\Tagger::setDefaultRunner($runner);
```

### mecab プロセスを終了する

バックグラウンドで動作するプロセスを明示的に終了する場合。

```php
\Karakani\MeCab\Tagger::getDefaultRunner()->close();
```

但し、通常はスクリプト終了時に自動的に終了するため、必要がない限りこのメソッドを呼び出す必要はありません。
