<?php

namespace MediaWiki\Extension\MSPAuthoriser;

use MediaWiki\MediaWikiServices;
use PluggableAuth as PluggableAuthBase;
use PluggableAuthLogin;
use User;

class PluggableAuth extends PluggableAuthBase {

  public function authenticate( &$id, &$username, &$realname, &$email, &$errorMessage ) {
    global $wgServer;
    $authManager = MediaWikiServices::getInstance()->getAuthManager();
    $jwt = $authManager->getAuthenticationSessionData("jwt");

    $url = 'https://auth.mspchallenge.info/usersc/plugins/apibuilder/authmsp/checkjwt.php';
  	$arraySend = array (
  			"jwt" => $jwt,
  			"audience" => "http://localhost");
  	$data2send = json_encode($arraySend);
  	$curl = curl_init();
  	curl_setopt($curl, CURLOPT_URL, $url);
  	curl_setopt($curl, CURLOPT_POSTFIELDS, $data2send);
  	curl_setopt($curl, CURLOPT_POST, 1);
  	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
  	$result = curl_exec($curl);
  	curl_close($curl);
  	$resultDecoded = json_decode($result);
    
  	if($resultDecoded->success) {
        $username = $resultDecoded->userdata->username;
        $checklocaluser = User::newFromName( $username );
        $id = $checklocaluser->getId();
        if ($id == 0) {
          $id = null; // this is what PluggableAuth really wants, apparently
        }
        $realname = $resultDecoded->userdata->fname." ".$resultDecoded->userdata->lname;        
        $email = $resultDecoded->userdata->email;
        return true;
    }
    return false;
  }

  public function saveExtraAttributes( $id ) {



  }

  public function deauthenticate( User &$user ) {



  }

}

 ?>
