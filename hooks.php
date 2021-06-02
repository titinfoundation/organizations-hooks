<?php

use Directus\Application\Application;

  return [
    'actions' => [
      'item.update.organizations' => function (array $data) {

        //Access data using item service
        $container = Application::getInstance()->getContainer();
        $itemsService = new \Directus\Services\ItemsService($container);
        $params = ['fields'=>'*.*'];
        $item = $itemsService->find('organizations', $data["id"], $params);
        $item = $item["data"];

        //Email construction
        $subject = "";
        $message = "";

        if($item["status"] == 'published'){
          $subject = "test";
        $message = "test";
          $emailContent = createdEmail($item["name"]);
        } else if($item["status"] == 'not_published'){
          $subject = "not_published";
          $message = "not_published";
        } if($item["status"] == 'denied'){
          $subject = "denied";
          $message = "denied";
        } 

        if(!empty($message)){
          //Request to smtp.com api
          $body = smtpRequestBodyBuilder("jlugo.engi@gmail.com", $emailContent);
          $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.smtp.com'
          ]);
          $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
            'json' => $body
          ]);
        }
        
      }
    ]
  ];

  class EmailContent {
    public $subject;
    public $message;
  }

  function updatedEmail () {
    $ec = new EmailContent();
    $ec->subject = ""; 
    $ec->message = "";

    return $ec;
  }

  function createdEmail (string $name) {
    $ec = new EmailContent();
    $ec->subject = "¡Recibimos tu solicitud!"; 
    $ec->message = '<html><body>';
    $ec->message .= "<p >¡Saludos  ${name}!</p>";
    $ec->message .= "<p >¡Tu perfil ha sido completado! En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
    $ec->message .= "<p >¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
    $ec->message .= "</body></html>";

    return $ec;
  }

  function publishedUpdatedEmail () {
    $ec = new EmailContent();
    $ec->subject = "¡Recibimos tu solicitud!"; 

    $ec->message = '<html><body>';

    $ec->message .= "<p >¡Saludos  ${name}!</p>";
    // $ec->message .= "<br >";
    $ec->message .= "<p ></p>";
    // $ec->message .= "<br >";
    $ec->message .= "<p ></p>";
   
    $ec->message .= "</body></html>";

    return $ec;
  }

  function publishedEmail () {
    $ec = new EmailContent();
    $ec->subject = ""; 
    $ec->message = "";

    return $ec;
  }

  function deniedEmail () {
    $ec = new EmailContent();
    $ec->subject = ""; 
    $ec->message = "";

    return $ec;
  }


  function smtpRequestBodyBuilder(string $email, EmailContent $ec){

    $body = array (
      'channel' => 'info_sinfinespr_org',
      'recipients' =>
        array (
          'to' =>
            array (
                  0 =>
                  array (
                    'address' => $email,
                  ),
            ),
        ),
      'originator' =>
        array (
          'from' =>
            array (
              'name' =>'SinFines PR',
              'address' => 'info@sinfinespr.org',
              ),
          ),
      'subject' => $ec->subject,
      'body' =>
      array (
        'parts' =>
          array (
              0 =>
              array (
                'type' => 'text/html',
                'content' => $ec->message,
                ),
            ),
        ),
    );

    return $body;
  }

?>
        
