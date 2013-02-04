<?PHP
    // Stick your DBOjbect subclasses in here (to help keep things tidy).

    class User extends DBObject
    {
        public function __construct($id = null)
        {
            parent::__construct('users', array('username', 'password', 'level', 'email'), $id);
        }
    }

	class Score extends DBObject
	{
		function __construct($id = null)
		{
			parent::__construct('score', array('time','updated','ip','count','url','title','domain','goodscore','badscore','score','rank'), $id);
		}
	}
	
	class Counter extends DBObject
	{
		function __construct($id = null)
		{
			parent::__construct('counter', array('count'), $id);
		}
	}