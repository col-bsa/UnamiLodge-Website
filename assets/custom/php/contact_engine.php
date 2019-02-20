<?php

require '../../vendor/autoload.php';
use Mailgun\Mailgun;
use Medoo\Medoo;

require 'secrets.php';

$mailgun = array();

/* * * * * * * * * * * * * * * * * * *
 *          ADMIN VARIABLES          *
 * * * * * * * * * * * * * * * * * * */

$mailgun['domain'] = "unamilodge.org";
$mailgun['from'] = "UnamiLodge.org Contact Form <contact_form@" . $mailgun['domain'] . ">";
$mailgun['subject'] = "UnamiLodge.org Message About: "; // Is added to later in script

/* * * * * * * * * * * * * * * * * * *
 *    COLLECT HTML FORM POST DATA    *
 * * * * * * * * * * * * * * * * * * */

$user_data = array();
$return_data = array();

$user_data['name'] = trim($_POST['name']);
$user_data['email'] = trim($_POST['email']);
$user_data['recipient'] = trim($_POST['recipient']);
$user_data['subject'] = trim($_POST['subject']);
$user_data['message'] = trim($_POST['message']);
$user_data['recaptcha'] = $_POST['g-recaptcha-response'];

$user_data['address'] = $_SERVER['REMOTE_ADDR'];

/* * * * * * * * * * * * * * * * * * *
 *           VALIDATE DATA           *
 * * * * * * * * * * * * * * * * * * */

if (empty($user_data['recaptcha']))
  $error_text = "reCAPTCHA was not received.";

if(!isset($error_text))
{
  $recaptcha_status   = isreCAPTCHAValid($SECRET_recaptcha, $user_data['recaptcha'], $user_data['address']);
  if ($recaptcha_status != true)
    $error_text = "reCAPTCHA was not verified.";
}

$inputs = ['name', 'email', 'recipient', 'subject', 'message'];

foreach ($inputs as $input)
{
  if(!isset($error_text))
  {
    if (empty($user_data[$input]))
      $error_text = ucfirst($input) . " was not received.";
  }
}

if(!isset($error_text))
{
  /* * * * * * * * * * * * * * * * * * *
   *          EMAIL FORM DATA          *
   * * * * * * * * * * * * * * * * * * */

  $mailgun['subject'] .= $user_data['subject'];

  $send_text = "The following was submitted to UnamiLodge.org/contact." .
    PHP_EOL . PHP_EOL . $user_data['message'] . PHP_EOL . PHP_EOL . $user_data['name'] . PHP_EOL . $user_data['email'];

  $mg = new Mailgun($SECRET_mailgun);

  $mg->sendMessage($mailgun['domain'], array(
    'from'        => $mailgun['from'],
    'to'          => "Unami Lodge <" . $user_data['recipient'] . "@" . $mailgun['domain'] . ">, Communications Committee <communications@" . $mailgun['domain'] . ">",
    'h:Reply-To'  => $user_data['name'] . " <" . $user_data['email'] . ">, Communications Committee <communications@" . $mailgun['domain'] . ">",
    'subject'     => $mailgun['subject'],
    'text'        => $send_text
  ));

  /* * * * * * * * * * * * * * * * * * *
   *          DATABASE INSERT          *
   * * * * * * * * * * * * * * * * * * */

  $database = new Medoo([
    'database_type' => 'sqlite',
    'database_file' => '../../../../database.sqlite'
  ]);

  $database->insert('arc_contactform', [
    'name' => $user_data['name'],
    'email' => $user_data['email'],
    'recipient' => $user_data['recipient'],
    'subject' => $user_data['subject'],
    'message' => $user_data['message'],
    'orig_ip' => $user_data['address'],
  ]);
}

/* * * * * * * * * * * * * * * * * * *
 *           RETURN STATUS           *
 * * * * * * * * * * * * * * * * * * */

if (empty($error_text))
  $return_data['success'] = true;
else
{
  $return_data['success'] = false;
  $return_data['error'] = $error_text;
}

echo json_encode($return_data);

?>
