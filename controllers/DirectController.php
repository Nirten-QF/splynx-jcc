<?php

namespace app\controllers;

use app\components\ValidateConfig;
use app\models\PayInvoiceForm;
use app\models\PayRequestForm;
use splynx\models\finance\BaseInvoice;
use splynx\models\finance\BaseProformaInvoice;
use yii;
use yii\web\Controller;

class DirectController extends Controller
{
    public $enableCsrfValidation = false;

    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ]
        ];
    }

    public function actionDirectPayInvoice($item_id, $status = null, $paymentId = null, $PayerID = null)
    {
        if (empty($item_id)) {
            throw new yii\base\UserException(Yii::t('app', 'No invoice id!'));
        }

        // Find invoice
        $invoice = BaseInvoice::findByNumber($item_id);
        if (empty($invoice)) {
            throw new yii\base\UserException(Yii::t('app', 'Invalid invoice number!'));
        }

        ValidateConfig::checkAll($invoice->customer_id);

        // Check invoice status
        if ($invoice->status != BaseInvoice::STATUS_NOT_PAID) {
            if ($invoice->status == BaseInvoice::STATUS_PAID) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Invoice already paid!'));
            } elseif ($invoice->status == BaseInvoice::STATUS_DELETED) {
                Yii::$app->getSession()->setFlash('warning', Yii::t('app', 'Invoice deleted!'));
            }
            return $this->render('result', []);
        }

        $model = new PayInvoiceForm();
        $model->invoice = $invoice;

        // If `status` is set - process payment
        if ($status !== null) {
            if ($status == 'cancel') {
                $model->setCancel();
                Yii::$app->getSession()->setFlash('warning', Yii::t('app', 'Payment was cancelled!'));
            } elseif ($status == 'success') {
                $model->process($paymentId, $PayerID);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Payment successful!'));
            } elseif ($status == 'error') {
                $model->setError();
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Payment error!'));
            } else {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Unknown error!'));
            }

            return $this->render('result', [
            ]);
        }

        $url = $model->create(true);
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->render('error');
        }
    }

    public function actionDirectPayRequest($item_id, $status = null, $paymentId = null, $PayerID = null)
    {
        if (empty($item_id)) {
            throw new yii\base\UserException(Yii::t('app', 'No request id!'));
        }

        // Find request
        $request = BaseProformaInvoice::findByNumber($item_id);
        if (empty($request)) {
            throw new yii\base\UserException(Yii::t('app', 'Invalid request number!'));
        }

        ValidateConfig::checkAll($request->customer_id);

        // Check request status
        if ($request->status != BaseProformaInvoice::STATUS_NOT_PAID) {
            if ($request->status == BaseProformaInvoice::STATUS_PAID) {
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Request already paid!'));
            }
            return $this->render('result', []);
        }

        $model = new PayRequestForm();
        $model->invoice = $request;

        // If `status` is set - process payment
        if ($status !== null) {
            if ($status == 'cancel') {
                $model->setCancel();
                Yii::$app->getSession()->setFlash('warning', Yii::t('app', 'Payment was cancelled!'));
            } elseif ($status == 'success') {
                $model->process($paymentId, $PayerID);
                Yii::$app->getSession()->setFlash('success', Yii::t('app', 'Payment successful!'));
            } elseif ($status == 'error') {
                $model->setError();
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Payment error!'));
            } else {
                Yii::$app->getSession()->setFlash('danger', Yii::t('app', 'Unknown error!'));
            }

            return $this->render('result', [
            ]);
        }

        $url = $model->create(true);
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->render('error');
        }
    }
}
