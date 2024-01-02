<?php

namespace app\models;

use app\components\Jcc;
use base\Model;
use splynx\models\customer\BaseCustomer;
use splynx\models\finance\BaseInvoice;
use splynx\models\finance\BaseProformaInvoice;
use Yii;
use yii\helpers\Html;

/**
 * Trait BaseJccPaymentTrait.
 * @package app\models
 */
trait BaseJccPaymentTrait
{
    // Jcc execute error
    public $errorMessage;

    /**
     * @inheritdoc
     */
    public function getAddonTitle()
    {
        return "Jcc";
    }

    /**
     * @param bool $isDirect
     * @return bool|string Url redirect
     */
    public function create($isDirect = false)
    {

        $Jcc = new Jcc(['customer_id' => $this->customer_id]);

        // Create bank statement
        $bankStatement = $this->createBankStatement();

        if ($this instanceof PayInvoiceForm) {
            $paymentType = 'invoice';
            $paymentIdForTitle = $this->invoice->number;
            $invoiceNumber = $paymentIdForTitle;
        } elseif ($this instanceof PayRequestForm) {
            $paymentType = 'request';
            $paymentIdForTitle = $this->invoice->number;
            $invoiceNumber = $paymentIdForTitle;
        } else {
            $paymentType = 'payment';
            $paymentIdForTitle = $this->bankStatement->id;
            $invoiceNumber = null;
        }


        $title = ucfirst($paymentType) . ' #' . $paymentIdForTitle;

        $partner = (new BaseCustomer())->findById($this->customer_id)->partner_id;


        if ($Jcc->create($this->getTotalAmount(), $title, $paymentType, $partner, $isDirect, $invoiceNumber)) {
            // Save payment id to session
            Yii::$app->session->set(self::SESSION_PAYMENT_ID, $Jcc->paymentId);
            Yii::$app->session->set(self::BANK_STATEMENT_ID, $bankStatement->id);

            // Return Jcc payment url
            return $Jcc->redirectUrl;
        } else {
            return false;
        }
    }

    /**
     * @param $paymentId
     * @param $PayerID
     * @return bool
     */
    public function process($paymentId, $PayerID)
    {
        $savedPaymentId = Yii::$app->session->get(self::SESSION_PAYMENT_ID);

        if ($this instanceof PayRequestForm) {
            $checkInvoice = $this->invoice !== null and $this->invoice->status == BaseProformaInvoice::STATUS_NOT_PAID;
        } elseif ($this instanceof PayInvoiceForm) {
            $checkInvoice = $this->invoice !== null and $this->invoice->status == BaseInvoice::STATUS_NOT_PAID;
        } else {
            $checkInvoice = true;
        }
		
		$api_key = 'cfcdfda3f449ac3b4adbf433347fd793';
$api_secret = '7ebaac90f70642039c75bcaa72c0b46e';

$nonce = round(microtime(true) * 100);

$signature = strtoupper(hash_hmac('sha256', $nonce . $api_key, $api_secret));

$auth_data = array(
    'key' => $api_key,
    'signature' => $signature,
    'nonce' => $nonce++
);

$auth_string = http_build_query($auth_data);

$header = 'Authorization: Splynx-EA (' . $auth_string . ')';
$api_domain='https://billing.ghofi.com.cy/api/1.0/admin/finance/invoices/6494';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_domain);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, $header);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, array('status' => 'Paid'));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json"
));

$response = curl_exec($ch);
curl_close($ch);

var_dump($response);
exit;

        if ($paymentId == $savedPaymentId and $checkInvoice) {
            $bankStatementId = Yii::$app->session->get(self::BANK_STATEMENT_ID);

            $Jcc = new Jcc(['customer_id' => $this->customer_id]);
            if ($Jcc->execute($paymentId, $PayerID)) {
                if ($this instanceof PaymentForm) {
                    // Set amounts without fee
                    $this->setAmountWithOutFee($Jcc->getAmount());
                }

                if (!$this->processPayment($paymentId, $bankStatementId)) {
                    $this->errorMessage = Html::errorSummary([$this]);
                    if ($Jcc->refund()) {
                        $this->errorMessage .= '<br>Payment was refunded!';
                    }
                    return false;
                }
                return true;
            } else {
                $this->setError($bankStatementId);
                $this->errorMessage = $this->getError($Jcc->error);
            }
        }

        return false;
    }


    /**
     * @param null|int $bankStatement_id
     */
    public function setCancel($bankStatement_id = null)
    {
        $bankStatement_id = $bankStatement_id ? $bankStatement_id : Yii::$app->session->get(self::BANK_STATEMENT_ID);
        $this->cancel($bankStatement_id);
    }


    /**
     * @param null|int $bankStatement_id
     */
    public function setError($bankStatement_id = null)
    {
        $bankStatement_id = $bankStatement_id ? $bankStatement_id : Yii::$app->session->get(self::BANK_STATEMENT_ID);
        $this->error($bankStatement_id);
    }

    /**
     * @param $error
     * @return string
     */
    private function getError($error)
    {
        return $error . ' Please, contact support!';
    }
}
