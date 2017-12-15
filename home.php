<?php
include('header.php');
include('lib.php');
if(($user = islogin()) == false){
	header('location:index.php');
	}

	//取出自己发的和粉猪自己发的信息
	$r =  connredis();
#	$r->ltrim('recivepost:'.$user['userid'],0,49);
	$star = $r->smembers('following:'.$user['userid']);
	$star[] = $user['userid'];
#3var_dump($star);
	$lastpull =$r->get('lastpull:userid:'.$user['userid']);
	if(!$lastpull){
		$lastpull =0;
		}
#		var_dump($lastpull);
	$latest=array();
	foreach($star as $s){
			$latest = array_merge($latest,$r->zrangebyscore('starpost:userid:'.$s,$lastpull+1,1<<32-1));
		}
	#	var_dump($latest);
		sort($latest,SORT_NUMERIC);
		if(!empty($latest)){
		$r->set('lastpull:userid:'.$user['userid'],end($latest));
		}
#	var_dump($latest);
#	$newpost =$r->sort('recivepost:'.$user['userid'],array('sort'=>'desc','get'=>'post:postid:*:content'));
    //哈希取数据
	foreach($latest as $k){
	#	echo $k;
		$r->lpush('recivepost:'.$user['userid'],$k);
		}
		$r->ltrim('recivepost:'.$user['userid'],0,999);
	$newpost =$r->sort('recivepost:'.$user['userid'],array('sort'=>'desc'));

//计算几个粉丝几个关注
	$myfans = $r->scard('follower:'.$user['userid']);
	$mystar =$r->scard('following:'.$user['userid']);
?>
<div id="navbar">
<a href="index.php">主页</a>
| <a href="timeline.php">热点</a>
| <a href="logout.php">退出</a>
</div>
</div>
<div id="postform">
<form method="POST" action="post.php">
<?php echo $user['username']?>, 有啥感想?
<br>
<table>
<tr><td><textarea cols="70" rows="3" name="status"></textarea></td></tr>
<tr><td align="right"><input type="submit" name="doit" value="Update"></td></tr>
</table>
</form>
<div id="homeinfobox">
<?php echo $myfans?> 粉丝<br>
<?php echo $mystar ?>关注<br>
</div>
</div>
<?php
    foreach($newpost as $postid){
	$p =	$r->hmget('post:postid:'.$postid,array('userid','time','content','username'));
		
#		}
#foreach($newpost as $v){ ?>
<div class="post">
<a class="username" href="profile.php?u=test"><?php echo $p['username']?></a> <?php echo $p['content']?><br>
<i><?php echo formattime($p['time'])?></i>
</div>
<?php } ?>
<?php include('footer.php')?>
