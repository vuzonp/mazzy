<?php use Shrew\Mazzy\Lib\Template\Tpl; ?>
<!doctype html>
<html class="no-js">
    <head> 
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title><?= $tpl->name; ?></title>
        <meta name="description" content="<?= $tpl->description; ?>">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="<?= Tpl::getGlobal("assets"); ?>css/normalize.min.css">
        <style>
            body {
                font: 1em/1.4 "Segoe UI", "Cantarell", Helvetica, Verdana, sans-serif;
                margin: 0;
                padding: 0;
                background: #FEFEFE;
            }
            #panel {
                margin: 2.8em 5% 2.8em 15% ;
                padding: 0 2.8em;
            }
            h1 {
                color: #DB7093;
                font-size: 2.8em;
                line-height: 1em;
                padding: .5em 0;
                margin: 0;
            }
            p {
                font-size: 1.4em;
                line-height: 1em;
                margin: .5em 0;
            }
            em, strong {
                color: #2F4F4F;
            }
        </style>
    </head>
    <body>
        <div id="panel">
            <h1><?= _("Maintenance en cours"); ?></h1>
            <p><?= _("Notre site est actuellement indisponible, veuillez revenir plus tard"); ?></p>
        </div>
    </body>
</html>