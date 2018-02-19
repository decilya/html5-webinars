<?php

/* @var $this yii\web\View */

use app\assets\AppAsset;

AppAsset::register($this);
?>
<style>
    .wrap {
        width: 100% !important;
    }

    html,
    body {
        height: 100%;
    }

    .wrap {
        min-height: 100%;
        height: auto;
        margin: 0 auto -60px;
    / / padding : 0 0 60 px;
    }

    .wrap > .container {
        padding: 70px 15px 20px;
    }

    .footer {
        height: 60px;
        background-color: #f5f5f5;
        border-top: 1px solid #ddd;
        padding-top: 20px;
    }

    .jumbotron {
        text-align: center;
        background-color: transparent;
    }

    .jumbotron .btn {
        font-size: 21px;
        padding: 14px 24px;
    }

    /* add sorting icons to gridview sort links */
    a.asc:after, a.desc:after {
        position: relative;
        top: 1px;
        display: inline-block;
        font-family: 'Glyphicons Halflings';
        font-style: normal;
        font-weight: normal;
        line-height: 1;
        padding-left: 5px;
    }

    a.asc:after {
        content: /*"\e113"*/ "\e151";
    }

    a.desc:after {
        content: /*"\e114"*/ "\e152";
    }

    .sort-numerical a.asc:after {
        content: "\e153";
    }

    .sort-numerical a.desc:after {
        content: "\e154";
    }

    .sort-ordinal a.asc:after {
        content: "\e155";
    }

    .sort-ordinal a.desc:after {
        content: "\e156";
    }

    .grid-view th {
        white-space: nowrap;
    }

</style>
<div class="wrap">
    <div class="container">
        <div class="dummy-page">

            <h2>Внимание! Ваш браузер не поддерживает необходимый для работы функционал.</h2>

            <p>Допустимые браузеры - Google Chrome или Opera 11+. Обновите браузер или установите
                один из предложенных ниже:</p>
            <table>
                <tr>
                    <td>
                        <a href="http://www.google.com/chrome" target="_blank">
                            <img src="/img/browsers/browser-chrome.png" alt="Google Chrome"/>
                            <span>Google Chrome</span>
                        </a>
                    </td>
                    <td>
                        <a href="http://www.opera.com/ru" target="_blank">
                            <img src="/img/browsers/browser-opera.png" alt="Opera"/>
                            <span>Opera</span>
                        </a>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>


