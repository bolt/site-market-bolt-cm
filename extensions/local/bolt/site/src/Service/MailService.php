<?php
/**
 * A wrapper class for Mandrill email sender.
 *
 * @author Ross Riley, riley.ross@gmail.com
 */

namespace Bolt\Extension\Bolt\MarketPlace\Service;

use Mandrill;
use Twig_Environment;

class MailService
{
    public $renderer;
    public $mandrill;
    public $mailDefaults;

    public function __construct(Twig_Environment $renderer, Mandrill $mandrill, $mailDefaults)
    {
        $this->renderer = $renderer;
        $this->mandrill = $mandrill;
        $this->mailDefaults = $mailDefaults;
    }

    public function sendTemplate($template, $emailAddress, $toName, array $data)
    {
        if (!$emailAddress) {
            throw new \RuntimeException('An email key must be set on the passed data', 1);
        }
        if (!$toName) {
            throw new \RuntimeException('A name key must be set on the passed data', 1);
        }

        $template = $this->renderer->loadTemplate('emails/' . $template . '.html');

        $email = $this->createEmail();
        $email->addTo($emailAddress, $toName);
        $email->subject  = $template->renderBlock('subject',   $data);
        $email->html  = $template->renderBlock('html_version', $data);
        $email->text  = $template->renderBlock('text_version', $data);

        return $this->deliver($email);
    }

    public function createEmail()
    {
        $email = new Email();
        foreach ($this->mailDefaults as $k => $v) {
            if (property_exists($email, $k)) {
                $email->$k = $v;
            }
        }

        return $email;
    }

    protected function deliver(Email $email)
    {
        $message = (array) $email;
        $result = $this->mandrill->messages->send($message);

        return $result;
    }
}
