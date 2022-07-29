<?php
namespace Grav\Plugin\Panier;

class Panier
{
    const COOKIE_NAME = "panier";

    public static function initCookie(): void
    {
        if(!isset($_COOKIE[self::COOKIE_NAME]))
        {
            self::saveCookie([
                "id" => uniqid(),
                "list" => []
            ]);
        }
    }

    private static function saveCookie($cookieValue): void
    {
        $json = json_encode($cookieValue);
        setcookie(self::COOKIE_NAME,$json,mktime()+(13*30*24*60*60),'/','',true,false);
    }

    public static function getCookie(): ?array
    {
        $cookie = json_decode($_COOKIE[self::COOKIE_NAME],true);

        return $cookie;
    }

    public static function getPanier(?array $cookie, array $store, float $vat): array
    {
        if($cookie ==  null)
        {
            return [];
        }
        
        $result = [];
        $result['nb'] = 0;
        $result['price_ht'] = 0;
        
        foreach($cookie['list'] as $final) {

            $ref = $final['ref'];

            if(isset($store[$ref]) && (!isset($store[$ref]['published']) || $store[$ref]['published']))
            {
                $final['name'] = $store[$ref]['title'];
                $final['price'] = $store[$ref]['price'];
                $final['photo'] = "/".array_key_first($store[$ref]['photo']);
                $result['list'][] = $final;
                $result['nb'] += $final['quantity'];
                $result['price_ht'] += $final['quantity'] * $final['price'];
            }
        }

        $result['price_ht'] = round($result['price_ht'],2);
        $result['vat'] = round($result['price_ht'] * ($vat/100),2);
        $result['price_ttc'] = round(($result['price_ht'] + $result['vat']),2);
        $result['id'] = $cookie['id'];
        
        return $result;
    }

    public static function addProductToPanier(string $ref, array $store)
    {
        if(isset($store[$ref]))
        {
            $cookie = self::getCookie();

            $noExist = true;
            foreach ($cookie['list'] as $key => $row) {
                if($row['ref'] == $ref)
                {
                    $noExist = false;
                    $cookie['list'][$key]['quantity'] += 1;
                    self::saveCookie($cookie);
                    break;
                }   
            }

            if($noExist)
            {
                $cookie['list'][] = [
                    "ref" => $ref,
                    "quantity" => 1
                ];
                self::saveCookie($cookie);
            }
        }
    }

    public static function decreaseProductPanier(string $ref, array $store): void
    {
        if(isset($store[$ref]))
        {
            $cookie = self::getCookie();

            foreach ($cookie['list'] as $key => $row) {
                if($row['ref'] == $ref)
                {
                    $cookie['list'][$key]['quantity'] -= 1;
                    self::saveCookie($cookie);
                    break;
                }   
            }

            if($cookie['list'][$key]['quantity'] < 1)
            {
                unset($cookie['list'][$key]);
                self::saveCookie($cookie);
            }
        }
    }

    public static function setQuantityProductPanier(string $ref, int $quantity,  array $store)
    {
        if(isset($store[$ref]))
        {
            $cookie = self::getCookie();

            foreach ($cookie['list'] as $key => $row) {
                if($row['ref'] == $ref)
                {
                    $cookie['list'][$key]['quantity'] = $quantity;
                    self::saveCookie($cookie);
                    break;
                }   
            }

            if($cookie['list'][$key]['quantity'] < 1)
            {
                unset($cookie['list'][$key]);
                self::saveCookie($cookie);
            }
        }
    }

    public static function clearProductPanier(string $ref)
    {
        $cookie = self::getCookie();

        foreach($cookie['list'] as $key => $row)
        {
            if($row['ref'] == $ref)
            {
                unset($cookie['list'][$key]);
                self::saveCookie($cookie);
                break;
            }
        }

    }

    public static function clearPanier()
    {
        self::saveCookie([
            "id" => uniqid(),
            "list" => []
        ]);
    }
}