<?php

class Controller_Welcome extends Controller {

	public function action_index()
	{
		//var_dump($this->request->param());
		$this->response->body('hello, world!!!!');
	}

	public function action_test()
	{
		//$this->response->body('hello, TEST action!');
		echo $this->request->query();
	}

} // End Welcome
