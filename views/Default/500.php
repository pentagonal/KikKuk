<?php
/**
 * @var \KikKuk\Template $this
 * @var Exception        $exception
 * @subpackage KikKuk
 */

$this->partial('header');
echo <<<EOF
{$exception->getMessage()}
{$exception->getFile()}
{$exception->getLine()}
EOF;

$this->partial('footer');
