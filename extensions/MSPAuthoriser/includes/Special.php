<?php
use MediaWiki\MediaWikiServices;

class SpecialGmailAPIPermission extends SpecialPage {
	
	
	
	function __construct() {
	
	}

	function execute( $par ) {
		$request = $this->getRequest();
		$output = $this->getOutput();
		$user = $output->getUser();

        if ( in_array( 'sysop', $user->getEffectiveGroups())) {

            try {
                $mailer = new GmailAPIMailer;
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
	}
	
}
