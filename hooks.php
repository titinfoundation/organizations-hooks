<?php

include './update-email.php';
include './published-email.php';
include './denied-email.php';
use Directus\Application\Application;


return [
  'actions' => [
    'item.update.organizations' => function (array $data) {

      //Access data using item service
      $container = Application::getInstance()->getContainer();
      $itemsService = new \Directus\Services\ItemsService($container);
      $params = ['fields'=>'*.*'];
      $item = $itemsService->find('organizations', $data->id, $params);

      //Validation not to send email to client 
      if(!is_null($item)){
        $item = $item->data;
      }else 
        return;

        $body = publishedEmail($item);

      // if($item->status == 'published'){
      //   $body = publishedEmail($item);

      // } else if($item->status == 'not_published'){
      //   $body = updateEmail($item);

      // } else if($item->status == 'denied'){
      //   $body = deniedEmail($item);

      // } else 
      //   return;

      //Request to smtp.com api
      $client = new \GuzzleHttp\Client([
        'base_uri' => 'https://api.smtp.com'
      ]);
      $response = $client->request('POST', 'v4/messages?api_key=fe1788dd32593bbc21fa941018856731f3b00f30', [
        'json' => $body
      ]);

   }
 ]
];
        
