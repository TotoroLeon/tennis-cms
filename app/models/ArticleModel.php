<?php

use Phalcon\Mvc\Model;

class ArticleModel  extends Model
{
	public $artId;
	public $artTitle;
	public $artContent;
	public $artAuthor;
	public $artTime;
	public $artType;
	public $artCatagory;
	public $modelsManager;
	public function initialize()
	{
		$this->setSource("ten_article");
		$this->modelsManager=$this->getDI()->get('modelsManager');
	}
	
	public function articleList()
	{
		$article=$article=$this->modelsManager->createBuilder()
				->columns('artId,artTitle,artAuthor,artTime')
				->from('ArticleModel')
				->orderby('artId');
		return $article;
	}
	public function searchArcticle($conditions)
	{
		
		$article=$this->modelsManager->createBuilder()
				->columns('artId,artTitle,artAuthor,artTime')
				->from('ArticleModel')
				->where("$conditions")
				->orderby('artId');
		return $article;
	}	
}

?>