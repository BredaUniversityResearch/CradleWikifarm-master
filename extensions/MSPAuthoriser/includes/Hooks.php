<?php
/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 *
 * @file
 */

namespace MediaWiki\Extension\MSPAuthoriser;

class Hooks {

  public static function onPersonalUrls( array &$personal_urls, \Title $title ) {
    if (!isset($personal_urls['logout'])) {
      $personal_urls['createaccount']['text'] = 'Join!';
      $personal_urls['createaccount']['href'] = 'https://auth.mspchallenge.info/users/join.php?return_url='.htmlentities('https://community.mspchallenge.info');
    }
    return true;
  }

  public static function onAlternateUserMailer( $headers, $to, $from, $subject, $body ) {
    $mailer = new \MediaWiki\Extension\MSPAuthoriser\GmailAPIMailer;
    $mailer->headers = $headers;
    $mailer->to = [$to->address => $to->name];
    $mailer->from = $from;
    $mailer->subject = $subject;
    $mailer->body = $body;
    $mailer->Send();
    return true;
  }

}
