<?php

use Directus\Application\Application;

  return [
    'actions' => [
      'item.update.organizations' => function (array $data) {

        //Access data using item service
        $container = Application::getInstance()->getContainer();
        $itemsService = new \Directus\Services\ItemsService($container);
        $params = ['fields'=>'*.*'];
        $item = $itemsService->find('organizations', $data->id, $params);
        $item = $item->data;


        //Email construction
        $subject = "published";
        $message = "publihed";

        // if($item->status == 'published'){
        //   $subject = "published";
        //   $message = "publihed";
        // } else if($item->status == 'not_published'){
        //   $subject = "not_published";
        //   $message = "not_published";
        // } if($item->status == 'denied'){
        //   $subject = "denied";
        //   $message = "denied";
        // } else
        //   return;

        //Request to smtp.com api
        $client = new \GuzzleHttp\Client([
          'base_uri' => 'https://api.smtp.com'
        ]);
        $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
          'json' => smtpRequestBodyBuilder();
        ]);

      }
    ]
  ];


  function smtpRequestBodyBuilder(){

    $body = array (
      'channel' => 'info_sinfinespr_org',
      'recipients' =>
        array (
          'to' =>
            array (
                  0 =>
                  array (
                    'name' => "Jorge",
                    'address' => "jlugo.engi@gmail.com",
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
      'subject' => "asdfasdf",
      'body' =>
      array (
        'parts' =>
          array (
              0 =>
              array (
                'type' => 'text/html',
                'content' => "asdfasdf",
                ),
            ),
        ),
    );

    return $body;
  }

?>
        
