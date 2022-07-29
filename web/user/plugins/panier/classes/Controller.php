<?php
namespace Grav\Plugin\Panier;

use Exception;
use SplFileInfo;
use Stripe\Stripe;
use Stripe\TaxRate;
use Grav\Common\Grav;
use Swift_Attachment;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use Grav\Plugin\Email\Email;
use Stripe\Checkout\Session;
use chillerlan\QRCode\QRCode;
use Grav\Plugin\Panier\Store;
use Grav\Plugin\Panier\Panier;
use Grav\Plugin\Panier\Manager;
use chillerlan\QRCode\QROptions;
use Grav\Plugin\Panier\Database;
use Grav\Plugin\Panier\OrderRow;
use Swift_Mime_SimpleMimeEntity;
use chillerlan\QRCode\Output\QRImage;
use TheCodingMachine\Gotenberg\Client;
use Stripe\Exception\ApiErrorException;
use Stripe\Refund;
use TheCodingMachine\Gotenberg\Request;
use TheCodingMachine\Gotenberg\HTMLRequest;
use TheCodingMachine\Gotenberg\ClientException;
use TheCodingMachine\Gotenberg\DocumentFactory;
use TheCodingMachine\Gotenberg\RequestException;

class Controller
{
    private Grav $grav;
    private array $config;
    private Manager $manager;
    private array $env_vars;

    public function __construct(Grav $grav, Manager $manager, array $config, array $env_vars)
    {
        $this->grav = $grav;
        $this->config = $config;
        $this->manager = $manager;
        $this->env_vars = $env_vars;
    }

    public static function routes(): array
    {
        return [
            '/panier/add' => "addAction",
            '/panier/full' => "fullAction",
            '/panier/decrease' => "decreaseAction",
            '/panier/quantity' => "setQuantityAction",
            '/panier/clear' => "clearAction",
            '/panier/clearProduct' => "clearProductAction",
            '/panier/validate' => "validateCart",
            '/panier/vider' => "viderPanier",
            '/panier/pay' => "payPanier",
            '/panier/checkout-session' => "checkoutSession",
            '/panier/success-order' => "successOrder",
            '/panier/order-complete' => "completeOrder"
        ];
    }

    private function getJsonData(): ?array
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    private function initPage(string $file = "/json_info.md"): Page
    {
        $page = new Page();
        $page->init(new SplFileInfo(__DIR__. '/../pages'.$file));

        return $page;
    }

    private function createResponse(Page $page, array $content, string $http_code): Page
    {
        $page->expires(0);
        $page->modifyHeader("content",$content);
        $page->modifyHeader("http_response_code",$http_code);
        return $page;
    }

    public function executeController(string $uri, array $config)
    {
        $action = self::routes()[$uri];
        $this->config = $config;

        if($action != null){
            unset($this->grav['page']);
            $this->grav['page'] = $this->$action();
        }
    }

    public function fullAction(): Page
    {
        $page = $this->initPage();
        return $this->createResponse(
            $page,
            Panier::getPanier(
                Panier::getCookie(),
                Store::readData(),
                $this->config['vat']
            )
        ,200);
    }

    public function addAction()
    {
        $data = $this->getJsonData();
        $page = $this->initPage();

        if(isset($data['action']) && $data['action'] == 'add' && isset($data['ref']))
        {
            Panier::addProductToPanier($data['ref'],Store::readData());

            return $this->createResponse($page,["msg"=>"Cart successfully changed"],200);
        }

        return $this->createResponse($page,["msg"=>"Invalid data"],400);
    }

    public function decreaseAction()
    {
        $data = $this->getJsonData();
        $page = $this->initPage();

        if(isset($data['action']) && $data['action'] == 'decrease' && isset($data['ref']))
        {
            Panier::decreaseProductPanier($data['ref'],Store::readData());
            return $this->createResponse($page,["msg"=>"Cart successfully changed"],200);
        }

        return $this->createResponse($page,["msg"=>"Invalid data"],400);
    }

    public function setQuantityAction()
    {
        $data = $this->getJsonData();
        $page = $this->initPage();

        if(isset($data['action']) && $data['action'] == 'setQuantity' && isset($data['ref']) && isset($data['quantity']))
        {
            Panier::setQuantityProductPanier($data['ref'],(int)$data['quantity'],Store::readData());
            return $this->createResponse($page,["msg"=>"Cart successfully changed"],200);
        }

        return $this->createResponse($page,["msg"=>"Invalid data"],400);
    }

    public function clearAction()
    {
        $page = $this->initPage();
        Panier::clearPanier();
        return $this->createResponse($page,["msg"=>"Cart clear"],200);
    }

    public function clearProductAction()
    {
        $data = $this->getJsonData();
        $page = $this->initPage();

        if(isset($data['action']) && $data['action'] == 'clearProduct' && isset($data['ref']))
        {
            Panier::clearProductPanier($data['ref']);
            return $this->createResponse($page,["msg"=>"Cart successfully changed"],200);
        }

        return $this->createResponse($page,["msg"=>"Invalid data"],400);
    }

    public function validateCart()
    {
        $page = $this->initPage("/cart_validate.md");

        $panier = Panier::getPanier(
            Panier::getCookie(),
            Store::readData(),
            $this->config['vat']);

        if(isset($panier['list']))
        {
            $page->modifyHeader("title","Valider votre panier");
        }
        else
        {
            $page->modifyHeader("title","Votre panier est actuellement vide");
        }

        $page->expires(0);

        return $page;
    }

    public function viderPanier()
    {
        Panier::clearPanier();
        $this->grav->redirect("/panier/validate");
    } 

    public function payPanier()
    {
        $page = $this->initPage("/cart_pay.md");

        $panier = Panier::getPanier(Panier::getCookie(),Store::readData(),$this->config['vat']);

        $manager = $this->manager;

        $order = $manager->createCommand($panier,$this->config['vat']);
        $manager->saveToDB($order);

        $page->expires(0);

        return $page;
    }

    public function checkoutSession()
    {
        $page = $this->initPage();
        $data = $this->getJsonData();

        $order = $this->manager->getOrder(Panier::getCookie()['id'],Store::readData());

        Stripe::setApiKey($this->env_vars['STRIPE_PRIVATE_KEY']);

        $intent = null;
        try
        {
            if(isset($data['payment_method_id']))
            {
                $intent = PaymentIntent::create([
                    'payment_method' => $data['payment_method_id'],
                    'amount' => round($order->getPrice_ttc(),2)*100,
                    'currency' => 'eur',
                    'confirmation_method' => 'manual',
                    'confirm' => true,
                ]);

            }
            
            if(isset($data['payment_intent_id']))
            {
                $intent = PaymentIntent::retrieve(
                    $data['payment_intent_id']
                );

                $intent->confirm();
            }

            return $this->generateResponse($intent,$page);
        }
        catch(ApiErrorException $e)
        {
            return $this->createResponse($page,['error' => $e->getMessage()],500);
        }

    }

    private function generateResponse(?PaymentIntent $intent,Page $page): Page
    {
        if($intent->status == 'requires_action' &&  $intent->next_action->type == 'use_stripe_sdk')
        {
            return $this->createResponse($page,[
                'requires_action' => true,
                'payment_intent_client_secret' => $intent->client_secret
            ],200);
        }
        else if($intent->status == 'succeeded')
        {
            $stripe = new StripeClient($this->env_vars['STRIPE_PRIVATE_KEY']);

            $paymentMethod = $stripe->paymentMethods->retrieve(
                $intent->payment_method
            );

            $this->finishOrder(
                $paymentMethod->metadata['cookieId'],
                $paymentMethod->billing_details['name'],
                $paymentMethod->billing_details['email'],
                $intent->id
            );

            return $this->createResponse($page,[
                "success" => true,
                "id" => $intent->id,
                "object" => $intent,
                "paymentMethod" => $paymentMethod,
                "metadata" => $paymentMethod->metadata['cookieId']
            ],200);
        }
        else
        {
            return $this->createResponse($page,[
                'error' => 'Invalid PaymentIntent status'
            ],500);
        }
    }

    private function finishOrder(string $cookieId, string $fullname, string $email, string $idStripe)
    {        
        try{
            $arr = explode(" ",$fullname);
            $firstname = $arr[0];
            $lastname = $arr[1];

            $order = $this->manager->getOrder($cookieId,Store::readData());

            // Init invoice data

            $facture = new Facture();
            $facture->dataFromOrder($order);
            $facture->setIdStripe($idStripe);

            $facture->setFirstname($firstname);
            $facture->setLastname($lastname);
            $facture->setEmail($email);

            $this->manager->saveFacture($facture);

            $factureId = $facture->getId();

            $facture = $this->manager->getFacture($factureId);

            // Delete cart and order

            $this->manager->deleteOrder($order->getCookie());
            Panier::clearPanier();

            // Generate invoice

            /** @var Twig */
            $twigRender = $this->grav['twig'];

            $pdfGenerator = new PdfHelper($twigRender->twig);
            $facturePath = $pdfGenerator->generatePdf(
                $facture,
                $this->grav['config']['site']['info'],
                $this->grav['config']['site']['title']
            );


            // Generate the QRCode
            $qrHelper = new QrCodeHelper($this->env_vars['DOMAIN']);

            $qr = $qrHelper->generateQrCode($facture);
            $qrUrl = $qr['qrUrl'];
            $qrFile = $qr['qrFile'];

            // Send Email

            /** @var Email */
            $emailService = $this->grav['Email'];

            $emailSender = new EmailHelper($emailService,$twigRender->twig);

            $emailSender->sendEmail(
                "email_pay.html.twig",
                [
                    "firstname" => $firstname,
                    "lastname" => $lastname,
                    "qrCode" => $qrUrl,
                    "contactUrl" => $this->env_vars['DOMAIN'] . "/contact"
                ],
                "Votre commande",
                $this->env_vars['ADMIN_EMAIL'],
                $email,
                [
                    $qrFile,
                    $facturePath
                ]
            );
        }
        catch(Exception $e)
        {
            Refund::create([
                'payment_intent' => $idStripe
            ]);

            throw new Exception($e);
        }
    }


    public function completeOrder()
    {
        $page = $this->initPage("/cart_success.md");

        $page->modifyHeader("title","Commande réussi");
        $page->modifyHeader("content", "Votre commande a bien été effectué.".
        " Vous allez recevoir un email contenant la facture ainsi q'un QrCode vous permettant de retirer votre commande en boutique. <br/> <br/>".
        " Si le moindre problème survient n'hésiter pas a nous contacter via le formulaire de contact. <br/><br/>".
        " A bientôt au Bon Pain"
        );
        $page->modifyHeader("url_label","Revenir a l'accueil");
        $page->modifyHeader("url","/");

        return $page;
    }
}