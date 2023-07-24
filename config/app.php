<?php



if(!function_exists('database')){

    function database(string $driver = 'mysql')
    {
        if($driver === 'mysql'){
            return [
                'driver' => 'mysql',

                'mysql' => [
                    'host' => 'localhost',
                    'dbname' => 'zanko',
                    'username' => 'root',
                    'password' => '',
                    'port' => '3306'
                ]
            ];
        }
    }
}