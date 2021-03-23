<?php

namespace Edev\Resource\Display\Parser\Block;

use Edev\Resource\Display\Template\Templater;
use \Edev\Resource\Display\Parser\Block\BlockGetter;

class BlockParser
{

    // pointer to BlockGetter()
    private $blockgetter;

    // the view data object on the page
    protected $data;

    // property string divided as upstream and downstream. downstream is
    // yet to be parsed. upstream is once all block calcs and loops are
    // complete and has been parsed with all block values
    protected $downstream;
    protected $upstream;

    // no need for has as of yet, other than by the python level parser
    protected $hash;

    // blocks are any command blocks, ie, if statements, foreach loops
    protected $blocks;

    /**
     * All the parsing and building comes in the construtor. The user
     * decides if the render is echo'ed or returned for later use
     *
     * @param \BlockGetter  $blockgetter
     * @param array         $data
     */
    public function __construct(BlockGetter $blockgetter, $data)
    {
        $this->blockgetter = $blockgetter;
        $this->data = $data;
        $this->_parseIncomingJson($this->blockgetter->get());
        $this->_buildStream();
        $this->_handleIncludes();
    }

    /**
     * Output the upstream value. By default, the upstream value is 
     * echo'ed. User can pass `false` if they want to just return the
     * upstream, usually used when creating a timeline for later 
     * rendering
     *
     * @param boolean $render
     * 
     * @return void
     */
    public function render($render = true)
    {
        if (!$render) {
            return $this->_returnStreamAsString();
        }
        return $this->_renderStream();
    }

    /**
     * Incoming json has three props: `downstream`, `hash`, `blocks`. Hash
     * is for comparison purposes at the server level, for the python
     * engine to check if it needs to reformat the file
     *
     * @param string $jsonObj  - formatted JSON file output by a python 
     *                           script
     * 
     * @return void
     */
    private function _parseIncomingJson($jsonObj)
    {
        extract(json_decode($jsonObj, true));
        $this->downstream = $string;
        $this->hash = $hash;
        $this->blocks = $blocks;
        $this->includes = $includes;
    }

    /**
     *
     * @return string
     */
    private function _returnStreamAsString()
    {
        return $this->upstream;
    }

    /**
     *
     * @return void
     */
    private function _renderStream()
    {
        echo $this->upstream;
    }

    /**
     * Foreach found block, create a new Block() object to parse its own
     * output
     *
     * @return void
     */
    private function _buildStream()
    {
        $output = [];
        // $hash value is the key for the blocks, and matches the place-
        // holder in the `stream` property, which allows its output to 
        // be templated back into the output string
        foreach ($this->blocks as $hash => $block) {
            $output[$hash] = (new Block($block, $this->data))->getOutput();
        }
        $this->upstream = Templater::parse($this->downstream, array_merge($this->data, $output));
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function _handleIncludes()
    {
        if (is_null($this->includes) || empty($this->includes)) {
            return;
        }
        $this->upstream = (new BlockIncludes(
            $this->includes,
            $this->upstream,
            $this->data
        ))->parse();
    }
}
