# mecha-mocha
PHP Wrapper for MeCab

[![Build Status](https://travis-ci.org/karakani/mecha.svg?branch=master)](https://travis-ci.org/karakani/mecha)
[![Coverage Status](https://coveralls.io/repos/github/karakani/mecha/badge.svg?branch=master)](https://coveralls.io/github/karakani/mecha?branch=master)

## インストール

composer.json に次を追加してください。

```json
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:karakani/mecha-mocha.git"
        }
    ],
```

```shell script
composer require karakani/mecha-mocha
```

## 使い方

```php
$tagger = \Karakani\MeCab\Tagger::create();
$nodeGroups = $tagger->parse("すもももももももものうち");

foreach ($nodeGroups as $nodeGroup) {
    foreach ($nodeGroup as $token) {
        printf("%s(%s)\n", $token->surface, $token->feature->pos);
    }
}
```
