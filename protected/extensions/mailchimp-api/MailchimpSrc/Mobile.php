<?php


namespace MailchimpSrc;
use Mailchimp\Mailchimp;


class Mailchimp_Mobile {
    public function __construct(Mailchimp $master) {
        $this->master = $master;
    }

}


