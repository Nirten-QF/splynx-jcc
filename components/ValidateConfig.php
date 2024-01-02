<?php

namespace app\components;

use splynx\helpers\ConfigHelper;
use splynx\helpers\ApiHelper;
use yii;
use yii\base\Component;
use yii\base\UserException;

class ValidateConfig extends Component
{ 
    /**
     * @param null|int $id_customer
     * @throws UserException
     * @throws yii\base\InvalidConfigException
     */
    public static function checkAll($id_customer = null)
    {
        self::checkjcc($id_customer);
        self::checkApi();
    }

    /**
     * @return Jcc
     */
    public static function getjcc()
    {
        return Yii::$app->jcc;
    }

    /**
     * @param null|int $id_customer
     * @throws UserException
     */
    public static function checkjcc($id_customer = null)
    {
        $params = Yii::$app->params;

        $customer = static::getjcc()->getCustomer();

        $partnerId = $customer == null ? null : $customer->partner_id;


        $clientId = static::getjcc()->getClientId();
        if (!isset($clientId) or $clientId == '') {
            throw new UserException('Error: clientId is not set. Please check your params.php');
        }

        $clientSecret = static::getjcc()->getClientSecret();
        if (!isset($clientSecret) or $clientSecret == '') {
            throw new UserException('Error: clientSecret is not set. Please check your params.php');
        }
		$clientPassword = static::getjcc()->getClientPassword();
        if (!isset($clientPassword) or $clientPassword == '') {
            throw new UserException('Error: clientPassword is not set. Please check your params.php');
        }

        
        if (!isset($params['splynx_url']) or $params['splynx_url'] == '') {
            throw new UserException('Error: splynx_url is not set. Please check your params.php');
        }

        if (strpos($params['splynx_url'], 'http://') !== 0 and strpos($params['splynx_url'], 'https://') !== 0) {
            throw new UserException('Error: splynx_url must start with `http://` or `https://`. Please check your params.php');
        }

        if (substr($params['splynx_url'], -1) == '/') {
            throw new UserException('Error: splynx_url must be without last slash. Please check your params.php');
        }
    }

    /**
     * @throws UserException
     * @throws yii\base\InvalidConfigException
     */
    public static function checkApi()
    {
        $params = Yii::$app->params;
        if (!isset($params['api_key']) or $params['api_key'] == '') {
            throw new UserException('Error: api_key is not set. Please check your params.php');
        }

        if (!isset($params['api_secret']) or $params['api_secret'] == '') {
            throw new UserException('Error: api_secret is not set. Please check your params.php');
        }
		
		if (!isset($params['api_domain']) or $params['api_domain'] == '') {
            throw new UserException('Error: api_domain is not set. Please check your params.php');
        }

        if (strpos($params['api_domain'], 'http://') !== 0 and strpos($params['api_domain'], 'https://') !== 0) {
            throw new UserException('Error: api_domain must start with `http://` or `https://`. Please check your params.php');
        }

        if (substr($params['api_domain'], -1) != '/') {
            throw new UserException('Error: api_domain must be with last slash. Please check your params.php');
        }

        $result = ApiHelper::getInstance()->search('admin/customers/customer/', ['limit' => 1]);
        if ($result['result'] == false or empty($result['response'])) {
            throw new UserException('Error: Api call error. Please check your param.php');
        }
    }
}
