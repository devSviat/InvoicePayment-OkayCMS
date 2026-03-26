<?php

namespace Okay\Modules\Sviat\InvoicePayment;

use Okay\Core\BackendTranslations;
use Okay\Core\Design;
use Okay\Core\EntityFactory;
use Okay\Core\FrontTranslations;
use Okay\Core\OkayContainer\Reference\ServiceReference as SR;
use Okay\Core\Request;

return [
    PaymentForm::class => [
        'class' => PaymentForm::class,
        'arguments' => [
            new SR(EntityFactory::class),
        ],
    ],
    Extenders\FrontExtender::class => [
        'class' => Extenders\FrontExtender::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Request::class),
            new SR(FrontTranslations::class),
            new SR(Design::class),
        ],
    ],
    Extenders\BackendExtender::class => [
        'class' => Extenders\BackendExtender::class,
        'arguments' => [
            new SR(EntityFactory::class),
            new SR(Design::class),
            new SR(Request::class),
            new SR(BackendTranslations::class),
        ],
    ],
];
