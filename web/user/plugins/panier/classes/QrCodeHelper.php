<?php

namespace Grav\Plugin\Panier;

use chillerlan\QRCode\QRCode;
use Grav\Plugin\Panier\Facture;
use chillerlan\QRCode\QROptions;

class QrCodeHelper
{
    const URL =  "/admin/order/";
    const PATH = "/user/data/qr/";
    const BIG_PATH = "/../../../../user/data/qr/";

    private string $domain;

    public function __construct(string $domain)
    {
        $this->domain = $domain;
    }

    public function generateQrCode(Facture $facture):array
    {
        $qrOptions = new QROptions([
            'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel'     => QRCode::ECC_H,
            'imageBase64'  => false,
            'imageTransparent' => false
        ]);

        $qrCode = (new QRCode($qrOptions))->render($this->domain.self::URL.$facture->getId());

        $qrFile = __DIR__.self::BIG_PATH.$facture->getId().".png";

        $im = imagecreatefromstring($qrCode);

        imagepng($im,$qrFile);

        $qrUrl = $this->domain.self::PATH.$facture->getId().".png";

        return [
            "qrUrl" => $qrUrl,
            "qrFile" => $qrFile
        ];
    }
}