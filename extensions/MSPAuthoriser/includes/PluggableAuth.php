<?php

namespace MediaWiki\Extension\MSPAuthoriser;

use MediaWiki\Auth\AuthManager;
use PluggableAuth as PluggableAuthBase;
use PluggableAuthLogin;
use User;

class PluggableAuth extends PluggableAuthBase {

  public function authenticate( &$id, &$username, &$realname, &$email, &$errorMessage ) {
    $authManager = AuthManager::singleton();
    $extraLoginFields = $authManager->getAuthenticationSessionData(
      PluggableAuthLogin::EXTRALOGINFIELDS_SESSION_KEY
    );
    $username = $extraLoginFields["username"];
    $password = $extraLoginFields["password"];

    $url = 'https://auth.mspchallenge.info/usersc/plugins/apibuilder/authmsp/checkuser.php';
  	$arraySend = array (
  			"username" => $username,
  			"password" => $password);
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
        $checklocaluser = User::newFromName( $username );
        $id = $checklocaluser->getId();
        if ($id == 0) $id = null; // this is what PluggableAuth really wants, apparently
        $realname = $resultDecoded->userdata->fname." ".$resultDecoded->userdata->lname;
        $username = $resultDecoded->userdata->username;
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
