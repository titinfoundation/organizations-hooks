<?php

  function publishedEmail(array $data) {

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
        'subject' => 'published Su organización está bajo revisión pendiente de algunos documentos requeridos',
        'body' =>
        array (
          'parts' =>
            array (
                0 =>
                array (
                  'type' => 'text/html',
                  'content' => "Saludos sub sup {$data['name']} esto es una prueba. ",
                  ),
              ),
          ),
      );

    return $body;
  }
?>

        