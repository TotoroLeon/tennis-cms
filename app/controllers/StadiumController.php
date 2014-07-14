<?php
/**
 * =============================
 * 
 * 场馆操作 -控制器
 * 
 * =============================
 */
use \Phalcon\Mvc\Controller;
use \Phalcon\Http\Response;
class StadiumController extends Controller 
{
	public function initialize()
	{
		
	}
	public function indexAction()
	{

	}
	//添加场馆页面
	
	public function addStadiumAction()
	{
		//添加人
		$model = new UserModel();
		$value = $model->getUserInfo($this->session->get('userId'));
		//添加人的ip
		$ip = ip2long($_SERVER["REMOTE_ADDR"]);
		$value['userIp']=$ip;
		$this->view->setVar("userInfo", $value);
		//所有公司
		$company = new CompanyModel();
		$companylist = $company->CompanyListOne();
		$this->view->setVar('companyList',$companylist);
	}
	//添加场馆功能
	public function addStadiumFuncAction()
	{
		$model = new StadiumModel();
		$response = new \Phalcon\Http\Response();
		$time = time().rand(10000,99999);
		$model->belongComId = $this->request->getPost('belongComId');
		$model->addUser = $this->request->getPost('addUser');
		$model->addIp = $this->request->getPost('addIp');
		$model->staName = $this->request->getPost('staName');
		$model->staAddress = $this->request->getPost('staAddress');
		$model->staSize = $this->request->getPost('staSize');
		$model->gpsLong = $this->request->getPost('gpsLong');
		$model->gpsDim = $this->request->getPost('gpsDim');
		$model->addTime = $time;
		if ($model->checkstaName($model->staName) AND $model->checkstaAddress($model->staAddress) AND $model->checkLong($model->gpsLong) AND $model->checkDim($model->gpsDim))
		{
			$model->save();
			if($model->staId)
			{		
			// 保存图片
			if ($this->request->hasFiles() == true) {
	            foreach ($this->request->getUploadedFiles() as $file) {
	            	$array=explode('.', $file->getName());
				$extension=array_pop($array);
				//echo 'images/' . $picUrl.'.'.$extension;die();
                $file->moveTo('images/' . $time.'.'.$extension);
				$image2=new Phalcon\Image\Adapter\GD("images/logo.jpg");
				$picUrl=$time.'.'.$extension;
				$image = new Phalcon\Image\Adapter\GD("images/".$picUrl);
				$image->resize(600, 400)->watermark ($image2,200,320,80)->save();
	            }
	        }
	        else{
	        	$picUrl="";
	        }
        	$picture = new PictureModel();
			//图片数据
			$data=array('addUser'=>$model->addUser,'addIp'=>$model->addIp,'addTime'=>$model->addTime,'stadiumId'=>$model->staId,'isCover'=>'1','picUrl'=>$picUrl);
			$result=$picture->insertPicInfo($data);
			$picture->save();
			$model->staPicture = $picture->picId;
			$check = $model->update();
			if($check)
			{
			$log = new LogModel();
			//操作记录数据
				$log->insertLog($this->session->get('userId'),$content='添加场馆，添加封面');
				$results["statusCode"]="200";
				$results["message"]="操作成功";
				$results["navTabId"]="navStadiumList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="stadium/stadiumList";
				echo json_encode($results);
			}
		}
		else
		{
		    $this->flash->success('false');
		}
		}
		else{
			
			foreach ($model->getMessages() as $message) 
			{
        		//echo $message. "\n";
    		}
				$results["statusCode"]="300";
				$results["message"]='操作失败';
				$results["navTabId"]="navStadiumList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="stadium/stadiumList";
				echo json_encode($results);
		}
		
	}
	//场馆列表
	public function stadiumListAction()
	{
		
		$model=new StadiumModel();
		$companyList=new CompanyModel();
		$stadiumName='';
		$stadiumAddress='';
		$belongComId='';
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
		if($this->request->getPost('search')!='')
		{
			$stadiumName=$this->request->getPost('stadiumName');
			$stadiumAddress=$this->request->getPost('stadiumAddress');
			$belongComId=$this->request->getPost('belongComId');
		}	
		//场馆信息
		$data=$model->getStadiumList($stadiumName,$stadiumAddress,$belongComId);
		$paginator =  new Phalcon\Paginator\Adapter\QueryBuilder(
				array(
						"builder" => $data,
						"limit"=> $numPerPage,
						"page" => $currentPage
				)
		);
		
		$array=$companyList->companyList();
		
		$page = $paginator->getPaginate();
		
		$this->view->setVar('companyList',$array);
		
		$this->view->setVar('page',$page);
		$this->view->setVar('stadiumName',$stadiumName);
		$this->view->setVar('stadiumAddress', $stadiumAddress);		
		$this->view->setVar('belongComId', $belongComId);
		$this->view->setVar('search', 'search');
		$this->view->setVar('numPerPage',$numPerPage);
		//echo '<pre>';var_dump($paginator->item);die();
		
	}
	//场馆修改页面
	public function editStadiumAction()
	{
		$response = new \Phalcon\Http\Response();
		if($this->request->getPost('sub') == '')
		{
			$model=new StadiumModel();
			$staId=$this->request->get('staId');
			$staInfo=$model->findFirst('staId='.'"'.$staId.'"')->toArray();
			//公司信息
			$company=new CompanyModel();
			$companyInfo=$company->CompanyListOne();
			//图片信息
			$picture= new PictureModel();
			$pictureInfo=$picture->find('stadiumId='.'"'.$staId.'"'.'')->toArray();;
			$this->view->setVar('staInfo',$staInfo);
			//echo '<pre>';var_dump($staInfo);die();
			$this->view->setVar('companyInfo',$companyInfo);
			//echo '<pre>';var_dump($companyInfo);
			$this->view->setVar('pictureInfo',$pictureInfo);
			//echo '<pre>';var_dump($pictureInfo);
		}
		else
		{
			$models=new StadiumModel();
			$time=time().rand(10000,99999);
			$models->staId=$this->request->getPost('staId');
			$models->belongComId=$this->request->getPost('belongComId');
			$models->addUser=$this->request->getPost('addUser');
			$models->addIp=$this->request->getPost('addIp');
			$models->staName=$this->request->getPost('staName');
			$models->staPicture=$this->request->getPost('staPicture');
			$models->staAddress=$this->request->getPost('staAddress');
			$models->staSize=$this->request->getPost('staSize');
			$models->gpsLong=$this->request->getPost('gpsLong');
			$models->gpsDim=$this->request->getPost('gpsDim');
			$models->addTime=$time;
			if($models->checkstaAddress($models->staAddress) AND $models->checkLong($models->gpsLong) AND $models->checkDim($models->gpsDim))
			{
				$result=$models->update();
			//echo '<pre>';var_dump($models);
			if($result)
			{
				$log=new LogModel();
				//操作记录数据
					$log->insertLog($this->session->get('userId'),$content='修改场馆信息');
				 	$results["statusCode"]="200";
					$results["message"]="操作成功";
					$results["navTabId"]="navStadiumList";
					$results["rel"]="";
					$results["callbackType"]="closeCurrent";
					$results["forwardUrl"]="stadium/stadiumList";
					echo json_encode($results);
			}
			else
			{
				    foreach ($models->getMessages() as $message){} ;
					$results["statusCode"]="300";
					$results["message"]="$message";
					$results["navTabId"]="navStadiumList";
					$results["rel"]="";
					$results["callbackType"]="closeCurrent";
					$results["forwardUrl"]="stadium/stadiumList";
					echo json_encode($results);
			}
				die();
		}
		}
		
	}
	//场馆信息删除
	public function deleteStadiumAction()
	{
		$model = new StadiumModel();
		$staid = $this->request->getPost('ids');
		if(strpos($staid,',')==true)
		{
			$staidArray=explode(',', $staid);
			foreach ($staidArray as $key=>$value)
			{
				$model->staId = $value;
				//删除场馆
				$checkone = $model->delete();
				//删除场馆图片
				$picture = new PictureModel();
				$array = $picture->find('stadiumId="'.$value.'"')->toArray();
				foreach ($array as $key => $values) {
					$url = 'images/'.$values["picUrl"];
					$resouse = @unlink("$url");
				}
				$picture->stadiumId = $staid;
				$checktwo = $picture->delete();
			}
		}
		else
		{
			$staid = $this->request->get('ids');
			$model->staId = $staid;
			//删除场馆
			$checkone = $model->delete();
			//删除场馆图片
			$picture = new PictureModel();
			$array = $picture->find('stadiumId="'.$staid.'"')->toArray();
			foreach ($array as $key => $values) 
			{
				$url = 'images/'.$values["picUrl"];
				$resouse = @unlink("$url");
			}
			$picture->stadiumId = $staid;
			$checktwo = $picture->delete();
		}		
		if ($checkone AND $checktwo)
		{
			$log=new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='删除场馆');
			echo '{
     			"statusCode":"200",
    			"message":"操作成功",
    			"navTabId":"navStadiumList"
				}';
		}
		else
		{
			echo '{
     			"statusCode":"300",
    			"message":"删除失败",
    			"navTabId":"navStadiumList"
				}';
		}
		die ();
	}

}
