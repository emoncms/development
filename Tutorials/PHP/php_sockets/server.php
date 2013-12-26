<?php

  $server = stream_socket_server("tcp://127.0.0.1:1330", $errno, $errorMessage);
  
  if ($server === false) {
    throw new UnexpectedValueException("Could not bind to socket: $errorMessage");
  }

  while (true) 
  {
    $client = @stream_socket_accept($server);

    while($client)
    {
      $result = fwrite($client,"{power:200}\n");
      if (!$result) $client = false;              
      //sleep(1);
    }
  }
