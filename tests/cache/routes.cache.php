<?php return array (
  0 => 
  array (
  ),
  1 => 
  array (
    'GET' => 
    array (
      0 => 
      array (
        'regex' => '~^(?|/articles/(\\d+)|/articles/(\\d+)/([^/]+))$~',
        'routeMap' => 
        array (
          2 => 
          array (
            0 => 'test',
            1 => 
            array (
              'id' => 'id',
            ),
          ),
          3 => 
          array (
            0 => 'test',
            1 => 
            array (
              'id' => 'id',
              'title' => 'title',
            ),
          ),
        ),
      ),
    ),
  ),
);