<?php
use MediaWiki\MediaWikiServices;

class SpecialGmailAPIPermission extends SpecialPage {
	
	
	
	function __construct() {
        parent::__construct( 'GmailAPIPermission' );
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$user = $output->getUser();

        if ( in_array( 'sysop', $user->getEffectiveGroups())) {

            try {
                $mailer = new \MediaWiki\Extension\MSPAuthoriser\GmailAPIMailer;
                $mailer->adapter->authenticate();
                $token = $mailer->adapter->getAccessToken();
                $mailer->updateAccessToken(json_encode($token));
                $this->setHeaders();
                $output->addHTML("<h2>Access token inserted successfully.</h2>");
            }
            catch( Exception $e ){
                echo $e->getMessage();
            }

        }

        $output->addHTML("<h2>Sending test mail to warmelink.h@buas.nl...</h2>");
        $mailer = new \MediaWiki\Extension\MSPAuthoriser\GmailAPIMailer;
        $mailer->headers = [];
        $mailer->to = ["warmelink.h@buas.nl" => "Harald Warmelink"];
        $mailer->from = "webmaster@mspchallenge.org";
        $mailer->subject = "test mail automatically sent";
        $mailer->body = "test mail sent through Special:GmailAPIPermission. if you receive this, then the Gmail API was successfully used to send an email! yay!";
        $mailer->Send();
	}
	
}
