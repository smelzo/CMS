<?php
class Users extends DbDepends {
    const ADMINISTRATOR = 100;
    const POWERUSER = 80;
    const USER = 50;
    const LIGHTUSER = 30;
    
    
    private static $instance = null;
    function __construct(){
        parent::__construct(array('users','user_group','groups'));
    }
    static function check_instance(){
        if(!self::$instance) self::$instance=new Users;
    }
    static function group_get($id){
        self::check_instance();
        if($id && is_numeric($id)) {
            $sql = "SELECT * FROM groups WHERE id=$id";
            $group = db()->querySingle($sql);
            return $group;
        }
        return null;
    }
    static function user_get($id){
        self::check_instance();
        if($id && is_numeric($id)) {
            $sql = "SELECT * FROM users WHERE id=$id";
            $user = db()->querySingle($sql);
            return $user;
        }
        return null;
    }
    static  function user_get_auth ($email,$password){
        self::check_instance();
        $uid = self::user_get_id($email,$password);
        if($uid) {
            return self::user_get($uid);
        }
        return null;
    }
    static function user_get_id($email,$psw,&$user=0){
        self::check_instance();
        if($email && $psw) {
            $sql = "SELECT id FROM " . 'users' . " WHERE password=" . db()->parseValue($psw) .
            " AND email=" . db()->parseValue($email) ;
            $id = db()->queryScalar($sql);
            if($id && $user!==0){
                $user = self::user_get($id);
            }
            return $id;
        }
        return null;
    }
    
    static function eval_power($user,$min){
        return self::user_get_power($user)>=$min;
    }
    
    static function user_get_power($user){
        self::check_instance();
        if(is_numeric($user)) {
            $user = self::user_get($user);
        }
        if($user) {
            $id = array_get($user,'id',0);
            $sql = "
            SELECT MAX([power]) FROM (
                SELECT [power] as [power] FROM
                users WHERE id =$id
                UNION
                SELECT [power] as [power] FROM
                groups G
                INNER JOIN user_group UG ON
                G.id=UG.id_group AND
                UG.id_user=$id
              )Q
            ";
           return  db()->queryScalar($sql);
        }
        return 0;
    }
    /**
     * gets current user
    */
    static function eval_user (){
        self::check_instance();
        if($user=getGlobal('user')) return $user;
        //find id COOKIE
        $uid = array_get($_COOKIE,'UID');
        $user = null;
        if($uid){
            $user = self::user_get($uid);
        }
        setGlobal('user',$user);
        return $user;
    }
    
    static function login($email,$password){
        self::check_instance();
        self::logout();
        if($email && $password){
            $uid = self::user_get_id($email,$password);
            if($uid){
                setcookie('UID',$uid);
                return $uid;
            }
        }
        return 0;
    }
    
    static function logout(){
        self::check_instance();
        setcookie('UID',"",time()-60);
        return 1;
    }
    /**
    * @param Array|JsonString $u
    */
    static function add_user($u){
        self::check_instance();
        if(is_string($u)) $u = json_decode($u,true);
        if(!is_array($u)) return -2; //errore parametri
        if( !Arrays::keys_exists($u,array('password','email','name'))) return -2;
        extract($u);
        if(self::user_get_auth($email,$password)) return  -1;
        $id=0;
        db()->Insert('users',$u,$id);
        return $id;
    }
    /**
    * @param Array|JsonString $g
    */
    static function add_group($g){
        self::check_instance();
        if(is_string($g)) $g = json_decode($g,true);
        if(!is_array($g)) return -2; //errore parametri
        if( !Arrays::keys_exists($g,array('name'))) return -2;
        $id=0;
        db()->Insert('groups',$g,$id);
        return $id;
    }
    /**
    * @param Number $id
    * @param Array|JsonString $data
    */
    static function edit_user($id,$data){
        self::check_instance();
        $u = self::user_get($id);
        if(!$u) return -1;
        if(is_string($data)) $data = json_decode($data,true);
        
        if(!is_array($data)) return -2; //errore parametri
        if(isset($data['id'])) unset($data['id']);
        return db()->update('users',$data,"id=$id");
    }
    /**
    * @param Number $id
    * @param Array|JsonString $data
    */
    static function edit_group($id,$data){
        self::check_instance();
        $g = self::group_get($id);
        if(!$g) return -1;
        if(is_string($data)) $data = json_decode($data,true);
        if(!is_array($data)) return -2; //errore parametri
        if(isset($data['id'])) unset($data['id']);
        return db()->update('groups',$data,"id=$id");
    }
    
    /**
    * @param Number $id
    */
    static function delete_user ($id) {
        self::check_instance();
        $d = 0;
        $d += db()->delete('user_group',"id_user=$id");
        $d += db()->delete('users',"id=$id");
        return $d;
    }
    
    /**
    * @param Number $id
    */
    static function delete_group ($id) {
        self::check_instance();
        $d = 0;
        $d += db()->delete('user_group',"id_group=$id");
        $d += db()->delete('groups',"id=$id");
        return $d;
    }
    
    /**
    * @param Number $id_user
    * @param Number $id_group
    */
    static function add_user_group($id_user,$id_group){
        self::check_instance();
        $u = self::user_get($id_user);
        if(!$u) return -1;
        $sql = "SELECT * FROM user_group WHERE id_user=$id_user AND id_group=$id_group";
        if(db()->queryCount($sql)) return -1;
        return db()->Insert('user_group',array('id_user'=>$id_user,'id_group'=>$id_group));
    }
    
    /**
    * @param Number $id_user
    * @param Number $id_group
    */
    static function remove_user_group($id_user,$id_group){
        self::check_instance();
        return  db()->delete('user_group',"id_group=$id_group AND id_user=$id_user");
    }
    private static function _list($table,$where=null,$order=null){
        self::check_instance();
        $sql = "SELECT * from $table ";
        if($where) {
            $sql .= " WHERE $where";
        }
        if($order) {
            $sql .=  " ORDER BY $order";
        }
        return db()->queryAssoc($sql);
    }
    static function list_users($where=null,$order=null){
        if(!$order) $order = "name ASC";
        return self::_list('users',$where,$order);
    }
    static function list_users_with_groups($where=null,$order=null){
        $users =  self::list_users($where,$order);
        foreach($users as &$user){
            $id_user = array_get($user,'id');
            $user['groups'] = self::list_groups("id IN (select id_group FROM user_group WHERE id_user=$id_user)");
        }
        return $users ;
    }
    static function list_groups($where=null ,$order=null){
        if(!$order) $order = "name ASC";
        return self::_list('groups',$where,$order);
    }
     /**
    * @param Number $id_group
    */
    static function list_users_group($id_group,$order=null){
        if(!$order) $order = "name ASC";
        $where = "id IN (select id_user FROM user_group WHERE id_group=$id_group)";
        return self::_list('users',$where,$order);
    }
    
    
}
?>