<?php
/**
 * =============================
 * 
 * 公司操作 -控制器
 * 
 * =============================
 */
 use Phalcon\Mvc\Controller;
 use Phalcon\Paginator\Adapter\Model;
class CompanyController extends Controller 
{
	public function initialize()
	{
		$check=new TestLoginController();
		$check->UserstateAction();
		
	}

	public function indexAction()
	{

	}
	//添加公司页面
	public function addCompanyAction()
	{
		
	}
	//添加公司功能
	public function addCompanyFuncAction()
	{
		$companyName = $this->request->getPost('companyName');
		$model = new CompanyModel();
		$model->companyName = $companyName;
		$res = $model->insertCompany();
		if($res == 'true')
		{
			$log = new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='添加一个公司名称');
			$result["statusCode"]="200";
			$result["message"]="操作成功";
			$result["navTabId"]="navCompanyList";
			$result["rel"]="";
			$result["callbackType"]="closeCurrent";
			$result["forwardUrl"]="company/companyList";
			echo json_encode($result);
		}
		else
		{
			echo $res;
		}
	}
	//公司列表
	public function companyListAction()
	{
		
		$model = new CompanyModel();
		//当前页
		if($this->request->getPost('pageNum')){
			$currentPage=$this->request->getPost('pageNum');
		}
		else{
			$currentPage=1;
		}		
		//每页条数
		if($this->request->getPost('numPerPage')){
			$numPerPage=$this->request->getPost('numPerPage');
		}
		else{
			$numPerPage=20;
		}
		
		if(!empty($this->request->getPost('companyName')))
		{
			$value=$this->request->getPost('companyName');
			$data = $model->find(array("conditions" => "companyName LIKE '%".$value."%' "));
			$this->view->setVar('companyName',$value);
		}
		else
		{
			$data = $model->find();
		}
		//分页
		$paginator = new \Phalcon\Paginator\Adapter\Model(
				array(
						"data" => $data,
						"limit"=> $numPerPage,
						"page" => $currentPage
				)
		);
		//var_dump($this->request->getPost());die();
		$page = $paginator->getPaginate();
		$this->view->setVar('page',$page);
		$this->view->setVar('numPerPage',$numPerPage);
	}
	//公司名称修改
	public function editCompanyAction()
	{
		$model = new CompanyModel();
		
			$companyId=$this->request->get('companyId');
			if($this->request->get('companyId'))
			{
				//$model->companyId=$companyId;
				$result=$model->findFirst(array('conditions'=>'companyId='.$companyId.''))->toArray();
			}
			$this->view->setVar('companyInfo',$result);
		
				
	}
	public function editCompanyFuncAction()
	{
			$model = new CompanyModel();
			$model->companyId=$this->request->getPost('companyId');
			$model->companyName=$this->request->getPost('companyName');
			
			$result=$model->update();
			if($result){
				echo '{
     			"statusCode":"200",
    			"message":"操作成功",
    			"navTabId":"navCompanyList",
    			"callbackType":"closeCurrent",
				"forwardUrl":"company/companyList"
				}';
			}
			else{
				echo '{
     			"statusCode":"300",
    			"message":"修改失败",
    			"navTabId":"navCompanyList",
    			"callbackType":"closeCurrent",
    			"forwardUrl":"company/companyList"
				}';
			}
			die();
		
	
	}
	//公司信息删除
	public function deleteCompanyAction()
	{
		$model=new CompanyModel();
		$ids=$this->request->get('ids');
		if(strpos($ids,',')==true)
		{
			$array=explode(',', $ids);
			foreach ($array as $key=>$value)
			{
				$model->companyId=$value;
				$result=$model->delete();
			}
		}
		else
		{
			$model->companyId=$ids;
			$result=$model->delete();
		}
		if ($result)
		{
			$log=new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='删除一个公司信息');
			echo '{
     "statusCode":"200",
    "message":"操作成功",
    "navTabId":"navCompanyList"
}';
		}
		else
		{
			echo '0';
		}
		die();
	}

}
