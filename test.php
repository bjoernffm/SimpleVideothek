<?php
  require __DIR__ . '/vendor/autoload.php';

  $options = array(
    'cluster' => 'eu',
    'useTLS' => true
  );
  $pusher = new Pusher\Pusher(
    'a81a59481c18032691f8',
    'dcde495d1951c6f5e92f',
    '717322',
    $options
  );

  $data['message'] = 'hello world';
  $pusher->trigger('private-my-channel', 'my-event', $data);
?>