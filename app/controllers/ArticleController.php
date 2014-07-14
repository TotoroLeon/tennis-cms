<?php
/**
 * =============================
 *
 * 文章操作 -控制器
 *
 * =============================
 */
use Phalcon\Mvc\Controller;

class ArticleController  extends Controller
{
	/**
	 * 文章列表
	 */
	public function articleListAction()
	{
		$articlemodel=new ArticleModel();
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
		if($this->request->getPost())
		{
			$conditions='';
			$conditions.='1 = 1';
			if($this->request->getPost('artTitle')!='')
			{
				$conditions.=" and  artTitle  LIKE '%".$this->request->getPost('artTitle')."%' ";
				$this->view->setVar('artTitle', $this->request->getPost('artTitle'));
			}
			if($this->request->getPost('artAuthor')!='')
			{
				$conditions.=" and  artAuthor  LIKE '%".$this->request->getPost('artAuthor')."%' ";
				$this->view->setVar('artAuthor', $this->request->getPost('artAuthor'));
			}
			if($this->request->getPost('startDate')!=''){
				$conditions.=" and artTime >= '".$this->request->getPost('startDate')."'";
				$this->view->setVar('startDate', $this->request->getPost('startDate'));
			}
			if($this->request->getPost('endDate')!=''){
				$conditions.=" and artTime <= '".$this->request->getPost('endDate')."'";
				$this->view->setVar('endDate', $this->request->getPost('endDate'));
				
			}
			$values = $articlemodel->searchArcticle($conditions);
				//var_dump($this->request->getPost());die();
		}
		else
		{
			$values=$articlemodel->articleList();
		}
		$paginator =  new Phalcon\Paginator\Adapter\QueryBuilder(
				array(
						"builder" => $values,
						"limit"=> $numPerPage,
						"page" => $currentPage
				)
		);
		$page=$paginator->getPaginate();
		$this->view->setVar('page', $page);
		$this->view->setVar('numPerPage', $numPerPage);		
		
	}
	/**
	 * 添加文章
	 */
	public function addArticleAction()
	{
		
	}
	/**
	 * 删除文章
	 */
	public function deleteArticleAction()
	{
		$idArray=$this->request->getPost('ids');
		$model=new ArticleModel();
		if(strpos($idArray, ',')==true)
		{
			$array=explode(',', $idArray);
			foreach ($array as $key=>$value)
			{
				$model->artId=$value;
				$result=$model->delete();
			}
		}
		else
		{
			$model->artId=$this->request->get('ids');
			$result=$model->delete();
		}
		if($result==true)
		{
			$log = new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='删除文章');
			$results["statusCode"]="200";
			$results["message"]="操作成功";
			$results["navTabId"]="navArticleList";
			echo json_encode($results);
		}
	}
	/**
	 * 修改文章
	 */
	public function editArticleAction()
	{
		$artId=$this->request->get('artId');
		$model=new ArticleModel();
		$value=$model->findFirst('artId='.'"'.$artId.'"')->toArray();
		$this->view->setVar('articleInfo', $value);
	}
	/**
	 * 保存文章
	 */
	public function saveArticleAction()
	{
		$model=new ArticleModel();
		$model->artTitle=$this->request->getPost('artTitle');
		$model->artContent=$this->request->getPost('artContent');
		//$model->artAuthor=$this->session->get('userId');
		$model->artTime=$this->request->getPost('artTime');
		if($this->request->get("artId"))
		{
			$model->artId=$this->request->get("artId");
			$ress=$model->update();
			if($ress==true)
			{
				$log=new LogModel();
				//操作记录数据
					$log->insertLog('1',$content='修改文章信息');
				 	$results["statusCode"]="200";
					$results["message"]="操作成功";
					$results["navTabId"]="navArticleList";
					$results["rel"]="";
					$results["callbackType"]="closeCurrent";
					$results["forwardUrl"]="article/articleList";
					echo json_encode($results);
			}
			else
			{
					$results["statusCode"]="300";
					$results["message"]="操作失败";
					$results["navTabId"]="navArticleList";
					$results["rel"]="";
					$results["callbackType"]="closeCurrent";
					$results["forwardUrl"]="article/articleList";
				echo json_encode($results);
			}
			die();
		}
		else
		{
			$res=$model->save();
		//var_dump($this->request->getPost());
		if($res==true)
		{
			$log = new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='添加文章');
			$results["statusCode"]="200";
			$results["message"]="操作成功";
			$results["navTabId"]="navAddArticle";
			echo json_encode($results);
		}
		else
		{
			$results["statusCode"]="300";
			$results["message"]="操作失败";
			echo json_encode($results);
		}
		die();
		}
	}
}

?>