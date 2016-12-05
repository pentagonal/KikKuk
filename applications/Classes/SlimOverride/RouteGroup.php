<?php
namespace KikKuk\SlimOverride;

/**
 * Class RouteGroup
 * @package KikKuk\SlimOverride
 */
class RouteGroup extends \Slim\RouteGroup
{
    /**
     * Invoke the group to register any Routable objects within it.
     *
     * @param \Slim\App $app The App to bind the callable to.
     */
    public function __invoke(\Slim\App $app = null)
    {
        $callable = $this->resolveCallable($this->callable);
        if ($callable instanceof \Closure && $app !== null) {
            $callable = $callable->bindTo($app);
        }

        if (is_object($callable) && method_exists($callable, '__invoke')) {
            $callable($app);
        } else {
            call_user_func_array($callable, [$app]);
        }
    }
}
