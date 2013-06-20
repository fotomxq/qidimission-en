qidimission-en
==============

启迪教育平台英语专用版


介绍
===
该平台为启迪教育平台，英语专用版。主要面向老师群体，您可以在个人或公共网络中部署该项目。
使用了bootstrap界面，QQ词典API、爱词霸API，由于API请求频率限制，在使用“自动添加单词”和“从URL获取单词”功能时可能有较长时间延迟。


安装
===
<p>1、使用XAMPP集成软件配置好PHP5.4.7+Mysql5.5.27运行环境。</p>
<p>2、将所有内容拷贝到网站根目录下。</p>
<p>3、修改includes/install/install.sql文件101内容“(6, 'WEB_URL', 'http://localhost', 'http://localhost'),”。其中“http://localhost”为您的服务器网址。如果只是本机访问，可直接修改为“http://localhost”；如果是内网，则修改为“ http://192.168.x.x ”类似的本机地址即可。注意，末尾不要添加“/”字符。</p>
<p>4、使用phpmyadmin在数据库中建立一个新的数据库，名称为"qidimission"，编码为“utf-8”。</p>
<p>5、在新建的数据库中执行或导入install.sql文件。</p>
<p>6、完成，现在您可以通过浏览器访问了。</p>

* 在glob.php-34行，可关闭debug模式，代码：define('SYS_DEBUG', false);


建议环境
===
* PHP 5.4.7
* Mysql 5.5.27
* IE 7+ / Chrome


默认登录口令
===
<p>默认的登录用户名为“admin”，密码为“adminadmin”。</p>
<p>由于平台处于测试阶段，暂时不开放修改用户名或密码的方法。</p>


更新日志
===
<p>- 2013.6.20</p>
<p>    创建了该项目。</p>

协议和声明
===
<p>该项目遵守<a href="http://www.apache.org/licenses/LICENSE-2.0.html" target="_blank">Apache2.0协议</a>。</p>

<p>词典公共API</p>
<p> 爱词霸：http://dict-co.iciba.com/</p>
<p> QQ词典：http://dict.qq.com</p>

<p>Bootstrap声明</p>
<p> Code licensed under Apache License v2.0。</p>
<p> 详情参阅：https://github.com/twitter/bootstrap#copyright-and-license</p>

<p>Jquery声明引用</p>
<p> jQuery is provided under the MIT license.</p>
<p> JqueryUI引用</p>
<p> 详情请参阅：https://github.com/jquery/jquery-ui</p>