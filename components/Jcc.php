<?php
/**
 * File Jcc.php
 *
 * Based on https://github.com/kongoon/yii2-Jcc
 *
 * @author Marcio Camello <marciocamello@outlook.com>
 * @author Manop Kongoon <kongoon@hotmail.com>
 *
 * @see https://github.com/Jcc/rest-api-sdk-php/blob/master/sample/
 * @see https://developer.Jcc.com/webapps/developer/applications/accounts
 */

namespace app\components;

define('PP_CONFIG_PATH', __DIR__);

use Jcc\Api\Amount;
use Jcc\Api\Item;
use Jcc\Api\ItemList;
use Jcc\Api\Payer;
use Jcc\Api\Payment;
use Jcc\Api\PaymentExecution;
use Jcc\Api\RedirectUrls;
use Jcc\Api\Sale;
use Jcc\Api\Transaction;
use Jcc\Auth\OAuthTokenCredential;
use Jcc\Exception\jccConnectionException;
use Jcc\Rest\ApiContext;
use splynx\helpers\ConfigHelper;
use splynx\models\Customer;
use yii;
use yii\base\Component;
use yii\base\ErrorException;
use yii\helpers\ArrayHelper;
use Jcc\Api\RefundRequest;

class Jcc extends Component
{
    //region Mode (production/development)
    const MODE_SANDBOX = 'https://tjccpg.jccsecure.com/MerchantAdmin/';
    const MODE_LIVE = 'https://jccpg.jccsecure.com/MerchantAdmin/';
    //endregion

    //region Log levels
    /*
     * Logging level can be one of FINE, INFO, WARN or ERROR.
     * Logging is most verbose in the 'FINE' level and decreases as you proceed towards ERROR.
     */
    const LOG_LEVEL_FINE = 'FINE';
    const LOG_LEVEL_INFO = 'INFO';
    const LOG_LEVEL_WARN = 'WARN';
    const LOG_LEVEL_ERROR = 'ERROR';
    //endregion

    //region API settings
    public $clientId;
    public $clientSecret;
	public $clientPassword;
    public $isProduction = false;
    public $currency = 'EUR';
    public $config = [];
    public $customer_id;

    /** @var ApiContext */
    private $_apiContext = null;

    /**
     * @setConfig
     * _apiContext in init() method
     */
    public function init()
    {
        $this->clientId = $this->getClientId();
        $this->clientSecret = $this->getClientSecret();
        $this->isProduction = $this->getClientPaymentMode();
		$this->clientPassword = $this->getClientPassword();
        
    }

    /**
     * @getApiContext
     * getApiContext
     */
    public function getApiContext()
    {
        return $this->_apiContext;
    }

    /**
     * @inheritdoc
     */
    
    public $redirectUrl;
    public $paymentId;

    public function create($amountMoney, $title, $type, $partner, $isDirect = false, $id = null)
    {
        
    }

    public $response;
    /** @var Payment */
    public $result;
    public $error;

    public function execute($paymentId, $payerId)
    {
        // Get the payment Object by passing paymentId
        // payment id was previously stored in session in
        // CreatePaymentUsingjcc.php
        return true;
		/*$payment = Payment::get($paymentId, $this->_apiContext);

        // PaymentExecution object includes information necessary
        // to execute a Jcc account payment.
        // The payer_id is added to the request query parameters
        // when the user is redirected from Jcc back to your site
        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        //Execute the payment
        // (See bootstrap.php for more on `ApiContext`)
        try {
            $this->result = $payment->execute($execution, $this->_apiContext);

            if ($this->result->getState() == 'approved') {
                return true;
            } else {
                return false;
            }

        } catch (jccConnectionException $e) {
            $this->error = $this->getErrorMessage($e->getData());
            return false;
        }*/
    }

    public $refundResult;

    /**
     * Refund payment.
     *
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function refund()
    {       
        return false;
    }
	
    public function get($paymentId)
    {
        return Payment::get($paymentId, $this->_apiContext);
    }

    public function getResponse()
    {
        $this->response = $this->result->toArray();
        return $this->response;
    }

    public function getAmount()
    {
        $transactions = $this->result->getTransactions();
        $transaction = $transactions[0];
        $amount = $transaction->getAmount();
        $total = $amount->getTotal();
        return $total;
    }

    public function getClientId()
    {
        $customer = $this->getCustomer();

        $this->clientId = ConfigHelper::get('clientId', $customer == null ? null : $customer->partner_id);

        return $this->clientId;
    }
		

    public function getClientSecret()
    {
        $customer = $this->getCustomer();

        $this->clientSecret = ConfigHelper::get('clientSecret', $customer == null ? null : $customer->partner_id);

        return $this->clientSecret;
    }
	
	public function getClientPaymentMode()
    {
        $customer = $this->getCustomer();

        $this->isProduction = ConfigHelper::get('isProduction', $customer == null ? null : $customer->partner_id);

        return $this->isProduction;
    }

	public function getClientPassword()
    { 
        $customer = $this->getCustomer();

        $this->clientPassword = ConfigHelper::get('clientPassword', $customer == null ? null : $customer->partner_id);

        return $this->clientPassword;
    }

    public function getCustomer()
    {
        if ($this->customer_id == null) {
            /** @var Customer $customer */
            return Yii::$app->getUser()->getIdentity();
        } else {
            return Customer::findById($this->customer_id);
        }
    }

    /**
     * Return response error message from Jcc
     *
     * @param $string
     * @return string
     */
    public function getErrorMessage($string)
    {
        $response = json_decode($string, true);
        if (isset($response['message'])) {
            return $response['message'];
        } else {
            return 'Unknown error';
        }
    }


}
