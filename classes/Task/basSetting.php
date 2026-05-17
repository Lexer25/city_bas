    <?php defined('SYSPATH') or die('No direct script access.');
	/*
	Вер. 1.1
	13.03.2025
	программа записывает в каждый контроллера настройки для работы с сервером.
	Ожидаю получение руфкеыИуфе и журнала событий.
		
		C:\xampp\php\php.exe c:\xampp\htdocs\bas\modules\minion\minion --task=basSetting
	*/
 
    class Task_basSetting extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
        //'id_dev'   => '7',
		'aaa'=>'111',
       
		);
	
        
        protected function _execute(array $params)
        {
			$t1=microtime(1);
           Log::instance()->add(Log::DEBUG, 'event-14 start basgetevent');
		//получаю список контроллеров для сбора событий
		$sql='select  d.id_dev, bp.intvalue from device  d
            join bas_param bp on bp.id_dev=d.id_dev  and bp.param=\'IP\'
			join bas_param bp2 on bp2.id_dev=d.id_dev  and bp2.param=\'ONLINE\'
            join servertypelist stl on stl.id_server=d.id_server
            join servertype srt on srt.id=stl.id_type
            where srt.sname=\'bas\'
			and d.id_dev<500';

			
	

		$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array()
				;
	Log::instance()->add(Log::DEBUG, 'basSetting start');	
	//echo Debug::vars('42');exit;
	//echo Debug::vars('43', count($query)); exit;
	if(count($query)>0)
	{
		foreach ($query as $key=>$value)
		{
			
			
			$ModelBas=new Model_Basoop();
		
			$ModelBas->init_dev(Arr::get($value, 'ID_DEV')); //создаю модель панель для указанного ID пользователя
			$ModelBas->quitLog('basSetting=50 Начало работы с панелью '.$ModelBas->baseurl.', id_dev= '.$ModelBas->id_dev.', device="'.iconv('windows-1251','UTF-8',$ModelBas->name));	
			Log::instance()->add(Log::DEBUG, 'basSetting=50 Начало работы с панелью '.$ModelBas->baseurl.', id_dev= '.$ModelBas->id_dev.', device="'.iconv('windows-1251','UTF-8',$ModelBas->name).'".');
			if($ModelBas->statusOnline)// если панель на связи, то начинаю выборку событий.
			{
						
			//Log::instance()->add(Log::DEBUG, '#43 Панель '.$ModelBas->baseurl.' на связи '.$ModelBas->statusOnline.', '.$ModelBas->device_model.', '.$ModelBas->framework_version.', '.$ModelBas->firmware_version.', '.$ModelBas->api_version);
			
			//$ModelBas->saveParam('ABOUT', NULL, $ModelBas->statusOnline.', '.$ModelBas->device_model.', '.$ModelBas->framework_version.', '.$ModelBas->firmware_version.', '.$ModelBas->api_version); //сохраняю параметры панели в БД СКУД.
			
			//Log::instance()->add(Log::DEBUG, 'event-43 провожу авторизацию панели id_dev='. $ModelBas->id_dev.', id_ctrl='. $ModelBas->id_ctrl.',  baseurl='. $ModelBas->baseurl.',  name=\''. iconv('windows-1251', 'UTF-8',$ModelBas->name).'\'');
		
					if($ModelBas->account_type == 'admin')// если авторизация выполнена и текущая роль admin, то начинаю выборку событий.
					{
						Log::instance()->add(Log::DEBUG, 'event-260 авторизация в устройстве baseurl='. $ModelBas->baseurl.',  name=\''. iconv('windows-1251', 'UTF-8',$ModelBas->name).'\' выполнена успешно.');
						
						//$ModelBas->setManagementServer('http');// Настрйока на работу с сервером СКУД.
						Log::instance()->add(Log::DEBUG, 'basSetting 65 check setting before '.Debug::vars($ModelBas->getManagementServer()));
						//Log::instance()->add(Log::DEBUG, 'basSetting 66 setting '.Debug::vars($ModelBas->setManagementServer('http')));
						//Log::instance()->add(Log::DEBUG, 'basSetting 67 check setting after '.Debug::vars($ModelBas->getManagementServer()));
						
						
						echo Debug::vars('75-2 get network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getManagementLink()); //exit;
						//echo Debug::vars('75-2 get network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->setManagementLink('http')); //exit;
						//echo Debug::vars('75-2 get network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getManagementLink()); //exit;
						
						
					} else {
						//авторизация выполнена неуспешно
													
						Log::instance()->add(Log::DEBUG, 'basSetting-136 Авторизация для устройства '.Arr::get($value, 'ID_DEV').' не выполнена. Ответ устрйоства: '. Debug::vars($ModelBas->account_type));	
					}
				//Log::instance()->add(Log::DEBUG, date("H:i:s").' ev- 137 работа с контроллером '.$ModelBas->baseurl.' завершена.'."\r\n\r\n");
									
			} else {
				// панель не на связи
				Log::instance()->add(Log::DEBUG, 'basSetting-151 с контроллером '.$ModelBas->baseurl.' связи нет id_dev='.$ModelBas->id_dev);
				//$ModelBas->saveParam('ABOUT', NULL, 'no connect'); //сохраняю параметры панели в БД СКУД.
			
				
			} 
			
		} 
	} else {
			Log::instance()->add(Log::DEBUG, 'basSetting-153 Нет устройств Bas-ip');
		}
		
		
		
	}

	
}
