<?php

namespace Grav\Plugin\Panier;

use Grav\Plugin\Panier\Facture;
use TheCodingMachine\Gotenberg\Client;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\RequestException;

class PdfHelper
{
    private $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    public function generatePdf(Facture $facture,array $infos,string $title):string
    {
        $client = new Client('http://gotenberg:3000', new \Http\Adapter\Guzzle6\Client());

        $html = $this->twig->render("cart_facture.html.twig",[
            "facture" => $facture,
            "site" => $infos,
            "title" => $title
        ]);
        $htmlFooter = $this->twig->render("cart_facture_footer.html.twig");
        $htmlHeader = $this->twig->render("cart_facture_header.html.twig",[
            "factureId" => $facture->factureNumero(),
            "title" => $title
        ]);
        // die($html);

        $index = DocumentFactory::makeFromString("index.html",$html);
        $footer = DocumentFactory::makeFromString("footer.html",$htmlFooter);
        $header = DocumentFactory::makeFromString("header.html",$htmlHeader);

        $assets = [
            DocumentFactory::makeFromPath("style.css",__DIR__."/../assets/css/cart_facture.css")
        ];

        try {

            $request = new HTMLRequest($index);
            $request->setHeader($header);
            $request->setFooter($footer);
            $request->setPaperSize(Request::A4);
            // $request->setMargins(Request::NO_MARGINS);
            $request->setMarginTop(1.0);
            $request->setMarginLeft(0.0);
            $request->setMarginRight(0.0);
            $request->setMarginBottom(1.0);
            $request->setResultFilename('pdf.pdf');

            $request->setAssets($assets);

            $facturePath = __DIR__."/../../../../user/data/factures/".$facture->getId().".pdf";

            $client->store($request, $facturePath);

            return $facturePath;
        
        } catch (RequestException $e) {
            # this exception is thrown if given paper size or margins are not correct.
            die($e->getMessage());
        } catch (ClientException $e) {
            # this exception is thrown by the client if the API has returned a code != 200.
            die($e->getMessage());
        }
    }
}