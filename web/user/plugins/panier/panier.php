<?php
namespace Grav\Plugin;

use PDO;
use SplFileInfo;
use Dotenv\Dotenv;
use Monolog\Logger;
use Grav\Common\Grav;
use Grav\Common\Plugin;
use Grav\Common\Page\Page;
use Grav\Common\Page\Pages;
use Grav\Plugin\Panier\Store;
use Grav\Common\Config\Config;
use Grav\Plugin\Panier\Panier;
use Grav\Plugin\Panier\Manager;
use Grav\Common\Page\Collection;
use Grav\Plugin\Panier\Database;
use Composer\Autoload\ClassLoader;
use Grav\Common\Data\Data;
use Grav\Plugin\Panier\Controller;
use Grav\Plugin\Panier\AdminController;

/**
 * Class PanierPlugin
 * @package Grav\Plugin
 */
class PanierPlugin extends Plugin
{
    private Manager $manager;

    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            'onPluginsInitialized' => [
                ['autoload', 100000], // TODO: Remove when plugin requires Grav >=1.7
                ['onPluginsInitialized', 0]
            ]
        ];
    }

    /**
    * Composer autoload.
    *is
    * @return ClassLoader
    */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized(): void
    {
        $dotEnv = Dotenv::createImmutable(__DIR__."/../../../")->load();
        $this->initDatabase();

        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            $this->enable([
                'onAdminSave' => ['onAdminSave',0],
                'onTwigTemplatePaths' => ['onTwigTemplatePaths',0],
                'onPagesInitialized'=> ['onPagesInitialized',0],
            ]);
        }
        else
        {
            Panier::initCookie();
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables',0],
                'onPagesInitialized'=> ['onPagesInitialized',0],
                'onTwigTemplatePaths' => ['onTwigTemplatePaths',0]
            ]);
        }
    }

    public function onAdminSave($event)
    {
        $page = $event['object'] ?? $event['page'];

        /** @var Pages */
        $pages = $this->grav['pages'];

        $collection = (new Collection($pages->all()->toArray()));
        $collection->ofType('product');

        if ($page instanceof Page) {
            if(!is_null($page->taxonomy()['type']) && in_array("product",$page->taxonomy()['type']))
            {
                Store::updateData($collection);
            }
        }

        if($page instanceof Data)
        {
            if($page->blueprints()->getFilename() === 'config/database') {
                $databaseData = $page->get('database');
                $this->createTables($databaseData);
            }
        }
    }

    public function onTwigSiteVariables(): void
    {
        /** @var Pages */
        $pages = $this->grav['pages'];

        $collection = (new Collection($pages->all()->toArray()));
        $collection->ofType('product');

        Store::updateData($collection);

        $panier = Panier::getPanier(Panier::getCookie(),Store::readData(),$this->config()['vat']);

        foreach ($panier as $key => $value) {
            $this->grav["twig"]->twig_vars['panier'][$key] = $value;
        }

        $commandPage = [
            '/panier/validate',
            '/panier/pay'
        ];

        if(in_array($this->grav['uri']->path(),$commandPage))
        {
            $this->grav["twig"]->twig_vars["show_panier"] = false;

            if($this->grav['uri']->path() == '/panier/pay')
            {
                $this->grav["twig"]->twig_vars["order"] = $this->manager->getOrder(Panier::getCookie()['id'],Store::readData());
            }
        }
        else
        {
            $this->grav["twig"]->twig_vars["show_panier"] = true;
        }
    }

    public function onPagesInitialized()
    {
        $uri = $this->grav['uri'];

        $env_vars = [
            "STRIPE_PUBLIC_KEY" => $_ENV['STRIPE_PUBLIC_KEY'],
            "STRIPE_PRIVATE_KEY" => $_ENV['STRIPE_PRIVATE_KEY'],
            "DOMAIN" => $_ENV['DOMAIN'],
            "PAGINATION_ORDER" => $_ENV['PAGINATION_ORDER'],
            "ADMIN_EMAIL" => $_ENV['ADMIN_EMAIL']
        ];

        if ($this->isAdmin())
        {
            $controller = new AdminController($this->grav,$this->manager,$this->config(), $env_vars);
            $controller->executeController($uri->path(),$this->config());
        }
        else
        {
            $controller = new Controller($this->grav,$this->manager,$this->config(), $env_vars);
            $controller->executeController($uri->path(),$this->config());
        }

    }

    public function onTwigTemplatePaths(): void
    {
        if ($this->isAdmin())
        {
            $this->grav['twig']->twig_paths[] = __DIR__ . '/templates/admin';
        }
        else
        {
            $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
        }
        
    }

    private function initDatabase():void
    {
        $configData = $this->grav['config']['database']['database'];
        $database = new Database(
            $configData['host'],
            $configData['dbname'],
            $configData['user'],
            $configData['pass']
        );

        $manager = new Manager($database);

        $this->manager = $manager;
        
    }

    private function createTables(array $databaseData): void
    {
        $database = new Database(
            $databaseData['host'],
            $databaseData['dbname'],
            $databaseData['user'],
            $databaseData['pass']
        );

        $manager = new Manager($database);

        $this->manager = $manager;

        $this->manager->initTable();
    }

}
