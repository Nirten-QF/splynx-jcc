<?php

namespace app\commands;

use splynx\base\BaseInstallController;

class InstallController extends BaseInstallController
{

    /**
     * @inheritdoc
     */
    public function getAddOnTitle()
    {
        return "Splynx Jcc add-on";
    }

    /**
     * @inheritdoc
     */
    public function getModuleName()
    {
        return 'splynx_jcc_addon';
    }

    /**
     * @inheritdoc
     */
    public function getApiPermissions()
    {
        return [
            [
                'controller' => 'api\admin\finance\BankStatements',
                'actions' => ['index', 'view', 'add'],
            ],
            [
                'controller' => 'api\admin\finance\BankStatementsRecords',
                'actions' => ['index', 'view', 'add', 'update'],
            ],
            [
                'controller' => 'api\admin\finance\Payments',
                'actions' => ['index', 'add'],
            ],
            [
                'controller' => 'api\admin\finance\Transactions',
                'actions' => ['index', 'add'],
            ],
            [
                'controller' => 'api\admin\finance\Invoices',
                'actions' => ['index', 'view', 'update'],
            ],
            [
                'controller' => 'api\admin\finance\Requests',
                'actions' => ['index', 'view', 'update'],
            ],
            [
                'controller' => 'api\admin\customers\Customer',
                'actions' => ['index', 'view'],
            ],
            [
                'controller' => 'api\admin\administration\Partners',
                'actions' => ['index', 'view'],
            ],
//            [
//                'controller' => 'api\admin\customers\CustomerPaymentAccounts',
//                'actions' => ['index', 'view', 'update', 'delete'],
//            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getEntryPoints()
    {
        return [
            [
                'name' => 'jcc_pay_invoice_point',
                'title' => 'Pay by Jcc',
                'icon' => 'fa-Jcc',
                'place' => 'portal',
                'type' => 'action_link',
                'model' => 'Invoices',
                'url' => '/jcc/pay-invoice'
            ],
            [ 
                'name' => 'jcc_pay_request_point',
                'title' => 'Pay by Jcc',
                'icon' => 'fa-Jcc',
                'place' => 'portal',
                'type' => 'action_link',
                'model' => 'Requests',
                'url' => '/jcc/pay-request'
            ],
            [
                'name' => 'jcc_pay_on_dashboard',
                'place' => 'portal',
                'type' => 'code',
                'root' => 'controllers\portal\DashboardController',
                'code' => file_get_contents(\Yii::$app->getViewPath() . '/dashboard.twig')
            ],
            [
                'name' => 'jcc_add_money_on_dashboard',
                'place' => 'portal',
                'type' => 'code',
                'root' => 'controllers\portal\DashboardController',
                'code' => file_get_contents(\Yii::$app->getViewPath() . '/add_money_dashboard.twig'),
                'enabled' => false,
            ],
        ];
    }
}
