<?php

namespace Grav\Plugin\Panier;

use Grav\Common\Taxonomy;
use Grav\Common\Page\Collection;
use Symfony\Component\Yaml\Yaml;

class Store
{
    private static function getJsonStore(): string
    {
        return __DIR__.'/../store/store.json';
    }

    public static function updateData(Collection $collection): void
    {
        $productsData = [];
            
        $i = 0;
        foreach ($collection as $value) {
            
            $data = Yaml::parse($value->frontmatter());

            unset($data["page_product"]);
            unset($data["taxonomy"]);
            $productsData[$data['ref']] = $data;

            $i++;
            
        }

        file_put_contents(Store::getJsonStore(),json_encode($productsData));
    } 

    public static function readData(): array
    {
        $store = file_get_contents(Store::getJsonStore());
        return json_decode($store, true);
    }

}