<?php

namespace Tethys\Web;

class ErrorHandler extends \Tethys\Core\ErrorHandler
{

    /**
     * @param \Throwable $exception
     */
    protected function renderException($exception)
    {
        ?>
        <style>

            html, body {
                font: 16px sans-serif, arial;
            }

            * {
                margin: 0;
                padding: 0;
            }

            .area {
                padding: 12px;
            }

            .title {
                font-size: 24px;
                font-weight: normal;
            }

            .message {
                margin: 12px 0;
            }

            .trace {
                font: 12px 'courier new';
            }


        </style>
        <div class="area">
            <div class="title"><?///=$exception->getName()?></div>
            <div class="message"><?=$exception->getMessage()?></div>
            <div class="trace">
                <?

                ob_start();

                foreach ($exception->getTrace() as $item) { ?>
                <div class="item">
                    <div class=""><?=($item['file'] ?? '/FILE/')?> : <?=($item['line'] ?? '/LINE/')?></div>
                    <div><?=($item['class'] ?? '/CLASS/').($item['type']??' || ').$item['function']?>()</div>
                    <? if ($item['args']) { ?>
                        <pre><? print_r($item['args']); ?></pre>
                    <? } ?>
                </div>
            <? }

                echo ob_get_clean();
//                $md = new Markdown();
//                echo $md->parse($code);

            ?>
            </div>
        </div>

        <?

    }
}