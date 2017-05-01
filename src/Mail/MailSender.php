<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Mail;

/**
 * Classe d'envoi d'emails
 */
class MailSender
{

  const MAIL_PRIORITY_HIGHEST = 1;
  const MAIL_PRIORITY_HIGH = 2;
  const MAIL_PRIORITY_NORMAL = 3;
  const MAIL_PRIORITY_LOW = 4;
  const MAIL_PRIORITY_LOWEST = 5;

  /**
   * @var array  Emails de destination
   */
  private $receivers = array();

  /**
   * @var array  Emails de destination en copie conforme
   */
  private $receiversCC = array();

  /**
   * @var array  Emails de destination en copie conforme invisible
   */
  private $receiversCCI = array();

  /**
   * @var array  Pièces jointes
   */
  private $attachments = array();

  /**
   * @var array  Headers
   */
  private $xheaders = array();

  /**
   * @var array  Referentiel de priorités
   */
  private $priorities = array(
    self::MAIL_PRIORITY_HIGHEST => '1 (Highest)',
    self::MAIL_PRIORITY_HIGH    => '2 (High)',
    self::MAIL_PRIORITY_NORMAL  => '3 (Normal)',
    self::MAIL_PRIORITY_LOW     => '4 (Low)',
    self::MAIL_PRIORITY_LOWEST  => '5 (Lowest)'
  );

  /**
   * @var string  Charset utilisé
   */
  private $charset = "utf-8";

  /**
   * @var string  Bits encoding
   */
  private $ctencoding = "8bit";

  /**
   * @var string  Contenu du mail
   */
  private $mailContent = null;

  /**
   * @var string  Type de contenu
   */
  private $mailContentType = 'text';

  /**
   * @var string  Clé unique
   */
  private $boundary = null;

  /**
   * Constructeur
   */
  public function __construct()
  {
    $this->boundary = "--" . md5(uniqid("myboundary"));
  }

  /**
   * Définit le sujet du mail
   *
   * @param string  $subject  Sujet du mail
   */
  public function setSubject($subject)
  {
    $this->xheaders['Subject'] = strtr($subject, "\r\n", "  ");
  }

  /**
   * Définit l'adresse email de l'expéditeur
   *
   * @param string  $email  Adresse email de l'expéditeur
   */
  public function setSender($email)
  {
    if (!is_string($email)) {
      throw new \Exception('L\'adresse email de l\'expéditeur doit être au format String');
      return;
    }
    $this->xheaders['From'] = $email;
  }

  /**
   * Définit l'adresse email de retour (ReplyTo)
   *
   * @param  string  $email  Adresse email de retour
   */
  public function setReplyTo($email)
  {
    if (!is_string($email)) {
      throw new \Exception('L\'adresse email de retour doit être au format String');
      return;
    }
    $this->xheaders["Reply-To"] = $email;
  }

  /**
   * Ajoute un destinataire
   *
   * @param  string  $email  Adresse email de destination
   */
  public function addReceiver($email)
  {
    if (!is_string($email)) {
      throw new \Exception('L\'adresse email de destinatation doit être au format String');
      return;
    }
    $this->receivers[] = $email;
  }

  /**
   * Définit un tableau de destinataires
   *
   * @param  array  $emails  Adresses email de destination
   */
  public function setReceivers($emails)
  {
    if (!is_array($emails)) {
      throw new \Exception('Le tableau d\'adresses email de destinatation doit être au format Array');
      return;
    }
    $this->receivers = $emails;
  }

  /**
   * Ajoute un destinataire Copie Conforme
   *
   * @param  string  $email  Adresse email de destination
   */
  public function addReceiverCC($email)
  {
    if (!is_string($email)) {
      throw new \Exception('L\'adresse email de destinatation Copie Conforme doit être au format String');
      return;
    }
    $this->receiversCC[] = $email;
  }

  /**
   * Définit un tableau de destinataires Copie Conforme
   *
   * @param  array  $emails  Adresses email de destination
   */
  public function setReceiversCC($emails)
  {
    if (!is_array($emails)) {
      throw new \Exception('Le tableau d\'adresses email de destinatation Copie Conforme doit être au format Array');
      return;
    }
    $this->receiversCC = $emails;
  }

  /**
   * Ajoute un destinataire Copie Conforme Invisible
   *
   * @param  string  $email  Adresse email de destination
   */
  public function addReceiverCCI($email)
  {
    if (!is_string($email)) {
      throw new \Exception('L\'adresse email de destinatation Copie Conforme Invisible doit être au format String');
      return;
    }
    $this->receiversCCI[] = $email;
  }

  /**
   * Définit un tableau de destinataires Copie Conforme Invisible
   *
   * @param  array  $emails  Adresses email de destination
   */
  public function setReceiversCCI($emails)
  {
    if (!is_array($emails)) {
      throw new \Exception('Le tableau d\'adresses email de destinatation Copie Conforme Invisible doit être au format Array');
      return;
    }
    $this->receiversCCI = $emails;
  }

  /**
   * Définit le corps du message
   *
   * @param  string   $content  Contenu du message
   * @param  boolean  $html     (optional) Message au format HTML ? (FALSE par défaut)
   */
  public function setMessage($content, $html = false)
  {
    if ($html) {
      $this->mailContentType = 'html';
    }
    $this->mailContent = $content;
  }

  /**
   * Construit le corps du message à partir d'un template prédéfini
   *
   * @param  string   $template  Chemin d'accès du template à utiliser pour le mail
   * @param  array    $data      Données à substituer dans le template. Format array('clerecherchee' => 'valeur de remplacement')
   * @param  boolean  $html      (optional) Message au format HTML ? (FALSE par défaut)
   */
  public function buildContentFromTemplate($template, $data, $html = false)
  {
    if (!is_file($template)) {
      throw new \Exception('Le template fourni est introuvable : ' . $template);
      return;
    }
    $content = file_get_contents($template);

    foreach ($data AS $k => $v) {
      $content = str_replace($k, $v, $content);
    }

    static::setMessage($content, true);
  }

  /**
   * Définit le niveau de priorité
   *
   * @param  integer  $priority  Niveau de priorité (de 1 à 5 - 3 = normal, 1 = le plus fort)
   */
  public function setPriority($priority)
  {
    if (!array_key_exists($priority, $this->priorities)) {
      $priority = self::MAIL_PRIORITY_NORMAL;
    }
    $this->xheaders["X-Priority"] = $this->priorities[$priority];
  }

  /**
   * Attacher un fichier au mail
   *
   * @param  string  $filePath     Chemin du fichier
   * @param  string  $mimeType     (optional) MimeType (application/x-unknown-content-type par défaut)
   * @param  string  $disposition  (optional) Type d'attachement (inline ou attachment - attachment par défaut. inline : la pièce jointe est intégrée si possible dans le contenu du mail, attachment : la pièce jointe est toujours attachée.)
   */
  public function addAttachment($filePath, $mimeType = 'application/x-unknown-content-type', $disposition = 'attachment')
  {
    $this->attachments[] = array('aattach' => $filePath, 'actype' => $mimeType, 'adispo' => $disposition);
  }

  /**
   * Envoie le mail
   *
   * @throws \Exception
   */
  public function send()
  {
    if (is_null($this->receivers)) {
      throw new \Exception('Vous devez spécifier au minimum une adresse email de destination');
      return;
    }
    if (!array_key_exists('From', $this->xheaders)) {
      throw new \Exception('L\'adresse email de l\'expéditeur n\'a pas été spécifiée');
      return;
    }
    if (is_null($this->mailContent)) {
      throw new \Exception('Vous devez spécifier un contenu au mail');
      return;
    }

    $this->buildMail();
    $strTo = implode(', ', $this->receivers);
    $res = @mail($strTo, $this->xheaders['Subject'], $this->fullBody, $this->headers);
  }

  /**
   * Construit le contenu du mail
   */
  private function buildMail()
  {
    // On construit les headers
    $this->headers = "";

    if (count($this->receiversCC) > 0) {
      $this->xheaders['CC'] = implode(', ', $this->receiversCC);
    }
    if (count($this->receiversCCI) > 0) {
      $this->xheaders['BCC'] = implode(', ', $this->receiversCCI);
    }

    $this->xheaders["Mime-Version"] = "1.0";
    if ($this->mailContentType == 'html') {
      $this->xheaders["Content-Type"] = "text/html; charset=$this->charset";
    } else {
      $this->xheaders["Content-Type"] = "text/plain; charset=$this->charset";
    }
    $this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
    $this->xheaders["X-Mailer"] = "Php/Sylab";

    // Fichiers attachés
    if (count($this->attachments) > 0) {
      $this->buildAttachments();
    } else {
      $this->fullBody = $this->mailContent;
    }

    reset($this->xheaders);
    while (list($hdr, $value ) = each($this->xheaders)) {
      if ($hdr != "Subject") {
        $this->headers .= "$hdr: $value\n";
      }
    }
  }

  /**
   * Construit les fichiers attachés
   *
   * @throws \Exception
   */
  private function buildAttachments()
  {
    $this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"$this->boundary\"";

    $this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
    if ($this->mailContentType == 'html') {
      $this->fullBody .= "Content-Type: text/html; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n" . $this->mailContent . "\n";
    } else {
      $this->fullBody .= "Content-Type: text/plain; charset=$this->charset\nContent-Transfer-Encoding: $this->ctencoding\n\n" . $this->mailContent . "\n";
    }

    $sep = chr(13) . chr(10);
    $ata = array();
    $k = 0;

    // For each attached file, do...
    for ($i = 0; $i < count($this->attachments); $i++) {
      $filename = $this->attachments[$i]['aattach'];
      $basenameparts = explode('/', $filename);
      $basename = end($basenameparts);
      $ctype = $this->attachments[$i]['actype']; // content-type
      $disposition = $this->attachments[$i]['adispo'];

      if (!file_exists($filename)) {
        throw new \Exception('Le fichier ' . $filename . ' ne peut être trouvé');
        return;
      }
      $subhdr = "--$this->boundary\nContent-type: $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding: base64\nContent-Disposition: $disposition;\n  filename=\"$basename\"\n";
      $ata[$k++] = $subhdr;
      // Non encoded line length
      $linesz = filesize($filename) + 1;
      $fp = fopen($filename, 'r');
      $ata[$k++] = chunk_split(base64_encode(fread($fp, $linesz)));
      fclose($fp);
    }

    $this->fullBody .= implode($sep, $ata);
  }

}
