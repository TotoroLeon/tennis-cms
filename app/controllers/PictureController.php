<?php
/**
 * =============================
 * 
 * 图片操作 -控制器
 * 
 * =============================
 */
 use \Phalcon\Mvc\Controller;
class PictureController extends Controller 
{
	public function initialize()
	{

	}

	public function indexAction()
	{

	}
	//添加图片页面
	public function addPictureAction()
	{
		//添加人
		$model = new UserModel();
		$value = $model->getUserInfo(2);
		//添加人的ip
		$ip = ip2long($_SERVER["REMOTE_ADDR"]);
		$value['userIp'] = $ip;
		$this->view->setVar("userInfo", $value);
		//所属场地
		$stadium = new StadiumModel();
		$stadiumList = $stadium->getStadiumNameList();
		$this->view->setVar("stadiumList", $stadiumList);
	}
	//添加图片功能
	public function addPictureFuncAction()
	{
		$addUser = $this->request->get('userId');
		$addIp = $this->request->get('userIp');
		$stadiumId = $this->request->get('stadiumId');
		$isCover = '0';
		$addTime = time().rand(10000,99999);
		$picUrl = $addTime;
		if ($this->request->hasFiles() == true )
		{
            foreach ($this->request->getUploadedFiles() as $file) 
            {
				$array = explode('.', $file->getName());
				$extension = array_pop($array);
				//echo 'images/' . $picUrl.'.'.$extension;die();
                $file->moveTo('images/' . $picUrl.'.'.$extension);
				$image2 = new \Phalcon\Image\Adapter\GD("images/logo.jpg");
				$picUrl = $picUrl.'.'.$extension;
				$image = new \Phalcon\Image\Adapter\GD("images/".$picUrl);
				$image->resize(600, 400)->watermark ($image2,200,320,80)->save();
            }
		 	$picture = new PictureModel();
			//图片数据
			$data = array('addUser'=>$addUser,'addIp'=>$addIp,'addTime'=>$addTime,'stadiumId'=>$stadiumId,'isCover'=>$isCover,'picUrl'=>$picUrl);
			$result = $picture->insertPicInfo($data);
			if ($result)
			{
				//保存操作记录
				$log = new LogModel();
				//操作记录数据
				$log->insertLog($this->session->get('userId'),$content='增加一张图片');
				$results["statusCode"]="200";
				$results["message"]="操作成功";
				$results["navTabId"]="navPictureList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="picture/pictureList";				
				echo json_encode($results);
			}
			else
			{
				$results["statusCode"]="300";
				$results["message"]="操作失败";
				$results["navTabId"]="navPictureList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="picture/pictureList";
				echo json_encode($results);
			}
		}
		else
		{
			$result["statusCode"]="300";
			$result["message"]="请上传图片";
			echo json_encode($result);
		}
	}
	//图片列表
	public function pictureListAction()
	{
		$model = new PictureModel();
		$stadium = new StadiumModel();
		$stadiumName = '';
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
		if ($this->request->getPost('stadiumName')!='')
		{
			$stadiumName = $this->request->getPost('stadiumName');
			$this->view->setVar('stadiumName', $stadiumName);
		}
		
		$value = $model->getAllPicInfo($stadiumName);
		$paginator =  new Phalcon\Paginator\Adapter\QueryBuilder(
				array(
						"builder" => $value,
						"limit"=> $numPerPage,
						"page" => $currentPage
				)
		);
		$page=$paginator->getPaginate();
		$stadiumList = $stadium->getStadiumNameList();
		$jsonData = json_encode($value);
		$this->view->setVar('stadiumList',$stadiumList);
		$this->view->setVar('page',$page);
		//条/页
		$this->view->setVar('numPerPage',$numPerPage);
		
	}
	//图片信息修改页面
	public function editPictureAction()
	{
		if (!$this->request->getPost('sub'))
		{
			$picId = $this->request->get('picId');
			$stadium = new StadiumModel();
			$stadiumList = $stadium->getStadiumNameList();
			$this->view->setVar('stadiumList',$stadiumList);
			$picture = new PictureModel();
			$pictureInfo = $picture->findFirst('picId='.'"'.$picId.'"')->toArray();
			$this->view->setVar('pictureInfo',$pictureInfo);
		}
	}
	//图片信息修改功能
	public function editPicInfoFuncAction()
	{
			$picture = new PictureModel();
			$picture->stadiumId = $this->request->getPost('stadiumId');
			//$picture->userId = $this->request->getPost('userId');
			//$picture->userIp = $this->request->getPost('userIp');
			$picUrl = time().rand(10000,99999);
			//var_dump($this->request->hasFiles());die();
			if ($this->request->hasFiles() == true  ) 
			{
				
            	foreach ($this->request->getUploadedFiles() as $file) 
            	{
	                $array = explode('.', $file->getName());
					$extension = array_pop($array);
					//echo 'images/' . $picUrl.'.'.$extension;die();
                	$file->moveTo('images/' . $picUrl.'.'.$extension);
					$image2 = new \Phalcon\Image\Adapter\GD("images/logo.jpg");
					$picUrl = $picUrl.'.'.$extension;
					$image = new \Phalcon\Image\Adapter\GD("images/".$picUrl);
					$image->resize(600, 400)->watermark ($image2,200,320,80)->save();
	            }
				//删除原图
				$oldpicurl = $this->request->getPost('oldpicUrl');
					if ($oldpicurl!='')
					{
						$url = 'images/'.$oldpicurl;
						@unlink("$url");
					}
				$picture->picUrl=$picUrl;
			}
			else
			{
				$picture->picUrl = $this->request->getPost('oldpicUrl');
			}
			$picture->picId = $this->request->getPost('picId');
			//var_dump($picture->picUrl);die();
			$res = $picture->update();
			if ($res)
			{
				$log = new LogModel();
				//操作记录数据
				$log->insertLog($this->session->get('userId'),$content='修改图片信息');
				$results["statusCode"]="200";
				$results["message"]="操作成功";
				$results["navTabId"]="navPictureList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="picture/pictureList";
				echo json_encode($results);
			}
			else
			{
				$results["statusCode"]="300";
				$results["message"]="修改失败";
				$results["navTabId"]="navPictureList";
				$results["rel"]="";
				$results["callbackType"]="closeCurrent";
				$results["forwardUrl"]="picture/pictureList";
				echo json_encode($results);
			}	
			die();
		
	}
	//图片删除功能
	public function deletePictureAction()
	{
		$model = new PictureModel();
		$picId = $this->request->get('ids');
		if(strpos($picId, '1')==true){
			$array=explode(',', $picId);
			foreach ($array as $key=>$value){
				$model->picId=$value;
				//删除图片资源
				$arrays = $model->findFirst('picId="'.$value.'"')->toArray();
				$url = 'images/'.$arrays["picUrl"];
				$resouse = @unlink("$url");
				//删除图片信息
				$res = $model->delete();
			}
		}
		else{
			//$picId = $this->request->getPost('ids');
			$model->picId=$picId;
			//删除图片资源
			$array = $model->findFirst('picId="'.$picId.'"')->toArray();
			$url = 'images/'.$array["picUrl"];
			$resouse = @unlink("$url");
			//删除图片信息
			$res = $model->delete();
		}
		if ($resouse AND $res)
		{
			$log = new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='删除一张图片');
			$result["statusCode"]="200";
			$result["message"]="操作成功";
			$result["navTabId"]="navPictureList";
			echo json_encode($result);
		}
		else
		{
			$log = new LogModel();
			//操作记录数据
			$log->insertLog($this->session->get('userId'),$content='删除一张图片');
			$result["statusCode"]="200";
			$result["message"]="图片资源不存在，删除本条数据";
			$result["navTabId"]="navPictureList";
			echo json_encode($result);
		}
		die();
	}
}
