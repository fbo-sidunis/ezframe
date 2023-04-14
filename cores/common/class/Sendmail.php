<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of sendmail
 *
 * @author jreynet
 */
namespace Core\Common;
class Sendmail {

  private $headers = "";
  private $body = "";
  private $fromMail = "";
  private $fromName = "";
  private $to = "";
  private $subject = "";
  private $cc = NULL;

  function __construct($fromName = NULL, $fromMail = NULL, $to = NULL, $subject = "", $msg = "") {
    if (!empty($to)) {
      $this->setTo($to);
    }
    if (!empty($subject)) {
      $this->setSubject($subject);
    }
    if (!empty($msg)) {
      $this->setBody($msg);
    }
    if (!empty($fromName)) {
      $this->setFromName($fromName);
    }
    if (!empty($fromMail)) {
      $this->setFromMail($fromMail);
    }
  }

  private function setHeaders() {

    $headers = "From: " . strip_tags($this->fromName) . '<' . $this->fromMail . '>' . "\r\n";
    $headers .= "Reply-To: " . strip_tags($this->fromMail) . "\r\n";
    if ($this->cc) {
      $headers .= "CC: $this->cc \r\n";
    }
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Date: " . date("r (T)") . " \r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    //$headers .= "Content-Type: text/html; charset=ISO-8859-1\r\n";

    $this->headers = $headers;
  }

  public function setBody($val) {
    $message = '<html><body>';
    $message .= '<div class="body">' . $val . '</div>';
    $message .= '</body></html>';
    $this->body = $message;
  }

  public function setTo($val, $sep = ",") {
    $strTo = is_array($val) ? join($sep, $val) : $val;
    $this->to = $strTo;
  }

  public function setCC($cc = NULL) {
    if (!empty($cc)) {
      $this->cc = $cc;
    }
  }

  public function setFromMail($val) {
    $this->fromMail = $val;
  }

  public function setFromName($val) {
    $this->fromName = $val;
  }

  public function setSubject($val) {
    $this->subject = $val;
  }

  public function send() {
    $this->setHeaders();


    $res = mail(
            $this->to
            , $this->subject
            , $this->body
            , $this->headers
    );

    return array('SEND' => $res, 'DEBUG' => array(
       $this->to
      ,$this->subject
      ,$this->body
      ,$this->headers
    ));
  }

}
