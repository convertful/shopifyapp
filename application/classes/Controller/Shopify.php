<?php

class Controller_Shopify extends Controller_Template {

    public $template = 'shopify_template';

	public function action_install()
	{
        $config = Kohana::$config->load('shopify')->as_array();
var_dump($config);
        //$this->redirect("https://{shop}.myshopify.com/admin/oauth/authorize?client_id={api_key}&scope={scopes}&redirect_uri={redirect_uri}&state={nonce}&grant_options[]={option}");
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
        echo "Here will be index screen";
    }

    private function _validate_hmac($hmac)
    {
        return TRUE;
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
