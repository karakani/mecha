<?php


namespace Karakani\MeCab;


class CommandBuilder
{
    const UNK_STR = '未知語(UNKNOWN)';
    protected $binpath = 'mecab';
    protected $rcfile = null;
    protected $dicdir = null;
    protected $userdic = null;
    /**
     * @var bool 未知語の推定を行わない
     */
    protected $unkFeature = false;

    /**
     * use $file as resource file
     * @param string $file
     * @return $this
     */
    public function setRcFile(string $file): CommandBuilder
    {
        if (!file_exists($file))
            throw new \Exception('rcfile is not exists: ' . $file);

        $this->rcfile = $file;
        return $this;
    }

    /**
     * set $dir as a system dicdir
     * @param string $dir
     * @return $this
     */
    public function setDicDir(string $dir): CommandBuilder
    {
        if (!is_dir($dir))
            throw new \Exception('dicdir is not exists: ' . $dir);

        $this->dicdir = $dir;
        return $this;
    }

    /**
     * use FILE as a user dictionary
     * @param string $file
     * @return $this
     */
    public function setUserDic(string $file): CommandBuilder
    {
        if (!file_exists($file))
            throw new \Exception('userdic is not exists: ' . $file);

        $this->userdic = $file;
        return $this;
    }

    public function setBinPath($path): CommandBuilder
    {
        $this->binpath = $path;
        return $this;
    }

    /**
     * build safe command
     * All command and options are escaped, so that you can invoke command safely.
     * @return string[]
     */
    public function build(): array
    {
        $command = [$this->binpath];

        if ($this->rcfile)
            $command[] = sprintf('--rcfile=%s', $this->rcfile);
        if ($this->dicdir)
            $command[] = sprintf('--dicdir=%s', $this->dicdir);
        if ($this->userdic)
            $command[] = sprintf('--userdic=%s', $this->userdic);
        if ($this->unkFeature)
            $command[] = sprintf('--unk-feature=%s', self::UNK_STR);

        return $command;
    }
}