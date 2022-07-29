<?php
namespace Grav\Theme;

use Grav\Common\Theme;
use Grav\Common\Page\Page;
use RocketTheme\Toolbox\Event\Event;

class Boulangerie extends Theme
{
    private const TWITTER_ACCOUNT = "@lebonpain";

    public function onOutputGenerated(Event $e)
    {
        if (!$this->isAdmin())
        {
            $base_url = $this->grav['base_url'];
            
            $output = $this->grav->output;

            $content = preg_replace('/\[my_base_url\]/', $base_url, $output);

            $this->grav->output = $content;
        }
    }

    public function onAdminSave(Event $event)
    {
        $page = $event['object'] ?? $event['page'];

        if ($page instanceof Page) {
            if(!is_null($page->taxonomy()['type']) && in_array("product",$page->taxonomy()['type']))
            {
                $header = &$page->header();

                $header->metadata = [];
                $header->metadata['description'] = $header->description;

                // og:url
                $header->metadata['og:url'] = "[my_base_url]".$page->route();
                // og:title
                $header->metadata['og:title'] = $header->title;
                // og:description
                $header->metadata['og:description'] = $header->description;
                // og:image
                if(!is_null($header->photo)) {
                    $header->metadata['og:image'] = "[my_base_url]/".array_values($header->photo)[0]['path'];
                }
                // og:type
                $header->metadata['og:type'] = "website";
                // og:site_name
                $header->metadata['og:site_name'] = $this->grav['config']['site']['title'];
                // twitter:card
                $header->metadata['twitter:card'] = "summary_large_image";
                // twitter:site
                $header->metadata['twitter:site'] = self::TWITTER_ACCOUNT;
                // twitter:creator
                $header->metadata['twitter:creator'] = self::TWITTER_ACCOUNT;

                $this->grav['page'] = $page;
            }
        }
    }
}
