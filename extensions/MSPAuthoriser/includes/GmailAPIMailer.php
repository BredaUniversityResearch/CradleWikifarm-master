<?php

class GmailAPIMailer {
	/* 
		requires a bunch of classes, use these composer (getcomposer.org) commands to get them:

		composer require swiftmailer/swiftmailer
		composer require hybridauth/hybridauth
	*/
	private $_db, $_config, $_username, $_client_id, $_client_secret;
	public $adapter, $headers, $subject, $body, $to, $from;

	public function __construct() {
		$lb = MediaWikiServices::getInstance()->getDBLoadBalancer();
		$this->_db = $lb->getConnectionRef( DB_REPLICA );

		// all below should and can be changed/configured
		$this->_username = 'webmaster@mspchallenge.org';
		$this->_client_id = '850011550167-8i4hb4ji0shajdg3qu23u4j4bm1ubkd4.apps.googleusercontent.com';
		$this->_client_secret = '2Mg6ZaMZr4a72TjPMEaDW6cM';
		$this->_config = [
			'callback' => 'http://localhost:8000/usersc/gmailcallback.php',
			'keys'     => [
							'id' => $this->_client_id,
							'secret' => $this->_client_secret
						],
			'scope'    => 'https://mail.google.com',
			'authorize_url_parameters' => [
					'approval_prompt' => 'force', // to pass only when you need to acquire a new refresh token.
					'access_type' => 'offline'
			]
		];
		$this->adapter = new Hybridauth\Provider\Google($this->_config);
	}

	public function Send() {
		if (empty($this->subject) || empty($this->body) || empty($this->to) || empty($this->from)) {
			return false;
		}

		if (is_string($this->from)) $this->from = array($this->from => $this->from );
		if (is_string($this->to)) $this->to = array($this->to => $this->to);
	
		try {
			$transport = (new Swift_SmtpTransport('smtp.googlemail.com', 465, 'ssl'))
				->setAuthMode('XOAUTH2')
				->setUsername($this->_username)
				->setPassword($this->getAccessToken());
	
			// Create the Mailer using your created Transport
			$mailer = new Swift_Mailer($transport);
	
			// Create a message
			$message = (new Swift_Message($this->subject))
				->setFrom($this->from)
				->setTo($this->to)
				->setBody($this->body)
				->addParameterizedHeader($this->headers)
				->setContentType('text/html');
	
			// Send the message
			$mailer->send($message);
			return true;
		} catch (Exception $e) {
			if( !$e->getCode() ) { // no code? then the access token just needs to be updated
				$refresh_token = $this->getRefreshToken();
	
				$response = $this->adapter->refreshAccessToken([
					"grant_type" => "refresh_token",
					"refresh_token" => $refresh_token,
					"client_id" => $this->_client_id,
					"client_secret" => $this->_client_secret,
				]);
				
				$data = (array) json_decode($response);
				$data['refresh_token'] = $refresh_token;
	
				$this->updateAccessToken(json_encode($data));
	
				$this->Send();
			} else {
				throw new Exception($e->getMessage()); // oh, then something else went wrong, just just throw as usual
			}
		}
	}

	public function isTokenEmpty() {
		$count = $this->_db->selectRowCount("google_oauth", " *", ["provider" => "google"]); //"SELECT * FROM google_oauth WHERE provider = 'google'"
        if($count > 0) {
            return false;
        }
        return true;
    }

	private function getProviderReturn() {
		$result = $this->_db->selectRow("google_oauth", "provider_value", ["provider" => "google"]); //SELECT provider_value FROM google_oauth WHERE provider='google'
        return json_decode($result->provider_value);
	}
  
    private function getAccessToken() {
        $result = $this->getProviderReturn();
		return $result->access_token;
    }
  
    private function getRefreshToken() {
        $result = $this->getProviderReturn();
        return $result->refresh_token;
    }
  
    public function updateAccessToken($token) {
        if($this->isTokenEmpty()) {
            $this->_db->insert("google_oauth", ["provider" => "google", "provider_value" => $token]); //"INSERT INTO google_oauth(provider, provider_value) VALUES('google', '$token')"
        } else {
            $this->_db->update("google_oauth", ["provider_value" => $token], ["provider" => "google"]); //"UPDATE google_oauth SET provider_value = '$token' WHERE provider = 'google'"
        }
    }

}