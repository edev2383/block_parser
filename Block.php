<?php

namespace Edev\Resource\Display\Parser\Block;

use Edev\Resource\Display\Template\Templater;

use function Edev\System\Helpers\Lib\calc;

class Block
{
    private $block;
    private $data;
    public function __construct($block, $data)
    {
        $this->block = $block;
        $this->data = $data;
    }

    public function getOutput()
    {
        return $this->_parseByType($this->block['type']);
    }

    private function _parseByType($type)
    {
        switch ($type) {
            case 'if':
                return $this->_parseIf($this->data);
            case 'foreach':
                return $this->_parseForeach($this->data);
        }
    }

    private function _parseIf($data)
    {
        $foundValue = $data[$this->block['key']];
        $op = $this->block['operator'];
        $compValue = $this->block['value'];
        if (calc($foundValue, $op, $compValue)) {
            return Templater::parse($this->block['string_if_true'], $data);
        } else {
            return Templater::parse($this->block['string_if_false'], $data);
        }
    }

    /**
     * Handles parsing the foreach blocks
     *
     * @param array $data
     * @return string
     */
    private function _parseForeach($data)
    {
        switch (boolval($this->block["compound"])) {
            case true:
                return $this->_compoundForeach($data);
            case false:
                return $this->_simpleForeach($data);
        }
    }

    private function _simpleForeach($data)
    {
        $upstream = '';
        // extract the target data for this loop by the `key` val
        $array = $data[$this->block['key']] ?: [];
        foreach ($array as $arr) {
            // check if this block has any nested blocks
            if (isset($this->block['blocks'])) {
                // pull out the nested block reference(s)...
                $nestedBlocks = $this->block['blocks'];
                // .. and loop through each
                foreach ($nestedBlocks as $hash => $nBlock) {
                    // call a new block for the nested blocks and push
                    // the output to the arr level data pack to parse it
                    // to the upstream value
                    $arr[$hash] = (new Block($nBlock, $arr))->getOutput();
                }
            }
            // since each iteration is parsed independently, we can do
            // the nested operations specific to each pass and then send
            // the results to the upstream Templater call
            $upstream .= Templater::parse($this->block['string'], $arr);
        }

        // return the resultant string
        return $upstream;
    }

    /**
     * Compound @foreach loops through the array_values, regardless of
     * the key
     *
     * */
    private function _compoundForeach($data)
    {
        $upstream = "";
        $array = $data[$this->block["key"]] ?: [];
        foreach ($array as $item) {
            $tmpArr = [$this->block["item"] => $item];
            $upstream .= Templater::parse($this->block["string"], $tmpArr);
        }
        return $upstream;
    }
}
