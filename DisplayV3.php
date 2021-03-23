<?php

namespace Edev\Resource\Display;

use Edev\Resource\Display\Parser\Block\BlockGetter;
use Edev\Resource\Display\Parser\Block\BlockParser;

class DisplayV3
{

    private $layoutpath = 'layout/';

    private $config;

    private $layout;
    private $layoutsuffix = '.html';
    private $viewdata;
    private $filepath;

    public function __construct($layout, $filepath, $viewdata = [])
    {
        $this->_mergeViewData($viewdata);
        $this->layout = $this->_acquireLayout($layout);
        $this->filepath = $filepath;
    }

    public function render($render = true)
    {
        $blockParser = new BlockParser(
            new BlockGetter($this->viewpath . $this->filepath),
            $this->viewdata
        );


        if ($render) {
            echo str_replace("@content", $blockParser->render(false), $this->layout);
        } else {
            echo 'testing here....';
            return str_replace("@content", $blockParser->render(false), $this->layout);
        }
    }

    private function _mergeViewData($incomingViewData)
    {
        $this->viewdata = $incomingViewData;
    }

    private function _acquireLayout($layout)
    {
        $layout .= $this->layoutsuffix;
        return (new BlockParser(
            new BlockGetter($this->layoutpath . $layout),
            $this->viewdata
        ))->render(false);
    }
}
