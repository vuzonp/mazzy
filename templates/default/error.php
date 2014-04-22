<?php

use Shrew\Mazzy\Lib\Template\Tpl;
?>
<html>
    <head>
        <title><?= $name = Tpl::toText($error->name); ?></title>
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
                color: #CD5C5C;
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
            pre, code {
                display: inline-block;
                vertical-align: baseline;
                background: #FFFAF0; 
                font-size: 1em;
                line-height: normal;
                padding: .5ex;
                border: 1px solid #FFE4E1;
                color: #2F4F4F;
                -webkit-border-radius: 3px;
                -moz-border-radius: 3px;
                border-radius: 3px;
            }
            
            pre {
                margin: .7em 0;
                padding: .7em;
                line-height: 2.1em;
                overflow-x: auto;
                max-width: 100%;
            }
        </style>
    </head>
    <body>
        <div id="panel">
            <h1><?= $name; ?></h1>
            <p><?= Tpl::toHtml($error->message); ?></p>
            <pre><?= $error->trace; ?></pre>
        </div>
    </body>
</html>