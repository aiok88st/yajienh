<?php if (!defined('THINK_PATH')) exit(); /*a:1:{s:67:"/Users/Web/archie/yajienh/public/../app/index/view/game/honbao.html";i:1512807340;}*/ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <title>红包雨</title>
    <script src='http://cdn.bootcss.com/socket.io/1.3.7/socket.io.js'></script>
    <script src='http://cdn.bootcss.com/jquery/1.11.3/jquery.js'></script>
</head>
<body>
    <h1>还剩余红包：<span id="number">100</span></h1>

</body>
<script type="text/javascript">
    // 连接服务端，workerman.net:2120换成实际部署web-msg-sender服务的域名或者ip
    var socket = io('http://127.0.0.1:2120');
    // uid可以是自己网站的用户id，以便针对uid推送以及统计在线人数
    uid = 5250;
    // socket连接后以uid登录
    socket.on('connect', function(){
        socket.emit('login', uid);
    });
    // 后端推送来消息时
    socket.on('new_msg', function(res){
            $('#number').text(res);
    });
    // 后端推送来在线数据时
    socket.on('update_online_count', function(online_stat){
        console.log(online_stat);
    });
</script>
</html>