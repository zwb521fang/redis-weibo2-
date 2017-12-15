<?php

include('lib.php');
$r = connredis();
$i=0;
$sql = 'insert into post(postid,userid,username,time,content) values ';
while($r->llen('global:store') && $i++<1000){
	$postid =$r->rpop('global:store');
	$post = $r->hmget('post:postid:'.$postid,array('userid','username','time','content'));
	$sql .= "($postid,".$post['userid'].",'".$post['username']."',".$post['time'].",'".$post['content']."'),";
	
	}
	
	if($i==0)
	{
		echo 'no job';
		exit;
		}
	$sql =substr($sql,0,-1);
    //连接数据库
	@$conn = new mysqli('127.0.0.1','root','','test');
#	mysql_query('user test',$conn);
	$conn->query('set names utf8;');
	$conn->query($sql);
    echo 'ok';

#echo $sql;

