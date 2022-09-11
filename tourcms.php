<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use Composer\Autoload\ClassLoader;
use RocketTheme\Toolbox\Event\Event;

class TourCMSPlugin extends Plugin
{
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
        if ($this->isAdmin())
        {
            return;
        }

        $this->enable([
            'onTwigSiteVariables' => ['onTwigSiteVariables', 0],
            'onTwigTemplatePaths'   => ['onTwigTemplatePaths', 0],
        ]);
    }

    /**
     * Add Twig variables.
     */
    public function onTwigSiteVariables(Event $event = null): void
    {

    }

    /**
     * Add Twig template locations.
     */
    public function onTwigTemplatePaths(): void
    {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }
}