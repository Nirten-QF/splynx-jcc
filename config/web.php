<?php

return function ($params, $baseDir) {
    return [
        'components' => [
            'request' => [
                'baseUrl' => '/jcc',
            ],
            'jcc' => [
                'class' => '\app\components\Jcc',
                'clientId' => $params[\splynx\helpers\ConfigHelper::DEFAULT_PARTNERS_SETTINGS_FIELD]['clientId'],
                'clientSecret' => $params[\splynx\helpers\ConfigHelper::DEFAULT_PARTNERS_SETTINGS_FIELD]['clientSecret'],
				'clientPassword' => $params[\splynx\helpers\ConfigHelper::DEFAULT_PARTNERS_SETTINGS_FIELD]['clientPassword'],
                'isProduction' => $params['isProduction'],

                // This is config file for the Jcc system
                'config' => [
                    'http.ConnectionTimeOut' => 30,
                    'http.Retry' => 1,
                    'mode' => 'sandbox',    // sandbox | live
                    'log.LogEnabled' => YII_DEBUG ? 1 : 0,
                    'log.FileName' => '@runtime/logs/Jcc.log',
                    'log.LogLevel' => 'FINE',   // FINE | INFO | WARN | ERROR
                ]
            ],
        ],
    ];
};
