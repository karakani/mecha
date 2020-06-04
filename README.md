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
$tagger = Tagger::create();
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
$command = (new CommandBuilder())
    ->setBinPath('/usr/local/bin/mecab')
    ->setUserDic('/usr/local/lib/mecab/dic/mecab-ipadic-neologd')
    ->build();
$runner = CommandRunner::create($command);

Tagger::setDefaultRunner($runner);

$tagger = Tagger::create();
```

複数のインスタンスで異なるコマンドラインオプションを使用する場合。

```php
$runnerWithDefaultOption = CommandRunner::create();
$taggerA = Tagger::create($runnerWithDefaultOption);

$runnerWithCustomOption = CommandRunner::create(
    (new CommandBuilder())
        ->setBinPath('/usr/local/bin/mecab')
        ->setUserDic('/usr/local/lib/mecab/dic/mecab-ipadic-neologd')
        ->build()
);
$taggerB = Tagger::create($runnerWithCustomOption);
```

コマンドラインの作成に `CommandBuilder` を使用しない場合。

```php
$runner = CommandRunner::create([
    '/usr/local/bin/mecab',
    '--dicdir=/usr/local/lib/mecab/dic/mecab-ipadic-neologd',
]);

Tagger::setDefaultRunner($runner);
```

### mecab プロセスを終了する

バックグラウンドで動作するプロセスを明示的に終了する場合。

```php
Tagger::getDefaultRunner()->close();
```

但し、通常はスクリプト終了時に自動的に終了するため、必要がない限りこのメソッドを呼び出す必要はありません。


### ストリームを使用してプロセス間通信(IPC)をする

ソケットを使用してプロセス間通信(IPC)を行いたい場合には `BidirectionalStreamProcess`
を使って次のように実装します。

```php
<?php

use Karakani\MeCab\CommandBuilder;
use Karakani\MeCab\CommandProcess;
use Karakani\MeCab\CommandRunner;
use Karakani\MeCab\BidirectionalStreamProcess;
use Karakani\MeCab\Tagger;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * CommandProcess から標準出力用ストリームを取得できるようにする
 */
class ExtendedCommandProcess extends CommandProcess
{
    public function getStdoutStream()
    {
        return $this->stdout;
    }
}

// プロセス間通信を行えるよう、ソケットを作成する。
$sockets = stream_socket_pair(STREAM_PF_UNIX, STREAM_SOCK_STREAM, STREAM_IPPROTO_IP);
$pid = pcntl_fork();

if ($pid == -1) {
    die('フォークできません');

} else if ($pid) {
    // 親プロセス (クライアント. テストのため標準入力/出力を使用したいので親プロセスをクライアント側として使用する)
    fclose($sockets[1]);

    // StreamProcess を継承したソケットを使用する Client クラスを使用して Tagger を作成する
    $clientRunner = new BidirectionalStreamProcess($sockets[0]);
    $tagger = Tagger::create(CommandRunner::createWithExistingProcess($clientRunner));

    // 標準入力を1行ずつ問い合わせるサンプル
    error_log('テキストを入力してください:');
    $stdin = fopen('php://stdin', 'r');
    while ($line = fgets($stdin)) {
        // 通常通り、 parseメソッドを呼び出す
        $parseResult = $tagger->parse($line);

        // ソケット経由でデータが返されるので、その結果を表示する
        foreach ($parseResult as $nodeGroup) {
            foreach ($nodeGroup as $node) {
                /** @var \Karakani\MeCab\Node $node */
                printf("%s(%s)", $node->surface, $node->feature->pos);
            }
        }
        printf(PHP_EOL);
    }

} else {
    // 子プロセス (サーバー. mecabプロセスが動作する)
    fclose($sockets[0]);

    $sock = $sockets[1];
    $commandProcess = new ExtendedCommandProcess; // 後に stream_select を利用できるよう、標準出力のストリームを取得する
    $commandProcess->open((new CommandBuilder())->build());
    $commandStdout = $commandProcess->getStdoutStream();

    while (true) {
        $w = [];
        $r = [$sock, $commandStdout];
        $x = [];

        // データが利用可能になるまで待機する
        $numReady = stream_select($r, $w, $x, 10);

        if (in_array($sock, $r)) { // クライアントデータが利用可能
            $data = fgets($sock);
            $commandProcess->fwrite($data); // mecabプロセスの標準入力に書き込む
        }

        if (in_array($commandStdout, $r)) { // 標準出力が利用可能
            $data = $commandProcess->fgets(); // mecabのプロセスの標準出力からデータを読み込む
            fwrite($sock, $data);
        }
    }
}
```
