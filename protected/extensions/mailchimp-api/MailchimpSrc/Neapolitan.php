<?php

namespace MailchimpSrc;
use Mailchimp\Mailchimp;

class Mailchimp_Neapolitan {
    public function __construct(Mailchimp $master) {
        $this->master = $master;
    }

}


