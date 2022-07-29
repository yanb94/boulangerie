<?php
namespace Grav\Plugin;

use Exception;
use Grav\Common\Plugin;
use Grav\Common\Taxonomy;
use Grav\Common\Page\Page;
use Grav\Common\Twig\Twig;
use Composer\Autoload\ClassLoader;
use Grav\Plugin\ResizeImg\Store;

/**
 * Class ResizeImgPlugin
 * @package Grav\Plugin
 */
class ResizeImgPlugin extends Plugin
{
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
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {

            $this->enable([
                "onAdminAfterSave" => "onAdminAfterSave"
            ]);

            return;
        }

        // Enable the main events we are interested in
        $this->enable([
            'onTwigExtensions' => ['onTwigExtensions',0]
        ]);
    }

    public function onAdminAfterSave($event): void
    {
        $page = $event['object'] ?? $event['page'];

        if ($page instanceof Page) {
            if(!is_null($page->taxonomy()['type']) && in_array("product",$page->taxonomy()['type']))
            {
                $slug = $page->slug();
                $futurePhoto = !is_null($page->header()->photo) ? array_key_first($page->header()->photo) : null;

                if(!is_null($futurePhoto) && file_exists($futurePhoto))
                {
                    $path = "user/pages/".$slug;

                    $files = scandir($path);

                    foreach ($files as $file) {
                        
                        $raw = explode('.',$file);
                        $ext = $raw[count($raw)-1];

                        $pathFile = $path."/".$file;

                        if(in_array($ext,['png','jpg','jpeg']))
                        {
                            $len = strlen($futurePhoto);

                            if(substr($pathFile, 0, $len) !== $futurePhoto)
                            {
                                unlink($pathFile);

                                if(isset(Store::readData()[$pathFile]))
                                {
                                    Store::removeImage($pathFile);
                                }
                            }
                        }
                    }

                }
            }
        }
    }

    public function onTwigExtensions(): void
    {
        /** @var Twig */
        $twigService = $this->grav['twig'];

        $twigService->twig()->addFunction("resizeProduct",new \Twig_SimpleFunction("resizeProduct",function(string $path,int $width, int $height, string $prefix = "list"){
            
            try
            {
                // Remove leading /
                $explodeL = explode("/",$path);
                if($explodeL[0] == "")
                    unset($explodeL[0]);

                $pathNew = implode("/",$explodeL);

                $explode = explode(".",$pathNew);
                $last = $explode[count($explode) - 1];
                $result = "/".$pathNew.".".$prefix.".".$last;

                if(!isset(Store::readData()[$result]))
                {
                    $imagick = new \Imagick();

                    $imagick->readImage($pathNew);

                    $imagick->thumbnailImage($width,$height,false,false);

                    $imagick->writeImage($pathNew.".".$prefix.".".$last);

                    Store::addImage($result);
                }

                return $result;
            }
            catch(Exception $e)
            {
                return $path;
            }

        }));
    }
}
