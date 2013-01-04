<?php

class PhpBBFriendsWidget extends DWidget
{
    public $tpl = 'default';
    public $class = 'friends';

    public $user;

    public function run()
    {
        $friends = $this->user->phpBbUser->friends;

        $this->render('PhpBBFriends/' . $this->tpl, array(
            'friends'=>$friends,
            'user'=>$this->user,
            'class'=>$this->class,
        ));
    }
}
