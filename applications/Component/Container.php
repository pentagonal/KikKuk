<?php
namespace {

    use KikKuk\Logger;
    use KikKuk\Template;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Psr\Log\LogLevel;
    use Slim\App;
    use Slim\Container;
    use Slim\Handlers\Error;
    use Slim\Handlers\NotAllowed;
    use Slim\Handlers\NotFound;

    /**
     * Middle Collection
     */
    if (!isset($slim) || !$slim instanceof App) {
        return;
    }

    /**
     * @param Container $container
     * @return Closure
     */
    $slim->getContainer()['notAllowedHandler'] = function (&$container) {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            array $allowedMethods
        ) use (&$container) {
            try {
                /** @var Template $view */
                $view= $container['view'];
                if ($view instanceof Template) {
                    if (!$view->isEmptyListTemplate() || $view->templateHasLoaded()) {
                        unset($container['view']);
                        $container['view'] = function () use ($view) {
                            /** @var Template $template */
                            $template = new $view($view->getTemplateDirectory());
                            $template->setAttributes($view->getAttributes());
                            return $template;
                        };
                        $view = $container['view'];
                    }
                    return $view->render(
                        '403',
                        [
                            'title' => '403 Method Not Allowed',
                            'allowed_methods' => $allowedMethods
                        ],
                        $response->withStatus(403)
                    );
                }
            } catch (\Exception $err) {
            }

            $notAllowed = new NotAllowed();
            return $notAllowed($request, $response, $allowedMethods);
        };
    };

    /**
     * @param Container $container
     * @return Closure
     */
    $slim->getContainer()['notFoundHandler'] = function (&$container) {
        return function (ServerRequestInterface $request, ResponseInterface $response) use (&$container) {
            try {
                /** @var Template $view */
                $view = $container['view'];
                if ($view instanceof Template) {
                    if (!$view->isEmptyListTemplate() || $view->templateHasLoaded()) {
                        unset($container['view']);
                        $container['view'] = function () use ($view) {
                            $template = $view->withNew($view->getTemplateDirectory());
                            $template->setAttributes($view->getAttributes());
                            return $template;
                        };
                        $view = $container->get('view');
                    }
                    return $view->render(
                        '404',
                        [
                            'title' => '404 Page Not Found'
                        ],
                        $response->withStatus(404)
                    );
                }
            } catch (\Throwable $e) {
            } catch (\Exception $e) {
            }

            $notFound = new NotFound();
            return $notFound($request, $response);
        };
    };

    /**
     * @param Container $container
     * @return Closure
     */
    $slim->getContainer()['errorHandler'] = function (&$container) {
        return function (
            ServerRequestInterface $request,
            ResponseInterface $response,
            \Exception $e
        ) use (&$container) {

            /* ---------------------------------------------
                               LOGGING
             --------------------------------------------- */

            /** @var Logger $log */
            $log = $container['log'];
            $code = Logger::codeToLogLevel($e->getCode(), LogLevel::ERROR);
            $log->log(
                $code,
                sprintf(
                    'Uncaught Exception %s: "%s" at %s line %s',
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                ['exception' => $e]
            );

            try {
                /** @var Template $view */
                $view = $container['view'];
                if ($view instanceof Template) {
                    if (!$view->isEmptyListTemplate() || $view->templateHasLoaded()) {
                        unset($container['view']);
                        $container['view'] = function () use ($view) {
                            /** @var Template $template */
                            $template = new $view($view->getTemplateDirectory());
                            $template->setAttributes($view->getAttributes());
                            return $template;
                        };
                        $view = $container->get('view');
                    }
                    return $view->render(
                        '500',
                        [
                            'title' => '500 Internal Server Error',
                            'exception' => $e
                        ],
                        $response->withStatus(500)
                    );
                }
            } catch (\Exception $err) {
            }

            $error = new Error(KIK_KUK_DEV_MODE);
            return $error($request, $response, $e)->withStatus(500);
        };
    };

}
