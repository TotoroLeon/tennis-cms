<?php
use \Phalcon\Mvc\Model ;
class LogModel extends Model
{
	public $insertBy;
	public $insertTime;
	public $content;
	public $insertIp;
	public $typeId;
	public $modelsManager;
	public function initialize()
    {
        $this->setSource("ten_log");
		$this->modelsManager=$this->getDI()->get('modelsManager');
    }
	public function insertLog($uid='1',$content='',$typeId='')
	{
		$this->insertBy=$uid;
		$this->insertTime=time();
		$this->content=$content;
		$this->insertIp=ip2long('127.0.0.1');
		$this->typeId=$typeId=1;
		LogModel::save();
	}
	public function logList($condition)
	{
		$loglist= $this->modelsManager->createBuilder()
				 ->columns('lid,userName,insertTime,content,insertIp,typeId')
				 ->from("LogModel")
				 ->where("$condition")
				 ->leftjoin('UserModel','LogModel.insertBy=UserModel.userId')
				 ->orderby('lid desc');
		
		return $loglist;
	}
}
