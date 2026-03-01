<?php

class Model {

    private static $connection = null;

    public static function getConnection() {

        if (self::$connection === null) {

            self::$connection = new mysqli(
                getenv('DB_HOST'),
                getenv('DB_USER'),
                getenv('DB_PASSWORD'),
                getenv('DB_NAME')
            );

            if (self::$connection->connect_error) {
                throw new Exception(self::$connection->connect_error);
            }

            self::$connection->set_charset('utf8mb4');
        }

        return self::$connection;
    }
}