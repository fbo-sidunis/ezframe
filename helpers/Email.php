<?php
namespace Helper;

use Core\Common\Site;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use Symfony\Component\Mailer\Mailer;

/**
 ⚠️ Nécessite la librairie PHPMailer
 * Génère et envoie des Emails à l'aide de la librairie PHPMailer
 * @package Helper
 */
class Email extends \Core\Db {

  const DEV_MODE = true;
  const DEV_MAIL = "mnadeau@groupefbo.com";

  protected $to = self::DEV_MODE ? self::DEV_MAIL : "mnadeau@groupefbo.com";
  protected $body = "";
  protected $altBody = "";
  protected $isHTML = true;
  protected $from = "contact@sidunis.fr";
  protected $fromName = "Sidunis";
  protected $cc = [];
  protected $bcc = [];
  protected $smtp = false;
  protected $subject = "Mail sans sujet";

  function __construct($parameters = []){
    foreach (($parameters ?: []) as $parameter=>$value){
      $setter = "set".ucfirst($parameter);
      if (method_exists($this,$setter)) $this->$setter($value);
    }
  }

  public function send()
  {
    $mail = new PHPMailer();
    $mail->setLanguage('fr');
    $mail->CharSet = 'UTF-8';
    try {
      //Server settings
      if ($this->getSmtp()){
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
        $mail->isSMTP();                                            //Send using SMTP
        $mail->Host       = 'smtp.example.com';                     //Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
        $mail->Username   = 'user@example.com';                     //SMTP username
        $mail->Password   = 'secret';                               //SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            //Enable implicit TLS encryption
        $mail->Port       = 465;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
      }
      //Recipients
      $mail->setFrom($this->getFrom(),$this->getFromName());
      $mail->addAddress($this->getTo());     //Add a recipient
      foreach ($this->getCc() as $cc){
        $mail->addCC($cc);
      }
      foreach ($this->getBcc() as $bcc){
        $mail->addBCC($bcc);
      }
  
      //Content
      $mail->isHTML($this->getIsHTML());                                  //Set email format to HTML
      $mail->Subject = $this->getSubject();
      $mail->Body    = $this->getBody();
      $mail->AltBody = $this->getAltBody();
      $mail->send();
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
    return true;
  }

  /**
   * Get the value of subject
   */ 
  public function getSubject()
  {
    return $this->subject;
  }

  /**
   * Set the value of subject
   *
   * @return  self
   */ 
  public function setSubject($subject)
  {
    $this->subject = $subject;

    return $this;
  }

  /**
   * Get the value of smtp
   */ 
  public function getSmtp()
  {
    return $this->smtp;
  }

  /**
   * Set the value of smtp
   *
   * @return  self
   */ 
  public function setSmtp($smtp)
  {
    $this->smtp = $smtp;

    return $this;
  }

  /**
   * Get the value of bcc
   */ 
  public function getBcc()
  {
    return $this->bcc;
  }

  /**
   * Set the value of bcc
   *
   * @return  self
   */ 
  public function setBcc($bcc)
  {
    $this->bcc = $bcc;

    return $this;
  }

  /**
   * Get the value of cc
   */ 
  public function getCc()
  {
    return $this->cc;
  }

  /**
   * Set the value of cc
   *
   * @return  self
   */ 
  public function setCc($cc)
  {
    $this->cc = $cc;

    return $this;
  }

  /**
   * Get the value of fromName
   */ 
  public function getFromName()
  {
    return $this->fromName;
  }

  /**
   * Set the value of fromName
   *
   * @return  self
   */ 
  public function setFromName($fromName)
  {
    $this->fromName = $fromName;

    return $this;
  }

  /**
   * Get the value of from
   */ 
  public function getFrom()
  {
    return $this->from;
  }

  /**
   * Set the value of from
   *
   * @return  self
   */ 
  public function setFrom($from)
  {
    $this->from = $from;

    return $this;
  }

  /**
   * Get the value of isHTML
   */ 
  public function getIsHTML()
  {
    return $this->isHTML;
  }

  /**
   * Set the value of isHTML
   *
   * @return  self
   */ 
  public function setIsHTML($isHTML)
  {
    $this->isHTML = $isHTML;

    return $this;
  }

  /**
   * Get the value of altBody
   */ 
  public function getAltBody()
  {
    return $this->altBody;
  }

  /**
   * Set the value of altBody
   *
   * @return  self
   */ 
  public function setAltBody($altBody)
  {
    $this->altBody = $altBody;

    return $this;
  }

  /**
   * Get the value of body
   */ 
  public function getBody()
  {
    return $this->body;
  }

  /**
   * Set the value of body
   *
   * @return  self
   */ 
  public function setBody($body)
  {
    $this->body = $body;

    return $this;
  }

  /**
   * Get the value of to
   */ 
  public function getTo()
  {
    return $this->to;
  }

  /**
   * Set the value of to
   *
   * @return  self
   */ 
  public function setTo($to)
  {
    $this->to = $to;

    return $this;
  }


  public static function sendNewDemande($idDemande){
    $mail = new self([
      "subject" => "Nouvelle demande créée",
      "body" => "
        Une nouvelle demande vient d'être créée<br>
        <a href=\"".Site::getRouting()->get("admin_requests_detail_edit",["idRequest"=>$idDemande],true)."\">Lien</a>
      ",
      "altBody" => "Une nouvelle demande vient d'être créée",
    ]);
    return $mail->send();
  }

  public static function sendDemandeEdit($idDemande){
    $mail = new self([
      "subject" => "Demande modifiée",
      "body" => "
        Une demande vient d'être modifiée<br>
        <a href=\"".Site::getRouting()->get("admin_requests_detail_edit",["idRequest"=>$idDemande],true)."\">Lien</a>
      ",
      "altBody" => "Une demande vient d'être modifiée",
    ]);
    return $mail->send();
  }
}
