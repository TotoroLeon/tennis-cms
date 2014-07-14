<?php
use \Phalcon\Mvc\Controller;
class TestLoginController extends Controller
{
	// public function initialize(){
		// $userId=$this->session->get('userId');
		// if(empty($userId)){
			// echo 'Please Login';
		// }
	// }
	
	public function UserstateAction()
	{
		//$this->session->set('userId','1');
		/* $userId = '';
		if (empty($userId)){
			echo '{
					"statusCode":"301",
					"message":"\u4f1a\u8bdd\u8d85\u65f6\uff0c\u8bf7\u91cd\u65b0\u767b\u5f55\u3002"					
				}';die();
		} */
	}
	public function loginAction()
	{
		$value = $this->request->getPost('userName');
		if($value)
		{
			echo '登录成功';die();
		}
	}
	public function logincheckAction()
	{
		$this->session->set('userId','1');
		echo '登录成功,请关闭窗口';die();
	}
}
