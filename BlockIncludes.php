<?php

namespace Edev\Resource\Display\Parser\Block;

use Edev\Resource\Display\Template\Templater;

class BlockIncludes
{

    private $includes;
    private $downstream;
    private $upstream;

    private $incPrefix = 'template/';
    private $incSuffix = '.html';

    public function __construct($includes, $downstream, $data)
    {
        $this->includes = $includes ?: [];
        $this->downstream = $downstream;
        $this->data = $data;
    }

    public function parse()
    {
        $str = $this->downstream;


        foreach ($this->includes as $include) {
            $key = $include['key'];
            $hash = $include['hash'];
            $incFilename = $this->incPrefix . $key . $this->incSuffix;
            $str = Templater::parse(
                $str,
                [
                    $hash => (new BlockParser(
                        new BlockGetter($incFilename),
                        $this->data,
                        'isInclude'
                    ))->render(false)
                ]
            );
        }
        return $str;
    }
}
