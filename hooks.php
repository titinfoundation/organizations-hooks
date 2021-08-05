<?php

use Directus\Application\Application;

  return [
    'filters' => [
      'item.update.organizations:before' => function (\Directus\Hook\Payload $payload) {

        $other_assets = 0;
        $income_total = 0
        $active_total = 0;
        $item = [];
        
        if(!$payload->has('other_assets') || !$payload->has('income_total')){
           //Access data using item service
          $container = Application::getInstance()->getContainer();
          $itemsService = new \Directus\Services\ItemsService($container);
          $params = ['fields'=>'*.*'];
          $item = $itemsService->find('organizations', $payload["id"], $params);
          $item = $item["data"];
        }

        if($payload->has('other_assets')){
            $other_assets = $payload->get('other_assets');
        }else {
          $other_assets = $item["other_assets"];
        }

        if($payload->has('income_total')){
          $income_total = $payload->get('income_total');
        }else {
          $income_total = $item["income_total"];
        }

        $active_total = $other_assets + $income_total;

        return $payload;
      }
    ],
    'actions' => [
      'item.create.organizations' => function (array $data) {

        $emailContent = createdEmail($data);
        //Request to smtp.com api

        if(!empty($data["email"])){
          $body = smtpRequestBodyBuilder($data["email"], $emailContent);
          $client = new \GuzzleHttp\Client([
            'base_uri' => 'https://api.smtp.com'
          ]);
          $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
            'json' => $body
          ]);
        }
      },
      'item.update.organizations' => function (array $data) {

        //validate status changed before continue
        if(is_null($data["status"])){
          return;
        }

        //Access data using item service
        $container = Application::getInstance()->getContainer();
        $itemsService = new \Directus\Services\ItemsService($container);
        $params = ['fields'=>'*.*'];
        $item = $itemsService->find('organizations', $data["id"], $params);
        $item = $item["data"];

        //Email construction
        $emailContent;
       
        if($item["status"] == 'published'){
          //$emailContent = publishedEmail($item);
          $emailContent = publishedUpdatedEmail($item);
        } else if($item["status"] == 'not_published'){
          $emailContent = updatedEmail($item);
        } else if($item["status"] == 'denied'){
          $emailContent = deniedEmail($item);
        } 

        if(!is_null($emailContent) && !empty($item["email"])){
          //Request to smtp.com api
          $body = smtpRequestBodyBuilder($item["email"], $emailContent);
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
    $ec->subject = "¡Recibimos tu actualización!"; 
    $ec->message = '<html><body>';
    $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
    $ec->message .= "<p>En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
    $ec->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
    $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
    $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
    $ec->message .= "</body></html>";

    return $ec;
  }

  function createdEmail (array $item) {
    $ec = new EmailContent();
    $ec->subject = "¡Recibimos tu solicitud!"; 
    $ec->message = '<html><body>';
    $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
    $ec->message .= "<p>¡Tu perfil ha sido completado! En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
    $ec->message .= "<p>¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
    $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
    $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
    $ec->message .= "</body></html>";

    return $ec;
  }

  function publishedEmail (array $item) {
    $ec = new EmailContent();
    $ec->subject = "¡Bienvenidos a SINFINESPR.ORG!"; 
    $ec->message = '<html><body>';
    $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
    $ec->message .= "<p>Deseamos informarte que la organización <b>{$item['name']}</b> ya es parte de la base de datos de SINFINESPR. Puede revisar su perfil en el siguiente enlace: ";
    $ec->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
    $ec->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
    $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
    $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
    $ec->message .= "</body></html>";

    return $ec;
  }

  function publishedUpdatedEmail (array $item) {
    $ec = new EmailContent();
    $ec->subject = "¡Tu actualización ha sido completada! "; 
    $ec->message = '<html><body>';
    $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
    $ec->message .= "<p>Deseamos informarte que la información sobre la organización <b>{$item['name']}</b> ha sido actualizada en la base de datos de SINFINESPR. Puede revisar su perfil en el siguiente enlace: ";
    $ec->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
    $ec->message .= "<p>Saludos cordiales,</p>";
    $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
    $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
    $ec->message .= "</body></html>";

    return $ec;
  }


  function deniedEmail (array $item) {
    $ec = new EmailContent();
    $ec->subject = "Su organización está bajo revisión pendiente de algunos documentos requeridos."; 
    $ec->message = '<html><body>';
    $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
    $ec->message .= "<p>Muchas gracias por tu interés en SINFINESPR. Para poder completar el proceso de registro necesitamos que revises la documentación requerida. ";
    $ec->message .= "El sistema nos indica que falta (n) un (os) documento (s). El motivo por este breve detente se debe a que: </p>";
    $ec->message .= "<p><b>{$item['reason']['description']}</b></p>";
    $ec->message .= "<p>En caso que hayas cumplido con todos estos requisitos y por alguna razón no se ve reflejado en nuestro panel de administración no dudes en comunicarte con nosotros ";
    $ec->message .= "para poder corregir la falta enseguida recibamos la evidencia. Puedes escribirnos a info@sinfinespr.org. </p>";
    $ec->message .= "<p>Quedamos atentos.</p>";
    $ec->message .= "<p>¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
    $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
    $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
    $ec->message .= "</body></html>";

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
        
