<?php
/**
 * MiddleWare Collection
 */
namespace {

    use KikKuk\Session;
    use KikKuk\Template;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use KikKuk\Utilities\DatabaseUtility;
    use Slim\Container;
    use Slim\Http\Uri;

    if (empty($slim) || !$slim instanceof App) {
        return;
    }

    /* --------------------------------------------------------
     * SELF RESOLVE DATABASE
     * --------------------------------------------------------
     */

    /**
     * Database Structures
     * @var array
     */
    $structures = (array) $slim->getContainer()['database_schema'];
    $tables = $slim->getContainer()->get('database')->getSchemaManager()->listTableNames();
    $theTables = [];
    foreach (array_diff(array_keys($structures), $tables) as $value) {
        $theTables[$value] = $structures[$value];
    }
    if (!empty($theTables)) {
        DatabaseUtility::execSchema($theTables);
    }
    $slim->add(function(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $next
    ) {
        /**
         * @var Container  $this    Container
         * @var Session    $session Session
         * @var Template   $template Template
         * @var Uri        $uri
         */
        $session  = $this['session'];
        $template = $this['view'];
        $uri = $request->getUri();
        /**
         * Set Token
         */
        $template->setAttributes(
            [
                'token'    => $session->getCsrfTokenValue(),
                'base_url' => $uri->getBaseUrl()
            ]
        );

        return $next($request, $response);
    });
}
