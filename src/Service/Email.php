<?php

namespace Bolt\Extension\Bolt\MarketPlace\Service;

/**
 * Email handling class.
 *
 * @author Ross Riley <riley.ross@gmail.com>
 */
class Email
{
    public $html = '';
    public $text = '';
    public $subject = '';
    public $from_email = '';
    public $from_name = '';
    public $to = [];
    public $headers = [];
    public $important = false;
    public $track_opens;
    public $track_clicks;
    public $auto_text;
    public $auto_html;
    public $inline_css;
    public $url_strip_qs;
    public $preserve_recipients;
    public $view_content_link;
    public $bcc_address;
    public $tracking_domain;
    public $signing_domain;
    public $return_path_domain;

    public $subaccount;
    public $google_analytics_domains;
    public $google_analytics_campaign;
    public $attachments = [];
    public $images = [];

    public function addTo($email, $name, $type = 'to')
    {
        $this->to[] = ['email' => $email, 'name' => $name, 'type' => $type];
    }
}
