<?php

use Directus\Application\Application;

  return [
    'actions' => [
      'item.create.test' => function (array $data) {

        $email = createdEmail($data);
        //Request to smtp.com api
        $body = smtpRequestBodyBuilder($email);
        $client = new \GuzzleHttp\Client([
          'base_uri' => 'https://api.smtp.com'
        ]);
        $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
          'json' => $body
        ]);
      },
      'item.update.test' => function (array $data) {

        //Access data using item service
        $container = Application::getInstance()->getContainer();
        $itemsService = new \Directus\Services\ItemsService($container);
        $params = ['fields'=>'*.*'];
        $item = $itemsService->find('organizations', $data["id"], $params);
        $item = $item["data"];

        //Email construction
        $email;
       
        if($item["status"] == 'published'){
          //$Email = publishedEmail($item);
          $email = publishedUpdatedEmail($item);
        } else if($item["status"] == 'not_published'){
          $email = updatedEmail($item);
        } else if($item["status"] == 'denied'){
          $email = deniedEmail($item);
        } else {
          return;
        }
        
        //Request to smtp.com api
        $body = smtpRequestBodyBuilder($email);
        $client = new \GuzzleHttp\Client([
          'base_uri' => 'https://api.smtp.com'
        ]);
        $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
          'json' => $body
        ]);
      }
    ]
  ];

  class Email {
    public $recipient;
    public $subject;
    public $message;
  }

  function updatedEmail () {
    $e = new Email();
    $e->recipient = $item['email'];
    $e->subject = "¡Recibimos tu actualización!"; 
    $e->message = '<html><body>';
    $e->message .= "<p>¡Saludos  {$item['name']}!</p>";
    $e->message .= "<p>En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
    $e->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
    $e->message .= "</body></html>";

    return $e;
  }

  function createdEmail (array $item) {
    $e = new Email();
    $e->recipient = $item['email'];
    $e->subject = "¡Recibimos tu solicitud!"; 
    $e->message = '<html><body>';
    $e->message .= "<p>¡Saludos  {$item['name']}!</p>";
    $e->message .= "<p>¡Tu perfil ha sido completado! En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
    $e->message .= "<p>¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
    $e->message .= "</body></html>";

    return $e;
  }

  function publishedEmail (array $item) {
    $e = new Email();
    $e->recipient = $item['email']; 
    $e->subject = "¡Bienvenidos a SINFINESPR.ORG!"; 
    $e->message = '<html><body>';
    $e->message .= "<p>¡Saludos  {$item['name']}!</p>";
    $e->message .= "<p>Deseamos informarte que la organización {$item['name']} ya es parte de la base de datos de SINFINESPR. Puede revisar su perfil en el siguiente enlace: ";
    $e->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
    $e->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
    $e->message .= "</body></html>";

    return $e;
  }

  function publishedUpdatedEmail (array $item) {
    $e = new Email();
    $e->recipient = $item['email']; 
    $e->subject = "¡Tu actualización ha sido completada! "; 
    $e->message = '<html><body>';
    $e->message .= "<p>¡Saludos  {$item['name']}!</p>";
    $e->message .= "<p>Deseamos informarte que la información de tu organización ha sido actualizada en la base de datos de SINFINESPR. Puede revisar su perfil en el siguiente enlace: ";
    $e->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
    $e->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
    $e->message .= "</body></html>";

    return $e;
  }


  function deniedEmail (array $item) {
    $e = new Email();
    $e->recipient = $item['email']; 
    $e->subject = "Su organización está bajo revisión pendiente de algunos documentos requeridos."; 
    $e->message = '<html><body>';
    $e->message .= "<p>¡Saludos  {$item['name']}!</p>";
    $e->message .= "<p>Muchas gracias por tu interés en SINFINESPR. Para poder completar el proceso de registro necesitamos que revises la documentación requerida. ";
    $e->message .= "El sistema nos indica que falta (n) un (os) documento (s). El motivo por este breve detente se debe a que: </p>";
    $e->message .= "<p>{$item['reason']['description']}</p>";
    $e->message .= "<p>En caso que hayas cumplido con todos estos requisitos y por alguna razón no se ve reflejado en nuestro panel de administración no dudes en comunicarte con nosotros ";
    $e->message .= "para poder corregir la falta enseguida recibamos la evidencia. Puedes escribirnos a info@sinfinespr.org. </p>";
    $e->message .= "<p>Quedamos atentos.</p>";
    $e->message .= "<p>¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
    $e->message .= "</body></html>";

    return $e;
  }


  function smtpRequestBodyBuilder(string $email, Email $e){

    $body = array (
      'channel' => 'info_sinfinespr_org',
      'recipients' =>
        array (
          'to' =>
            array (
                  0 =>
                  array (
                    'address' => $e->email,
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
      'subject' => $e->subject,
      'body' =>
      array (
        'parts' =>
          array (
              0 =>
              array (
                'type' => 'text/html',
                'content' => $e->message,
                ),
            ),
        ),
    );

    return $body;
  }

?>
        
