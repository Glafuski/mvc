<!DOCTYPE HTML>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="referrer" content="origin">
        <title><?= $title . ' | ' . CONFIG['server']['name'] ?></title>
        <?php 
            plugin::load('css, js, templateloader');
            new Core\App\Css();
            new Core\App\Js();
            $template = new Core\App\Template();
        ?>
    </head>
    <body>
    <header>
        <?php
            $template->Load(['name' => 'header_logo']);
            $template->Load(['name' => 'header_menu']);
        ?>
    </header>