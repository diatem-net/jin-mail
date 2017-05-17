<?php

/**
 * Jin Framework
 * Diatem
 */

namespace Jin2\Mail;

use SSilence\ImapClient\ImapClient;

/**
 * Gestion d'une webmail
 */
class MailConnector
{

  /**
   * @var object Boîte de réception
   */
  protected $mailbox;

  /**
   * @var string Serveur mail, port, protocole, dossier (exemple : '{imap.gmail.com:993/imap/ssl}INBOX')
   */
  protected $host;

  /**
   * @var string Nom d'utilisateur
   */
  protected $user;

  /**
   * @var string Mot de passe du compte
   */
  protected $pass;

  /**
   * @var string Encryption type
   */
  protected $encryption;

  /**
   * Constructeur
   *
   * @param  string  $host       Serveur mail, port, protocole, dossier
   * @param  string  $user       Nom d'utilisateur
   * @param  string  $pass       Mot de passe du compte
   * @param  string  $encryption Encryption type
   * @throws Exception
   */
  public function __construct($host, $user, $pass, $encryption = 'tls')
  {
    if (!extension_loaded('imap')) {
      throw new \Exception('Extension Imap nécessaire');
    }

    $this->host = $host;
    $this->user = $user;
    $this->pass = $pass;
    $this->encryption = $encryption;
  }

  /**
   * Connexion à la boîte mail
   */
  public function connect()
  {
    $this->mailbox = new ImapClient($this->host, $this->user, $this->pass, $this->encryption);
  }

  /**
   * Retourne un tableau des noms des dossiers
   *
   * @return array
   */
  public function getFolders()
  {
    return $this->mailbox->getFolders();
  }

  /**
   * Selectionne un dossier
   *
   * @param string $folderName Nom du dossier
   */
  public function selectFolder($folderName)
  {
    $this->mailbox->selectFolder($folderName);
  }

  /**
   * Retourne le nombre de messages non lus
   *
   * @return integer  Nombre de messages non lus
   */
  public function countUnreadMessages()
  {
    return $this->mailbox->countUnreadMessages();
  }

  /**
   * Retourne le nombre total de messages
   *
   * @return integer
   */
  public function countTotalMessages()
  {
    return $this->mailbox->countMessages();
  }

  /**
   * Retourne si la connexion est bien initiée
   *
   * @return boolean
   */
  public function isConnected()
  {
    return $this->mailbox->isConnected();
  }

  /**
   * Récupère tous les emails
   *
   * @return  array    Tableau d'emails (pk, vu, sujet, expediteur, date, message, listPJ)
   */
  public function getEmails($saveImageFilesInFolder = null)
  {
    return $this->mailbox->getMessages(true, $saveImageFilesInFolder);
  }

  /**
   * Supprime un mail
   *
   * @param integer $id
   */
  public function deleteMail($id)
  {
    $this->mailbox->deleteMessage($id);
  }

  /**
   * Récupère une pièce-jointe
   *
   * @param integer $id
   * @param integer $num
   */
  public function getAttachment($id, $num = 0)
  {
    return $this->mailbox->getAttachment($id, $num);
  }

}
