<?php

namespace Cherrylu\iotroot;

final class encrypter {
    private static $key;
    private static $clientId;
    private static $timeStamp;
    private static $seed;
    private static $signString;
    const HEX = '0123456789ABCDEF';
    const CIPHER = 'AES-128-ECB';
    const OPTION = OPENSSL_RAW_DATA;

    /**
     * encrypt sign string according to clientId and timeStamp
     *
     * @param string $clientId
     * @param string | int $timeStamp
     * @param string $key
     *
     * @return string
     */
    public static function encrypt($clientId, $timeStamp, $key = '') {
        self::$clientId   = $clientId;
        self::$timeStamp  = $timeStamp;
        self::$key        = $key;
        self::$seed       = $clientId . $timeStamp;
        self::$signString = self::toHex(self::encryptBytes());

        return self::$signString;
    }

    public static function getSignString() {
        return self::$signString;
    }

    /**
     * @return mixed
     */
    public static function getClientId() {
        return self::$clientId;
    }

    /**
     * @return mixed
     */
    public static function getTimeStamp() {
        return self::$timeStamp;
    }

    /**
     * @return mixed
     */
    public static function getKey() {
        return self::$key;
    }

    /**
     * @return mixed
     */
    public static function getSeed() {
        return self::$seed;
    }

    private static function getRawKey() {
        return substr(openssl_digest(openssl_digest(self::$key, 'sha1', true), 'sha1', true), 0, 16);
    }

    private static function encryptBytes() {
        return openssl_encrypt(self::$seed, self::CIPHER, self::getRawKey(), self::OPTION);
    }

    public static function toHex($buf) {
        if ( $buf === null ) {
            return "";
        }
        $result = "";
        for ( $i = 0; $i < strlen($buf); $i ++ ) {
            $result .= self::appendHex(ord($buf[ $i ]));
        }

        return $result;
    }

    /**
     * @param $b
     *
     * @return string
     */
    private static function appendHex($b) {
        return self::HEX[ ( $b >> 4 ) & 0x0F ] . self::HEX[ $b & 0x0F ];
    }

}