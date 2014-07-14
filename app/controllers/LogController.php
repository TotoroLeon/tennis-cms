<?php
/**
 * =============================
 * 
 * 日志操作 -控制器
 * 
 * =============================
 */
 use Phalcon\Mvc\Controller;
class LogController extends Controller 
{
	public function initialize(){
		/* $userId=$this->session->get('userId');
		if(empty($userId)){
			echo '<script type="text/javascript">window.open("../TestLogin/userState","newwindow","height=100,width=400,top=300,left=500,toolbar=no,menubar=no,scrollbars=no,resizable=no,location=no,status=no");</script>';
			//$this->session->set('userId','1');
			die();
		} */
	}

	public function indexAction()
	{

	}
	public function logListAction()
	{
		$model=new LogModel();
		
		//当前页
		if($this->request->getPost('pageNum'))
		{
			$currentPage=$this->request->getPost('pageNum');
		}
		else{
			$currentPage=1;
		}
		//每页条数
		if($this->request->getPost('numPerPage'))
		{
			$numPerPage=$this->request->getPost('numPerPage');
		}
		else{
			$numPerPage=20;
		}
		$condition='1 = 1 ';
		if($this->request->getPost('startTime'))
		{
			$startTime=strtotime($this->request->getPost('startTime'));
			$condition.="and insertTime >='".$startTime."'";
		}
		if($this->request->getPost('endTime'))
		{
			$endTime=strtotime($this->request->getPost('endTime'));
			$condition.="and insertTime <='".$endTime."'";			
		}
		
		$data = $model->logList($condition);
		$paginator = new Phalcon\Paginator\Adapter\QueryBuilder(array(
				"builder" => $data,
				"limit"=> $numPerPage,
				"page" => $currentPage
		));
		$page=$paginator->getPaginate();
		$this->view->setVar('page',$page);
		$this->view->setVar('numPerPage',$numPerPage);

	}

}
