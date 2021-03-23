<?php

namespace Edev\Resource\Display\Parser\Block;


/**
 * BlockGetter returns `filename.html.json` contents.
 * 
 * Checks if block.json file exists and if it's "fresh", i.e., has been
 * modified AFTER the original HTML file has been modified. If it's not
 * fresh, it calls the shell script ./cgi-bin/block_templater.sh which
 * task the HTMLPATH as an argument and calls the python templater app.
 * 
 * @author Jeffery Edick <jeff@jasoncases.com>
 * @since 0.2.0
 *
 * */
class BlockGetter
{
    // grab system config details for filesystem from the sysetm session
    private $config;

    // pointers for paths to html and .json template
    private $htmlpath;
    private $blockpath;

    // set .json suffix. This is likely overkill, but elims a magic str
    private $blocksuffix = '.json';

    // current roots w/ filesystem template, allowing sharded overlap
    private $blockroot = '/home/edev/public_html/view_templates/';
    private $htmlroot = '/home/edev/public_html/View/';

    public function __construct($htmlpath)
    {
        $this->htmlpath = $this->_createHtmlPath($htmlpath);
        $this->blockpath = $this->_createBlockPath($htmlpath);
    }

    public function get()
    {
        try {
            if (!file_exists($this->blockpath) || !$this->_blockIsFresh()) {
                $create = $this->_createBlock();

                // check inverse of the boolval, !null = true, !str = f
                if (!boolval($create)) {
                    // and throw the exception, providing the error
                    throw new \Exception($create);
                };
            }
            return $this->_getBlock();
        } catch (\Exception $e) {
            die($e->getMessage());
        }
    }

    /**
     * Check the file modified time of the block.json template against
     * the modified time of the given HTML file. Return false if the 
     * html file is more recently modified, initiating a call to the 
     * python templater 
     * 
     * @return bool
     */
    private function _blockIsFresh()
    {
        return filemtime($this->blockpath) > filemtime($this->htmlpath);
    }

    /**
     * Returns the output of the shell script, which is the output of
     * the python templater app. If templater is successful, shell_exec
     * output is null, otherwise we return the exception
     *
     * @return bool
     */
    private function _createBlock()
    {
        return shell_exec(
            '/home/edev/public_html/cgi-bin/block_templater.sh ' . $this->htmlpath
        );
    }

    /**
     * Return file contents
     *
     * @return string (JSON)
     */
    private function _getBlock()
    {
        return file_get_contents($this->blockpath);
    }

    /**
     *
     * @param string $str
     * @return string
     */
    private function _createBlockPath($str)
    {
        $str .= $this->blocksuffix;
        return $this->blockroot . $str;
    }

    /**
     *
     * @param string $str
     * @return string
     */
    private function _createHtmlPath($str)
    {
        return $this->htmlroot . $str;
    }
}
