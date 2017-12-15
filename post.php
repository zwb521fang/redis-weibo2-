<?php
include('lib.php');
include('header.php');
if(($user =islogin())== false)
{
   header('location:index.php');
   exit;
}
$content = $_POST['status'];
if(!$content){
	error('请填写内容');
	}

$r = connredis();
$postid =$r->incr('global:postid');
#$r->set('post:postid:'.$postid.':userid',$user['userid']);
#$r->set('post:postid:'.$postid.':time',time());
#$r->set('post:postid:'.$postid.':content',$content);
$r->hmset('post:postid:'.$postid,array('username'=>$user['username'],'userid'=>$user['userid'],'time'=>time(),'content'=>$content));
//维护在一个有序集合
$r->zadd('starpost:userid:'.$user['userid'],$postid,$postid);
#var_dump($dds);
#$r->ltrim('startpost:userid:'.$user['userid'],0,19)
if($r->zcard('starpost:userid'.$user['userid']>20)){
$r->zremrangebyrank('starpost:userid'.$user['userid'],0,0);
}
//吧自己的微博id ，放到一个链表100个，自己看自己的微博
$r->lpush('mypost:userid:'.$user['userid'],$postid);
if($r->llen('mypost:userid:'.$user['userid'])>5){
	$r->rpoplpush('mypost:userid:'.$user['userid'],'global:store');
	}
#var_dump($dd);

#$fans =$r->smembers('follower:'.$user['userid']);
#var_dump($fans);exit;
#$fans[]= $user['userid'];
#foreach($fans as $fansid){
#	$r->lpush('recivepost:'.$fansid,$postid);
#	}
header('location:home.php');
include('footer.php');
