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
        $subject = "not_published: ".strval( $data["id"] );
        $message = json_encode($item);

        // if($item->status == 'published'){
        //   $subject = "published";
        //   $message = "publihed";
        // } else if($item->status == 'not_published'){
        //   $subject = "not_published";
        //   $message = "not_published";
        // } if($item->status == 'denied'){
        //   $subject = "denied";
        //   $message = "denied";
        // } 

        //Request to smtp.com api
        $body = smtpRequestBodyBuilder("jlugo.engi@gmail.com", $subject, $message);
        $client = new \GuzzleHttp\Client([
          'base_uri' => 'https://api.smtp.com'
        ]);
        $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
          'json' => $body
        ]);

      }
    ]
  ];


  function smtpRequestBodyBuilder(string $email, string $subject, string $message){

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
      'subject' => $subject,
      'body' =>
      array (
        'parts' =>
          array (
              0 =>
              array (
                'type' => 'text/html',
                'content' => $message,
                ),
            ),
        ),
    );

    return $body;
  }

?>
        
