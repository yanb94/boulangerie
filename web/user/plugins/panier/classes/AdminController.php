<?php 

namespace Grav\Plugin\Panier;

use Exception;
use SplFileInfo;
use Grav\Common\Grav;
use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use Stripe\StripeClient;

class AdminController
{
    private array $config;
    private array $env_vars;
    private Grav $grav;
    private Manager $manager;

    public function __construct(Grav $grav, Manager $manager, array $config, array $env_vars)
    {
        $this->grav = $grav;
        $this->config = $config;
        $this->manager = $manager;
        $this->env_vars = $env_vars;
    }

    public static function findRoute(string $uri)
    {
        $regex = [
            '/^\/admin\/invoices(\/[a-z]+)?(\/[0-9]+)?$/' => "invoicesList",
            '/^\/admin\/order\/validate\/(.*)$/' => "deliverOrder",
            '/^\/admin\/order\/refund\/(.*)$/' => "refundOrder",
            '/^\/admin\/order\/(.*)$/' => "order",
        ];

        foreach ($regex as $key => $value) {
            $matches = [];
            if(preg_match($key,$uri,$matches))
            {
                unset($matches[0]);

                $matches = array_map(function($n){
                    return str_replace("/","",$n);
                },$matches);

                return [
                    "method" => $value,
                    "params" => $matches
                ];
            }
        }

        return null;
    }

    public function executeController(string $uri, array $config)
    {
        $route = self::findRoute($uri);

        if(!is_null($route))
        {
            $this->config = $config;
            unset($this->grav['page']);
            $method = $route['method'];
            $this->grav['page'] = call_user_func_array([$this,$method],$route['params']);
        }

        $this->removeFlash();
    }

    private function initPage(string $file): Page
    {
        $page = new Page();
        $page->init(new SplFileInfo(__DIR__. '/../pages/admin'.$file));

        return $page;
    }

    public function invoicesList(string $type = 'all',int $pageNb = 1)
    {
        $amountToday = $this->manager->getAmountToday();
        $amountWeek = $this->manager->getAmountWeek();
        $amountYear = $this->manager->getAmountYear();
        $amountWait = $this->manager->getAmountWait();

        if($pageNb < 1)
        {
            $this->grav->redirect("admin/error");
        }

        if(in_array($type,[Facture::DELIVRED,Facture::PAYED,Facture::REFUND]))
        {
            $factures = $this->manager->getListFacturesByType($type,$pageNb,$this->env_vars['PAGINATION_ORDER']);
            $maxRow = $this->manager->countFacturesByType($type);
        }
        else
        {
            $type = 'all';
            $factures = $this->manager->getListFactures($pageNb,$this->env_vars['PAGINATION_ORDER']);
            $maxRow = $this->manager->countFactures();
        }

        /** @var Twig */
        $twig = $this->grav["twig"];

        $maxPage = (int) ceil($maxRow/$this->env_vars['PAGINATION_ORDER']);

        if($pageNb > $maxPage && $pageNb != 1)
        {
            $this->grav->redirect("admin/error");
        }
        

        $page = $this->initPage("/invoices_list.md");

        $twig->twig_vars["invoice_list"] = $factures;
        $twig->twig_vars['pageNb'] = (int)$pageNb;
        $twig->twig_vars['maxPage'] = $maxPage;
        $twig->twig_vars['prevPage'] = $pageNb - 1;
        $twig->twig_vars['nextPage'] = $pageNb + 1;
        $twig->twig_vars["type"] = $type;

        $twig->twig_vars['amountToday'] = $amountToday;
        $twig->twig_vars['amountWeek'] = $amountWeek;
        $twig->twig_vars['amountYear'] = $amountYear;
        $twig->twig_vars['amountWait'] = $amountWait;

        return $page;
    }

    public function order(string $id)
    {
        /** @var Twig */
        $twig = $this->grav["twig"];

        $facture = $this->manager->getFacture($id,Store::readData());

        $twig->twig_vars['order'] = $facture;

        $twig->twig_vars['flash'] = [
            "success" => $this->getFlash("success"),
            "error" => $this->getFlash("error"),
        ];

        $page = $this->initPage("/order.md");

        $page->modifyHeader("title","Commande ".$facture->factureNumero());

        return $page;
    }

    public function deliverOrder(string $id)
    {
        $facture = $this->manager->getFacture($id,Store::readData());

        $facture->setStatut(Facture::DELIVRED);

        $this->manager->updateFactureStatut($facture);

        $this->createFlashMessage("success","La commande a bien été délivré");

        $this->grav->redirect("admin/order/".$facture->getId());
    }

    public function refundOrder(string $id)
    {
        $facture = $this->manager->getFacture($id,Store::readData());
        $idStripe = $facture->getIdStripe();

        try
        {
            $stripe = new StripeClient($this->env_vars['STRIPE_PRIVATE_KEY']);

            $stripe->refunds->create([
                'payment_intent' => $idStripe
            ]);

            $facture->setStatut(Facture::REFUND);
            $this->manager->updateFactureStatut($facture);

            $this->createFlashMessage("success","La commande a bien été remboursé");
            $this->grav->redirect("admin/order/".$facture->getId());
        }
        catch(Exception $e)
        {
            $this->createFlashMessage("error","Une erreur n'a pas permit le remboursement");
            $this->grav->redirect("admin/order/".$facture->getId());
        }
    }

    private function createFlashMessage(string $label, string $content):void
    {
        $_SESSION['flash'][$label] = $content;
    }

    private function getFlash(string $label)
    {
        return $_SESSION['flash'][$label];
    }

    private function removeFlash()
    {
        unset($_SESSION['flash']);
    }
}