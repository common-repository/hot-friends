=== Hot Friends ===
Contributors: redhorsecxl
Donate link: http://www.thinkagain.cn
Tags: Blogroll, link manager, comment number
Requires at least: 2.3
Tested up to: 2.8.3
Stable tag: 1.4.1

Generate Hot Friends list / cloud by ranking links in blogroll based on its associated comment number or other ways specified by admin.

== Description ==

This plugin is an extension for wordpress blogroll. It brings ability to admin to rank links in blogroll depends on the number of comments which made by link owner. Whose link owners with high rank are considered as "Hot friends".Admin can list the specified number of hot friends list into sidebar via function calling or widget on web page. 

This plugin provides the following functions:
 
1. Generate the so-called Hot Friends list (encapsulated in &lt;li&gt;&lt;/li&gt; tag) via insert `<?php hot_friends();?>` to theme or simply add hot friends widget to sidebar if theme is widgeted.
1. Insert `<?php hot_friends_cloud();?>` to theme to display all links in blogroll in cloud shape, very similar to tag cloud.
1. Ability to automatically add commenter to blogroll when his/her comment number over specified number.

Admin can easily configure major options via the hot friends options page under dashboard.

1. Specify the style (link or avatar) and the number of hot friends to display.
1. Specify the way to rank hot friends, e.g, based on period comment number.
1. Ability to configure whether display comment number and description of each link.
1. Ability to customize the output style of hot friends cloud.
1. Ability to specify the links which always be listed in Hot friends.
1. Ability to query the comment number by commenter's name or url.
1. Import / export links to OPML files.

Latest version is 1.4.1 New [screenshots](http://wordpress.org/extend/plugins/hot-friends/screenshots/ "Screenshots") added. Click [here](http://wordpress.org/extend/plugins/hot-friends/other_notes/ "release log") to see change logs.

Note:

1. Plugin will automatically load language pack according to wordpress language defiend in wp-config.php. Default is english. Available language pack: Chinese, Italian (<a href="http://gidibao.net/">Gianni</a>), RUSSIAN[(www.fatcow.com)](http://www.fatcow.com/ "Fat Cow")..
2. Chinese user please check <a href="http://www.thinkagain.cn/archives/985.html">here</a> for chinese instruction.

Sincerely thanks for advices and support from <a href="http://gidibao.net/">Gianni</a>, <a href="http://www.qiuzhang.org/">Qiuzhang</a>, <a href="http://lxz.name">ddkk3000</a>, <a href="http://www.eemir3.com/">Yacca</a>, <a href="http://photozero.net/">Neekey</a> and <a href="http://www.girlqiqi.cn/">QiQi</a>.



== Installation ==

Installation:

1. Upload `hot-friends` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin with name `Hot Friends` through the 'Plugins' menu in WordPress.
3. Place `<?php hot_friends();?>` in theme templates or invoke the 'Hot Friends' widget to generate Hot Friends List.
4. Place `<?php hot_friends_cloud();?>` in theme templates to display links cloud.

Uninstallation:

Navigate to 'Plugin' menu, and deactivate 'Hot Friends'. All associated data will be automatically deleted.

Note:

1. Plugin will automatically load language pack according to wordpress language defiend in wp-config.php. Default is english. Available language pack: Chinese, Italian (<a href="http://gidibao.net/">Gianni</a>), RUSSIAN[(www.fatcow.com)](http://www.fatcow.com/ "Fat Cow")..
2. Chinese user please check <a href="http://www.thinkagain.cn/archives/985.html">here</a> for chinese instruction.

== Frequently Asked Questions ==

Not yet. Waiting for your feedback.


== Screenshots ==

1. Hot Friends options page. Part-1. To set language option and major options of plugin.
2. Hot Friends options page. Part-2. To customize Hot Friends Avatar and Cloud style.
3. Hot Friends options page. Part-3. To configure automatically add commenter to blogroll and schedule options.
4. Hot Friends options page. Part-4. Management section which currently provides comment number query and import / export links to opml file functions.
5. Hot Friends widget.

== Release History ==
*			2009-08-11: Add russian language file. Thanks [Fat Cow](http://www.fatcow.com/ "Fat Cow").
* [1.4.1]	2008-12-08: Fixed bug to open hot friends link in current / new window according to the configuration of associated link. <a href="https://3284265.cn/">shamas</a>.
* [1.4]		2008-12-06:	Add avatar supports.
* [1.3.2]	2008-10-25:	Changed file name of functions.php to hot_friends_functions.php to avoid conflict with some themes (just in case).
* [1.3.1]	2008-09-26:	Fixed bug to avoid zero division in case of all links have same comment counts.
* [1.3]		2008-09-10:	Add options for customizing output style of hot friends cloud.
						Add options for language pack selection in dashboard.
						Several bugs fixed.
* [1.2.3]	2008-08-27: Add new option that allows admin to specify the link category which commenter will be automatically added into.
* [1.2.2]	2008-08-19:	Italian language pack added. Thanks <a href="http://gidibao.net/">Gianni</a>.
			2008-08-17:	Fixed code to make character correctly displayed in mail subject.
* [1.2.1]	2008-08-16:	Fixed code to exclude admin from automatically adding to blogroll. 
* [1.2]		2008-08-16:	Fixed function bug of automatically add friends. Thanks <a href="http://www.girlqiqi.cn/">QiQi</a>.
* [1.1]		2008-08-16:	Revised import/export links to opml file code. Thanks <a href="http://photozero.net/">Neekey</a>. Tested up to 2.6.1. 
* [1.0]		2008-08-15:	1.0 released.

== Demo ==

* Hot Friends List: <a href="http://www.thinkagain.cn/">http://www.thinkagain.cn/</a>
* Hot Friends Cloud: <a href="http://www.thinkagain.cn/friends">http://www.thinkagain.cn/friends/</a>