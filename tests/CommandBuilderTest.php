<?php

namespace Karakani\MeCab;

use PHPUnit\Framework\TestCase;

class CommandBuilderTest extends TestCase
{
    public function testNormal()
    {
        $builder = new CommandBuilder();

        touch('path');
        chmod('path', 0755);
        touch('rc;file');
        touch("user\ndic");
        mkdir('dic dir');

        $command = $builder->setBinPath('path')
            ->setDicDir('dic dir')
            ->setRcFile('rc;file')
            ->setUserDic("user\ndic")
            ->build();

        unlink('path');
        unlink('rc;file');
        unlink("user\ndic");
        rmdir('dic dir');

        $this->assertEquals([
            'path',
            '--rcfile=rc;file',
            '--dicdir=dic dir',
            "--userdic=user\ndic",
        ], $command);
    }
}
