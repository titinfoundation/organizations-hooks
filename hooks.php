<?php
  include "./update-email.php"
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
        
        //$body  = updateEmail();
        
        $body = array (
          'channel' => 'info_sinfinespr_org',
          'recipients' =>
            array (
              'to' =>
                array (
                      0 =>
                      array (
                        'name' => 'Jorge Lugo',
                        'address' => 'jlugo.engi@gmail.com',
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
          'subject' => 'Update Su organización está bajo revisión pendiente de algunos documentos requeridos',
          'body' =>
          array (
            'parts' =>
              array (
                  0 =>
                  array (
                    'type' => 'text/html',
                    'content' => "Saludos sub sup {$item->name} esto es una prueba. ",
                    ),
                ),
            ),
        );

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
?>
        
