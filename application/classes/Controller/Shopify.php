<?php

class Controller_Shopify extends Controller {

    public $template = 'shopify_template';

	public function action_install()
	{
		$config = Kohana::$config->load('shopify')->get('account');
//var_dump($config);
        //$this->redirect("https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&scope={scopes}&redirect_uri={redirect_uri}&state={nonce}&grant_options[]={option}");

		$apiKey = $config['api_key'];
		$host = $config['host'];
		$shop = $this->request->query('shop');
		if (!Integration_ShopifyHelper::validateShopDomain($shop)) {
			return $this->response->body('Invalid shop domain!');
		}

		$scope = 'read_products,read_script_tags,write_script_tags';
		$redirectUri = $host . '/' . Route::get('default')->uri(array(
				'controller' => 'Shopify',
				'action' => 'oAuthCallback'
			));
		$installUrl = "https://{$shop}/admin/oauth/authorize?client_id={$apiKey}&scope={$scope}&redirect_uri={$redirectUri}";
	//return $this->response->body($installUrl);
		return $this->redirect($installUrl);
	}

	public function action_oAuthCallback()
	{

		$config = Kohana::$config->load('shopify')->get('account');

		$params = $this->request->query();
		$apiKey = $config['api_key'];
		$secret = $config['secret'];
		$validHmac = Integration_ShopifyHelper::validateHmac($params, $secret);
		$validShop = Integration_ShopifyHelper::validateShopDomain($params['shop']);
		$accessToken = "";

		if ($validHmac && $validShop) {
			$accessToken = Integration_ShopifyHelper::getAccessToken($params['shop'], $apiKey, $secret, $params['code']);
		} else {
			return $this->response->body("This request is NOT from Shopify!");
		}

		//get list scriptTags
		$shopifyResponse = Integration_ShopifyHelper::performShopifyRequest(
			$params['shop'],
			$accessToken,
			'script_tags',
			array('script_tag' =>
				      array(
					      'src'       =>  "https://app.convertful.com/Convertful.js"
				      )
			),
			'GET'
		);

		//Delete scriptTags if was installed
		if ($shopifyResponse && count($shopifyResponse['script_tags']) > 0 ) {
			$shopifyResponse = Integration_ShopifyHelper::performShopifyRequest(
				$params['shop'],
				$accessToken,
				'script_tags',
				array('id' => $shopifyResponse['script_tags'][0]['id']),
				'DELETE'
			);
		}


		//insert Convertful.js into scriptTags
		$shopifyResponse = Integration_ShopifyHelper::performShopifyRequest(
			$params['shop'],
			$accessToken,
			'script_tags',
			array('script_tag' =>
				      array(
					      'event'     =>  "onload",
					      'src'       =>  "https://app.convertful.com/Convertful.js"
				      )
			),
			'POST'
		);

		$redirect_shop_url = "https://".$params['shop']."/admin/apps";

		return $this->redirect($redirect_shop_url);

	}



	public function action_test()
	{
		//$this->response->body('hello, TEST action!');
		var_dump($this->request->query());
	}


    public function action_auth()
	{
        $this->request->query(); // GET
        $this->request->post(); // POST

        if ($this->_validate_hmac())
        {
            $token = $this->_get_access_token();
            //save token
            $this->redirect('shopify/index');
        }
	}

    public function action_index()
    {
        echo "Application successful installed";
    }

    /**
     * @return Integration_Response
     */
    private function _get_access_token()
    {
        return Integration_Request::factory()
			->method('POST')
			->url('https://{shop}.myshopify.com/admin/oauth/access_token')
			//->header('X-Shopify-Access-Token', $token)
			->execute();
    }
}
