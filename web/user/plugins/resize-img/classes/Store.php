<?php

namespace Grav\Plugin\ResizeImg;

class Store
{
    private static function getJsonStore(): string
    {
        return __DIR__.'/../store/images.json';
    }

    public static function addImage(string $image)
    {
        $array = self::readData();
        $array[$image] = $image;
        self::updateData($array);
    }

    public static function removeImage(string $image)
    {
        $array = self::readData();
        unset($array[$image]);
        self::updateData($array);
    }

    public static function readData():array
    {
        $store = file_get_contents(self::getJsonStore());
        return json_decode($store, true);
    }

    private static function updateData(array $data):void 
    {
        file_put_contents(self::getJsonStore(),json_encode($data));
    }
}