<?php
/**
 * MiddleWare Collection
 */
namespace {

    use KikKuk\Model\DataRetrieval\Option;
    use KikKuk\Template;
    use Psr\Http\Message\ResponseInterface;
    use Psr\Http\Message\ServerRequestInterface;
    use Slim\App;
    use Slim\Container;

    if (empty($slim) || !$slim instanceof App) {
        return;
    }

    $slim->add(function(
        ServerRequestInterface $request,
        ResponseInterface $response,
        $next
    ) {
        /**
         * @var Container  $this    Container
         * @var Template   $template Template
         */
        $template = $this['view'];
        $template_directory = $template->getTemplateDirectory();
        $active_template = Option::get('template:active');
        if (!is_string($active_template) || trim($active_template) == ''
            || !is_dir( $template_directory . '/' . trim($active_template))) {
            $active_template = 'Default';
            if (!is_dir($template_directory . '/' . $active_template)) {
                throw new \RuntimeException(
                    'Could not reverse template to default!',
                    E_COMPILE_ERROR
                );
            }
            Option::update('template:active', $active_template);
        } else {
            if ($active_template != trim($active_template)) {
                Option::update('template:active', trim($active_template));
            }
        }

        $template = $template->withNew($template_directory . '/' . trim($active_template));
        unset($this['view']);
        // set attribute
        $template->setAttribute(
            'template_url',
            $template->getAttribute('template_url') . $active_template
        );
        $this['view'] = $template;
        return $next($request, $response);
    });
}
