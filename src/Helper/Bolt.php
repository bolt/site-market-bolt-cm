<?php
namespace Bolt\Extensions\Helper;


class Bolt extends \Twig_Extension
{

    public $statusTemplate = '<span class="buildstatus label radius %s"><i class="fi-%s has-tip" data-tooltip title="%s"></i></span>';

    public function getFunctions()
    {
        return array(
            'buildStatus'  => new \Twig_Function_Method($this, 'buildStatus',['is_safe' => ['html']])
        );
    }


    public function buildStatus($build, $options = [])
    {
        if(!$build || $build->testStatus === 'pending') {
            return sprintf($this->statusTemplate, 'alert', 'clock', "This version is currently awaiting a test result");
        }
        
        if($build->testStatus === 'approved') {
            return sprintf($this->statusTemplate, 'success', 'star', "This version is an approved build");
        }
        
        if($build->testStatus === 'failed') {
            return sprintf($this->statusTemplate, 'alert', 'x', "This version is not an approved build");
        }
    }

    public function getName()
    {
        return 'bolt_helper';
    }

}
