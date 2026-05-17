    <?php defined('SYSPATH') or die('No direct script access.');
	
	/** 
	*11.02.2025 Добавлено ограничение на длину названия устройства до 50 символов. Все, что более удаляется.
	*@16.12.2024 задача добавляет в базу данных СКУД приборы BAS-IP.
	*@input
	*@ip
	*@devName
	*@login
	*@password
	*@в таблицу device добавляется контроллер Bas-ip и одна точка прохода.
	
	c:\xampp\php\php.exe c:\xampp\htdocs\bas\modules\minion\minion --task=basIpAddDevice --ip=172.16.21.253 --devName="test 22" --id_ts=2 --login=admin --pass=2644256
	*/
 
    class Task_basIpAddDevice extends Minion_Task {
		
		 protected $_options = array(
        // param name => default value
        'ip'   => '172.16.21.253',
       // 'devName'   => 'Главный вход Н01 (ВП1.1)',
        'devName'   => 'test',
        'id_ts'   => '2',
        'login'   => 'admin',
        'pass'   => '171120',
        'log_prefix'   => 'basIpAddDevice',
		
		);
	
        
        protected function _execute(array $params)
        {
				//Minion_CLI::write('Hello World!');
			//$dateFrom=Date::formatted_time(\''.Arr::get($params, 'delay', 30).' minutes ago');
			
			$dateFrom=Date::formatted_time(Arr::get($params, 'delay', 30).' minutes ago');
			$dateTo=Date::formatted_time();
			//$dateTo=date("Y-m-d H:i:s");		
			
			$log = Log::instance();
					
			$log->add(log::INFO, 'Добавление приборов bas-ip в базу данных СКУД.');	
			
//			$log->add(log::INFO, Debug::vars($params));	
	//echo  Debug::vars('40', $params); exit;	
	
	   $ip=Arr::get($params, 'ip');
	   $devName=Arr::get($params, 'devName');
	   $id_ts=Arr::get($params, 'id_ts');
	   $login=Arr::get($params, 'login');
	   $pass=Arr::get($params, 'pass');
	   $log_prefix=Arr::get($params, 'log_prefix');
      
			
		$pattern='/^[a-zA-Zа-яА-ЯЁё0-9\s№* ().-_]+$/';
			$ddl= Validation::factory($params)
				->rule('ip', 'ip') 
				->rule('login', 'not_empty')
				->rule('login', 'regex', array(':value', $pattern))
				->rule('pass', 'not_empty')
				->rule('pass', 'regex', array(':value', $pattern))
				->rule('ip', array($this,'checkIP'))
				->rule('devName', array($this,'checkDevName'))
				; 

			//echo Debug::vars('200', get_class_methods($ddl), !$ddl->check()); exit;
			Log::instance()->add(log::INFO, Arr::get($this->_options, 'log_prefix').' 188 Старт добавление устройства "'. iconv('CP1251','UTF-8', Arr::get($ddl, 'devName')).'" IP='.Arr::get($ddl, 'ip')); 
			if(!$ddl->check()){
				
				Log::instance()->add(log::INFO, Arr::get($this->_options, 'log_prefix').' 204 Устройство "'. iconv('CP1251','UTF-8', Arr::get($ddl, 'devName')).'" не может быть добавлено: данные не прошли валидацию.'. Debug::vars($ddl->errors()));
				echo 'Validation error. Detail in log';
				exit;
			}			
		//echo  Debug::vars('72 Yes!!!'); exit;
				
			//echo Debug::vars('63 validation ok!'); exit;	
			//получаю id_dev для контроллера
			
			$sql='select GEN_ID(gen_dev_id,1)  FROM RDB$DATABASE';
			$id_dev = DB::query(Database::SELECT, DB::expr($sql))
				->execute(Database::instance('fb'))
				->get('GEN_ID');
				
				
			//получаю id_dev для точки прохода
			
			$sql='select GEN_ID(gen_dev_id,1)  FROM RDB$DATABASE';
			$id_door = DB::query(Database::SELECT, DB::expr($sql))
				->execute(Database::instance('fb'))
				->get('GEN_ID');
				
				
			//получаю id_ctrl для устройства
			
			$sql='select distinct d.id_ctrl from device d';
			$query = DB::query(Database::SELECT, DB::expr($sql))
				->execute(Database::instance('fb'))
				->as_array();
				
			foreach($query as $key=>$value)
			{
				
				$ctrl[]=Arr::get($value, 'ID_CTRL');
			}
			
			$id_ctrl=0;
			for ($i=1; $i<1000; $i++)
			{
				if(!in_array($i, $ctrl)) {
					$id_ctrl=$i;
					break;
				}
			}
			//echo Debug::vars('81', $ctrl, $id_ctrl); exit;
			//добавлю контроллер
			
			$sql='INSERT INTO DEVICE (ID_DEV,ID_DB,ID_SERVER,ID_DEVTYPE,ID_CTRL,ID_READER,NETADDR,NAME,"VERSION",INTERVAL,DSS1,DSS2,FLAG,ID_PLAN,POS_X,POS_Y,PSW,"ACTIVE",CONFIG,PARAM,TAGNAME,ID_GUIDE,ID_OBJECT,ID_PARENT) 
			VALUES ('.$id_dev.',1,'.$id_ts.',1,'.$id_ctrl.',NULL,NULL,\''.Text::limit_chars($devName, 50).'\',1,10,NULL,NULL,0,NULL,0,0,NULL,1,NULL,NULL,NULL,NULL,NULL,1)';
			try {
					//$query = DB::query(Database::INSERT,  iconv('UTF-8', 'CP1251', $sql))
					$query = DB::query(Database::INSERT,  $sql)
						->execute(Database::instance('fb'));
						$result=0;
							
				} catch (Exception $e) {
					
					
					$log->add(log::INFO, $log_prefix.' 101 '.$e->getMessage());	
					//return;
					
			
				}	
				
			//добавлю точки прохода (двери)
			
			$sql='INSERT INTO DEVICE (ID_DEV,ID_DB,ID_SERVER,ID_DEVTYPE,ID_CTRL,ID_READER,NETADDR,NAME,"VERSION",INTERVAL,DSS1,DSS2,FLAG,ID_PLAN,POS_X,POS_Y,PSW,"ACTIVE",CONFIG,PARAM,TAGNAME,ID_GUIDE,ID_OBJECT,ID_PARENT) 
			VALUES ('.$id_door.',1,'.$id_ts.',1,'.$id_ctrl.',0,NULL,\'Дверь № '.$id_ctrl.'-0\',1,10,NULL,NULL,0,NULL,0,0,NULL,1,NULL,NULL,NULL,NULL,NULL,1)';
			try {
					$query = DB::query(Database::INSERT,  iconv('UTF-8', 'CP1251', $sql))
						->execute(Database::instance('fb'));
						$result=0;
							
				} catch (Exception $e) {
					
					
					$log->add(log::INFO, $log_prefix.' 118 '.$e->getMessage());	
					//return;
			
				}	
				
			
			//заполняю таблицу bas-ip с парамерами.
			// ip адрес надо преобразовать с INT
			$sql='INSERT INTO BAS_PARAM (ID_DEV,PARAM,INTVALUE,STRVALUE,INSERTTIME) 
				VALUES ('.$id_dev.',\'IP\','.ip2long($ip).',NULL,\'now\')';	
			try {
					$query = DB::query(Database::INSERT,  iconv('UTF-8', 'CP1251', $sql))
						->execute(Database::instance('fb'));
						$result=0;
							
				} catch (Exception $e) {
					
					
					$log->add(log::INFO, $log_prefix.' 136 '.$e->getMessage());	
					//return;
			
				}					
				
		
			$sql='INSERT INTO BAS_PARAM (ID_DEV,PARAM,INTVALUE,STRVALUE,INSERTTIME) 
				VALUES ('.$id_dev.',\'LOGIN\',NULL,\''.$login.'\', \'now\')';	
			try {
					$query = DB::query(Database::INSERT,  iconv('UTF-8', 'CP1251', $sql))
						->execute(Database::instance('fb'));
						$result=0;
							
				} catch (Exception $e) {
					
					
					$log->add(log::INFO, $log_prefix.' 152 '.$e->getMessage());	
					//return;
			
				}	
				
		
			$sql='INSERT INTO BAS_PARAM (ID_DEV,PARAM,INTVALUE,STRVALUE,INSERTTIME) 
				VALUES ('.$id_dev.',\'PASS\',NULL,\''.$pass.'\', \'now\')';	
			try {
					$query = DB::query(Database::INSERT,  iconv('UTF-8', 'CP1251', $sql))
						->execute(Database::instance('fb'));
						$result=0;
							
				} catch (Exception $e) {
					
					
					$log->add(log::INFO, $log_prefix.' 168 '.$e->getMessage());	
					//return;
				}
			//если дошел сюда, то все вставлено успешно.
		$log->add(log::INFO, $log_prefix.' 165 Устройство '.$devName.' добавленно успешно. id_ctrl='.$id_ctrl.',  id_dev='.$id_dev);	
		echo 'Add device is OK';
				exit;
        }
		
		
		public function _build_validation(Validation $validation)
		{
			//echo Debug::vars('168',Arr::get($validation, 'devName'),  mb_detect_encoding(Arr::get($validation, 'devName'))); exit;
			
			$pattern='/^[a-zA-Zа-яА-ЯЁё0-9\s№* ().-_]+$/';
			$ddl= parent::build_validation($validation)
				->rule('ip', 'ip') 
				->rule('login', 'not_empty')
				->rule('login', 'regex', array(':value', $pattern))
				->rule('pass', 'not_empty')
				->rule('pass', 'regex', array(':value', $pattern))
				->rule('ip', array($this,'checkIP'))
				->rule('devName', array($this,'checkDevName'))
				; 

			//echo Debug::vars('200', get_class_methods($ddl), !$ddl->check()); exit;
			Log::instance()->add(log::INFO, Arr::get($this->_options, 'log_prefix').' 188 Старт добавление устройства "'. iconv('CP1251','UTF-8', Arr::get($ddl, 'devName')).'" IP='.Arr::get($ddl, 'ip')); 
			if(!$ddl->check()){
				echo  Debug::vars('203 No'); exit;
				Log::instance()->add(log::INFO, Arr::get($this->_options, 'log_prefix').' 204 Устройство "'. iconv('CP1251','UTF-8', Arr::get($ddl, 'devName')).'" не может быть добавлено: данные не прошли валидацию.'. Debug::vars($ddl->errors()));
				exit;
			}	
			/*
			else {


				echo  Debug::vars('209 Yes'); exit;
			}				
			*/	
		}
				

		public static function checkIP($ip)
		{
			//проверка, что такого IP нет в базе данных.
			
			$sql='select count(bp.id_dev) from  bas_param bp
				where  bp.param=\'IP\'
				and bp.intvalue='.ip2long($ip);
			//echo Debug::vars('168', $sql);	exit;
				$check_ip = DB::query(Database::SELECT, DB::expr($sql))
				->execute(Database::instance('fb'))
				->get('COUNT');
			if($check_ip>0) return false;
			return true;
		}
		
		
		public static function checkDevName($devName)
		{
			
				//проверка уникальности имени
			$check_devName=0;
			$sql='select count(*) from device d
			where d.name=\''.$devName.'\'';
			
			//echo $sql; exit;
				$check_devName = DB::query(Database::SELECT, DB::expr($sql))
				->execute(Database::instance('fb'))
				->get('COUNT');
			if($check_devName>0) return false;
			return true;
		}
		
		
		
		
    }
	