<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?><!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>Test1</title>
        <style type="text/css">

            ::selection { background-color: #E13300; color: white; }
            ::-moz-selection { background-color: #E13300; color: white; }

            body {
                background-color: #fff;
                margin: 40px;
                font: 13px/20px normal Helvetica, Arial, sans-serif;
                color: #4F5155;
            }

            a {
                color: #003399;
                background-color: transparent;
                font-weight: normal;
            }

            h1 {
                color: #444;
                background-color: transparent;
                border-bottom: 1px solid #D0D0D0;
                font-size: 19px;
                font-weight: normal;
                margin: 0 0 14px 0;
                padding: 14px 15px 10px 15px;
            }

            code {
                font-family: Consolas, Monaco, Courier New, Courier, monospace;
                font-size: 12px;
                background-color: #f9f9f9;
                border: 1px solid #D0D0D0;
                color: #002166;
                display: block;
                margin: 14px 0 14px 0;
                padding: 12px 10px 12px 10px;
            }

            #body {
                margin: 0 15px 0 15px;
            }

            p.footer {
                text-align: right;
                font-size: 11px;
                border-top: 1px solid #D0D0D0;
                line-height: 32px;
                padding: 0 10px 0 10px;
                margin: 20px 0 0 0;
            }

        </style>

<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="<?php echo rtrim(BASEWEB_PATH,'/www')?>/www/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

<!-- Optional theme -->
<link rel="stylesheet" href="<?php echo rtrim(BASEWEB_PATH,'/www')?>/www/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<style>
            table {
                width: 900px;
                min-width: 800px;
            }
            table th {
                background-color: #ddd;
                text-transform: uppercase;
                border: 1px solid rgba(191, 191, 191, .5);
            }
            table th a {
                color: grey;
            }
            .content-inner-2 {
                margin: 0 auto;
            }
            code {
                color: #6b6a6b;
                background-color: #f5f3f3;
            }
            pre {
                color: #6b6a6b;
            }
            .list-group-item.active a, .list-group-item.active a:focus, .list-group-item.active a:hover {
                color: white;
            }

</style>

<!-- Latest compiled and minified JavaScript -->
<script src="<?php echo rtrim(BASEWEB_PATH,'/www')?>/www/js/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
<script src="<?php echo rtrim(BASEWEB_PATH,'/www')?>/www/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>


    </head>
    <body>

        <div class="container-fluid">
            <h1>Supermodel</h1>
                <div class="row">
                    <div class="col-md-2 flex-nowrap" style='width: 290px; max-height: 100%;position:fixed;'>
                        <ul class="list-group" style="margin-bottom:-1px;">
                            <li class="list-group-item"><strong>Getting started</strong></li>
                        </ul>
                        <div class="list-group">
                            <a class="list-group-item list-group-item-action<?php echo $view=='introduction'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/introduction/">Introduction</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='install'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/install/">Install</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='structure'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/structure/">Model structure</a>
                        </div>
                        <ul class="list-group" style="margin-bottom:-1px;">
                            <li class="list-group-item"><strong>Compose a Query</strong></li>
                        </ul>
                        <div class="list-group">
                            <a class="list-group-item list-group-item-action<?php echo $view=='get'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/get/">Get</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='getall'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/getall/">GetAll</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='fields'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/fields/">Fields</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='where'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/where/">Where</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='groupby'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/groupby/">GroupBy</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='orderby'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/orderby/">OrderBy</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='joins'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/joins/">Joins</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='relations'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/relations/">Relations</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='model'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/page/model/">Model</a>
                            <a class="list-group-item list-group-item-action<?php echo $view=='listtables'?' active':''?>" href="<?php echo BASEWEB_PATH?>/sdemo/listtables/">ListTables</a>
                        </div>
                    </div>
                    <div id="content" class="col" style='max-width:1400px; padding-left: 300px;'>
                        <?php
                            if (isset($view) && $view != '') {
                                $this->view('supermodel/'.$view, array('data' => $data));
                            }
                        ?>
                    </div>
                </div>
        </div>

    </body>
</html>