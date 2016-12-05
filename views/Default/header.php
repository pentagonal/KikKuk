<?php
/**
 * @var \KikKuk\Template $this
 * @subpackage KikKuk
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?= htmlentities($this->getAttribute('title', 'Welcome')); ?></title>
  <link rel="stylesheet" href="<?= $this->getAttribute('base_url');?>/assets/css/bootstrap.css">
  <link rel="stylesheet" href="<?= $this->getAttribute('template_url');?>/assets/css/common.css">
</head>
<body>
