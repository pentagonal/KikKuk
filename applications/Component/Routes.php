<?php
namespace {

    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Slim\Container;

    if (empty($slim) || !$slim instanceof App) {
        return;
    }

    /**
     *Add Route Post & GET
     */
    $slim->map(
        ['POST', 'GET'],
        '/',
        function (ServerRequestInterface $request, ResponseInterface $response) {
            /**
             * @var $this Container
             */
            return $this['view']->render(
                'login',
                [
                    'title' => 'Login to member area'
                ],
                $response
            );
        }
    );
}
