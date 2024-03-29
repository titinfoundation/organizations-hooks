<?php

use Directus\Application\Application;

  return [
    'filters' => [
      'item.update.organizations:before' => function (\Directus\Hook\Payload $payload) {

        $other_assets = $payload->get('other_assets');
        $property_and_equipment = $payload->get('property_and_equipment');
        $current_assets = $payload->get('current_assets');

       if(is_null($other_assets)||is_null($property_and_equipment)||is_null($current_assets)){
          //Access data using item service
          $container = Application::getInstance()->getContainer();
          $itemsService = new \Directus\Services\ItemsService($container);
          $params = ['fields'=>'*.*'];
          $item = $itemsService->find('organizations', $payload["id"], $params);
          $item = $item["data"];

          if(is_null($other_assets)){
            $other_assets = $item["other_assets"];
          }

          if(is_null($property_and_equipment)){
            $property_and_equipment = $item["property_and_equipment"];
          }

          if(is_null($current_assets)){
            $current_assets = $item["current_assets"];
          }
        }

        $active_total = $other_assets + $property_and_equipment + $current_assets;
        $payload->set('active_total', $active_total);
        
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
        $params = ['fields'=>'*.*,reason.*,reason.translations.language,reason.translations.description'];
        $item = $itemsService->find('organizations', $data["id"], $params);
        $item = $item["data"];

        //Email construction
        $emailContent;
       
        if($item["status"] == 'published'){
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

  //status not published
  function updatedEmail (array $item) {
    $ec = new EmailContent();

    if($item["locale"] !=='en'){
      $ec->subject = "{$item['name']} ¡Recibimos tu actualización!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Saludos: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>Dentro de los próximos 3 a 5 días laborables, el equipo evaluará la información editada en su perfil. Recibirá una notificación en cuanto sea validado y publicado.</p>";
      $ec->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    } else {
      $ec->subject = "{$item['name']} We have received your request!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Greetings: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>During the next 3 to 5 workdays our team will evaluate the edited information on your profile. You will receive a notification when your profile is published and validated.</p>";
      $ec->message .= "<p>Thank you for being part of SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    }

    return $ec;
  }

  //on create
  function createdEmail (array $item) {
    $ec = new EmailContent();

    if($item["locale"] !=='en'){
      $ec->subject = "¡Recibimos tu solicitud!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Querido: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>¡Tu perfil ha sido completado! En los próximos 10 días nuestro equipo de trabajo validará la información. Recibirás una comunicación al correo electrónico de contacto cuando sea aprobada.</p>";
      $ec->message .= "<p>¡Muchas gracias por su confianza e interés en SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    } else {
      $ec->subject = "We have received your request!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Greetings: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>Your profile is completed! In the next ten days, our work team will validate the information. You will receive a communication to the contact email when it is approved.</p>";
      $ec->message .= "<p>Thank you very much for being part of SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    }

    return $ec;
  }

  // status published
  function publishedUpdatedEmail (array $item) {
    $ec = new EmailContent();

    if($item["locale"] !=='en'){
      $ec->subject = "{$item['name']} ¡Tu perfil se validó y publicó!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Saludos: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>Le informamos que el perfil de la organización <b>{$item['name']}</b>, fue validado y publicado exitosamente en SINFINESPR.org. Visita tu perfil: ";
      $ec->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
      $ec->message .= "<p><b><u>Recuerda que un perfil validado no significa que está vigente</u>. Corrobora</b> que los documentos presentados están: </p>";
      $ec->message .= "<ul><li><b>Dentro del término de vigencia de la agencia correspondiente.</b></li><li><b>En acuerdo con los requisitos del tipo de fondo a solicitar</b> (estatal, federal y/o filantrópico)<b>.</b></li></ul>";
      $ec->message .= "<p>¡Muchas gracias por ser parte de SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    } else {
      $ec->subject = "{$item['name']} Your profile has been validated and published!"; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Greetings: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>We would like to inform you that the profile of the <b>{$item['name']}</b>, was successfully validated and published in SINFINESPR.org. Visit your profile: ";
      $ec->message .= "<a href='https://sinfinespr.org/organizaciones/{$item['slug']}'>https://sinfinespr.org/organizaciones/{$item['slug']}</a></p>";
      $ec->message .= "<p><b><u>Remember that a validated profile does not mean that it is current</u>. Verify</b> that the documents presented are:  </p>";
      $ec->message .= "<ul><li><b>Within the term of validity of the corresponding agency.</b></li><li><b>In accordance with the requirements of the type of fund to be requested</b> (state, federal and/or philanthropic)<b>.</b></li></ul>";
      $ec->message .= "<p>Thank you for being part of SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    }

    return $ec;
  }


  function deniedEmail (array $item) {
    $ec = new EmailContent();

    if($item["locale"] !=='en'){
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
    } else {
      $ec->subject = "Your organization is under review pending some required documents."; 
      $ec->message = '<html><body>';
      $ec->message .= "<p>Greetings: <b>{$item['name']}</b></p>";
      $ec->message .= "<p>Thank you very much for your interest in SINFINESPR. To complete the registration process, we need you to review the required documentation. ";
      $ec->message .= "The system tells us that a document (s) is missing. </p>";
      $ec->message .= "<p><b>{$item['reason']['translations'][0]['description']}</b></p>";
      $ec->message .= "<p>If you have met all the requirements, and for some reason, we don't see it in our administration panel, ";
      $ec->message .= "do not hesitate to contact us to correct the fault as soon as we receive the evidence. You can write to us at info@sinfinespr.org. </p>";
      $ec->message .= "<p>We stay in touch!</p>";
      $ec->message .= "<p>Thank you very much for your trust and interest in SINFINESPR!</p>";
      $ec->message .= "<p>Website: <a href='https://sinfinespr.org'>https://sinfinespr.org</a><br/>Email: info@sinfinespr.org</p>";
      $ec->message .= "<div><img alt='SinFinesPR Logo' src='https://api.sinfinespr.org/sin-fines-pr/assets/klpil65vblcs8oco' width='225' height='130' ></div>";
      $ec->message .= "</body></html>";
    }

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
        
