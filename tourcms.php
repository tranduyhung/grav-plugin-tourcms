<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Composer\Autoload\ClassLoader;
use RocketTheme\Toolbox\Event\Event;
Use TourCMS\Utils\TourCMS as TourCMS;

class TourCMSPlugin extends Plugin
{
    /**
     * The plugin's route in admin.
     */
    protected $admin_route = 'tourcms';

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized'      =>[
                ['autoload', 100001],
                ['onPluginsInitialized', 1001]
            ],
        ];
    }

    /**
     * @return ClassLoader
     */
    public function autoload(): ClassLoader
    {
        return require __DIR__ . '/vendor/autoload.php';
    }

    /**
     * Listen to event hooks.
     */
    public function onPluginsInitialized(): void
    {
        if (!$this->isAdmin())
        {
            $this->enable([
                'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
                'onTwigTemplatePaths'   => ['onTwigSiteTemplatePaths', 0],
            ]);
        }
        else
        {
            $this->enable([
                'onAdminMenu' => ['onAdminMenu', 0],
                'onTwigSiteVariables' => ['onTwigAdminVariables', 0],
                'onTwigTemplatePaths'   => ['onTwigAdminTemplatePaths', 0],
            ]);
        }
    }

    /**
     * Show TourCMS plugin menu item in admin.
     */
    public function onAdminMenu(): void
    {
        $this->grav['twig']->plugins_hooked_nav['PLUGIN_TOURCMS.TITLE'] = [
            'route' => $this->admin_route, 'icon' => 'fa-calendar'
        ];
    }

    /**
     * Add Twig variables for back-end.
     */
    public function onTwigAdminVariables(Event $event = null): void
    {
        $page = $this->grav['page'];

        if ($page->template() == 'tourcms')
        {
            $config = $this->grav['config'];
            $twig = $this->grav['twig'];
            $marketplaceAccountId = $config->get('plugins.tourcms.marketplace_account_id', 0);
            $channelId = $config->get('plugins.tourcms.channel_id', 0);
            $apiPrivateKey = $config->get('plugins.tourcms.api_private_key');

            $tourcms = new TourCMS($marketplaceAccountId, $apiPrivateKey, "simplexml");
            $result = $tourcms->api_rate_limit_status($channelId);

            $error = false;
            $errorMessage = '';
            $remainingHits = 0;
            $hourlyLimit = 0;

            if ($result)
            {
                if ($result->error == 'OK')
                {
                    $remainingHits = (string) $result->remaining_hits;
                    $hourlyLimit = (string) $result->hourly_limit;
                }
                else
                {
                    $error = true;
                    $errorMessage = (string) $result->error;
                }
            }
            else
            {
                $error = true;
            }

            $twig->twig_vars['tourcms_error'] = $error;
            $twig->twig_vars['tourcms_error_message'] = $errorMessage;
            $twig->twig_vars['tourcms_remaining_hits'] = $remainingHits;
            $twig->twig_vars['tourcms_hourly_limit'] = $hourlyLimit;

        }
    }

    /**
     * Add Twig template locations for back-end.
     */
    public function onTwigAdminTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/admin/templates';
    }

    /**
     * Add Twig variables for front-end
     */
    public function onTwigSiteVariables(Event $event = null): void
    {

    }

    /**
     * Add Twig template locations for front-end.
     */
    public function onTwigSiteTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}