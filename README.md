Yii PhpBB Integration Kit
=========================

Synchronise Yii users with phpBB3 forum

- [README RUS](http://www.elisdn.ru/blog/32/podklyuchaem-forum-phpbb-k-yii)
- [Extension](http://www.yiiframework.com/extension/phpbb-integration-kit/)

Before usage
------

Turn profile activation in your forum off. 

Add redirects from a forum to the site in `forum/ucp.php`:

~~~
[php]
case 'register':

    header('location: /registration');
    exit();
    break;
    
case 'login':

    header('location: /login');
    exit();
    break;

case 'logout':

    header('location: /logout');
    exit();
    break;
~~~

Rename the class `user` to `bbuser` in the forum file `forum/includes/session.php`:

~~~
[php]
class user extends session
{
    // ...    
    function user()    
    // ...
}
~~~

to

~~~
[php]
class bbuser extends session
{
    // ...    
    function bbuser()    
    // ...
}
~~~

Replace in `forum/common.php`

~~~
[php]
// Instantiate some basic classes
$user		= new user();
~~~

to

~~~
[php]
// Instantiate some basic classes
$user		= new bbuser();
~~~

Remove input fields 'ICQ', 'AVATAR', etc. from forum templates `ucp_profile_profile_info.html` and `ucp_profile_avatar.html`. 

**Note:** If you don't want to modify your forum core files, you can move login/logout/register redirects to .htaccess. But if your application contains class User, you must use namespaces or must rename your own class.

Usage sample
------

Configure `protected/config/main.php`

~~~
[php]
return array(

    'modules'=>array(
        // ...
        'phpbb',
    }

    'components'=>array(
    
        // ...

        'db'=>array(
            'connectionString' => '...',
        ),

        'forumDb'=>array(
            'class'=>'CDbConnection',
            'connectionString' => '...',
            'tablePrefix' => 'phpbb_',
            'charset' => 'utf8',
        ),

        'phpBB'=>array(
            'class'=>'phpbb.extensions.phpBB.phpBB',
            'path'=>'webroot.forum',
        ),        
        
        // Synchronize Login/Logout. See PhpBBWebUser for inheritance details
        'user'=>array(
            'class'=>'phpbb.components.PhpBBWebUser',
            'allowAutoLogin'=>true,
            'loginUrl'=>array('/site/login'),
        ),

        'image'=>array(
            'class'=>'ext.image.CImageHandler',
        ),

        'file'=>array(
            'class'=>'ext.file.CFile',
        ),
    ),
);
~~~

Attach behavior and relation to your User model:

~~~
[php]
class User extends CActiveRecord
{
    public function behaviors()
    {
        return array(
            'PhpBBUserBehavior'=>array(
                'class'=>'phpbb.components.PhpBBUserBehavior',
                'usernameAttribute'=>'username',
                'newPasswordAttribute'=>'new_password',
                'emailAttribute'=>'email',
                'avatarAttribute'=>'avatar',
                'avatarPath'=>'webroot.upload.images.avatars',
                'forumDbConnection'=>'forumDb',
                'syncAttributes'=>array(
                    'site'=>'user_website',
                    'icq'=>'user_icq',
                    'from'=>'user_from',
                    'occ'=>'user_occ',
                    'interests'=>'user_interests',
                )
            ),
        );
    }
    
    public function relations()
    {    
        Yii::import('phpbb.models.*');
        return array(
            'phpBbUser'=>array(self::HAS_ONE, 'PhpBBUser', array('username'=>'username')),
        );
    }
}
~~~

Access to forum userdata:

~~~
[php]
<?php $model = User::model()->findByPk(Yii::app()->user->id); ?>

Private messages: <a href="/forum/ucp.php?i=pm&folder=inbox">New <?php echo $model->phpBbUser->user_unread_privmsg; ?></a>
~~~

Access to forum friends:

~~~
[php]
foreach ($model->phpBbUser->friends as $friend)
{
    echo $friend->user->name . ' ' . $friend->user->lastname . ' ' . $friend->age;
}
~~~

Changelog
------

Version 1.1

\* Fixed avatarPath handling
