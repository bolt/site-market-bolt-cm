<?php
namespace Bolt\Extensions\Helper;
use forxer\Gravatar\Gravatar;


class Bolt extends \Twig_Extension
{

    public $statusTemplate = '<span class="buildstatus label radius %s"><i class="fi-%s has-tip" data-tooltip title="%s"></i><sup><small>%s+</small></sup></span>';

    public function getFunctions()
    {
        return array(
            'buildStatus'  => new \Twig_Function_Method($this, 'buildStatus',['is_safe' => ['html']]),
            'gravatar'  => new \Twig_Function_Method($this, 'gravatar',['is_safe' => ['html']])
        );
    }
    
    public function getFilters()
    {
        return array(
            'humanTime'  => new \Twig_SimpleFilter('humanTime', [$this, 'humanTime'])
        );
    }


    public function buildStatus($build, $options = [])
    {
        if(!$build || $build->testStatus === 'pending') {
            return sprintf($this->statusTemplate, 'warning', 'clock', "This version is currently awaiting a test result", "");
        }
        
        if($build->phpTarget) {
            $php = str_replace('php', '', $build->phpTarget);
            $php = substr_replace($php, ".", 1, 0);
        } else {
            $php = "";
        }

        
        if($build->testStatus === 'approved') {
            return sprintf($this->statusTemplate, 'success', 'star', "This version is an approved build", $php);
        }
        
        if($build->testStatus === 'failed') {
            return sprintf($this->statusTemplate, 'alert', 'x', "This version is not an approved build", $php);
        }
    }
    
    public function gravatar($email, $options = [])
    {
        return Gravatar::image($email);
    }
    
    public function humanTime($time)
    {
        if ($time instanceof \DateTime) {
           $time = $time->getTimestamp(); 
        }
        $time = time() - $time; // to get the time since that moment

        $tokens = array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );

        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

    }

    public function getName()
    {
        return 'bolt_helper';
    }

}
