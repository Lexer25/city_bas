    <?php defined('SYSPATH') or die('No direct script access.');
	/*
	16.01.2025
	программа для отладки работы с панелями bas-ip
		
		C:\xampp\php\php.exe c:\xampp\htdocs\bas\modules\minion\minion --task=test2 --aaa=111
		
		C:\xampp\php\php.exe c:\xampp\htdocs\bas\modules\minion\minion --task=test2 > 1.htm
	*/
 
    class Task_test2 extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
        //'id_dev'   => '7',
		'aaa'=>'88',//1.0.7
		//'aaa'=>'154',//2.9.0
		'ID_CARD'=>'44332211',
	//	'ID_CARD'=>'4125',
       
		);
	
        
        protected function _execute(array $params)
        {
		$t1=microtime(1);

//тест логирования


       $model='basoopDev';
		 echo Debug::vars('25 event-14 start basgetevent с моделью '.$model.'. Время запуска '.$t1); //exit;
		//получаю список контроллеров для сбора событий
		/* $sql='select d.id_dev, bp.intvalue from device  d
            join bas_param bp on bp.id_dev=d.id_dev  and bp.param=\'IP\'
            join servertypelist stl on stl.id_server=d.id_server
            join servertype srt on srt.id=stl.id_type
            where srt.sname=\'bas\'';
			
		echo Debug::vars('29', $params); //exit;

		$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array()
				;
				 */
		$query=array(
			array('ID_DEV'=>Arr::get($params, 'aaa')));		
		//echo Debug::vars('35', $query);// exit;

		
	if(count($query)>0)
	{
		foreach ($query as $key=>$value)
		{
			//echo Debug::vars('48', Arr::get($value, 'ID_DEV')); exit;
			$ModelBas=new Model_basoop();
			
			$ModelBas->init_dev(Arr::get($value, 'ID_DEV')); //создаю модель панель для указанного ID пользователя

			//Log::instance()->add(Log::DEBUG, 'ev=34 Начало работы с панелью '.$ModelBas->baseurl.', id_dev= '.$ModelBas->id_dev.', device="'.iconv('windows-1251','UTF-8',$ModelBas->name).'".');
			if($ModelBas->statusOnline)// если панель на связи, то начинаю выборку событий.
			{
				echo Debug::vars('52', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas); //exit;
				
				//echo Debug::vars('65 get network/management/server/certificate', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getMqttAboutCertificate()); //exit;
				//echo Debug::vars('65-1 set network/management/server/debug', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getMqttDebug()); //exit;
				
				//echo Debug::vars('57 get befor network/management/server', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getManagementServer()); //exit;
				//echo Debug::vars('57-1 set network/management/server', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->setManagementServer('mqtt')); //exit;
			//echo Debug::vars('57-1 set network/management/server', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->setManagementServer('http', false, false)); //exit;
			
			//$ModelBas->setManagementLink('http', false, false);//1
			//$ModelBas->setManagementLink('http', false, true);//2
			//$ModelBas->setManagementLink('http', true, false);//3
			//$ModelBas->setManagementLink('http', true, true);//4
			
			//$link=$ModelBas->getManagementLink();
			//echo Debug::vars('57-1 get network/management/link', $link);
		
			//$ModelBas->setManagementServer('http', false, false);//1
			//$ModelBas->setManagementServer('http', false, true);//2
			//$ModelBas->setManagementServer('http', true, false);//3
			//$ModelBas->setManagementServer('http', true, true);//4
			
			//$server= $ModelBas->getManagementServer();
			//echo Debug::vars('57-1 get network/management/server', $server);
			
			//echo Debug::vars('57-2 get after network/management/server', 'id_dev='.Arr::get($value, 'ID_DEV')); //exit;
				
				//echo Debug::vars('75-2 get befor network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->getManagementLink()); //exit;
			//echo Debug::vars('75-2 get network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas->setManagementLink('http', false, false)); //exit;
			
			
			//	echo Debug::vars('75-2 get after network/management/link', 'id_dev='.Arr::get($value, 'ID_DEV')); //exit;
//echo Debug::vars('Сводная |'.(Arr::get($link, 'link_enable')? '1':'0').'|'.(Arr::get($link, 'heartbeat')? '1':'0').'|'.(Arr::get($server, 'is_enabled')? '1':'0').'|'.(Arr::get($server, 'is_heartbeat_enabled')? '1':'0').'|'); //exit;
				
//			exit;	
				
				echo Debug::vars('57-3 getTime', $ModelBas->getTime()); //exit;
				echo Debug::vars('60 номер карты '.Arr::get($params, 'ID_CARD')); //exit;
				//Проверю информацию о карте
				$data=$ModelBas->getInfoCard(Arr::get($params, 'ID_CARD'));//получил результат запроса UID по номеру карты				
				echo Debug::vars('56 начало работ. Есть ли данные по карте '.Arr::get($params, 'ID_CARD').' в панеле? При при правильной работе карты быть НЕ должно.', $data); //exit;
				
				
				//записываю карту
				echo Debug::vars('61 записываю карту '.Arr::get($params, 'ID_CARD').' в панель. Ответ:', $ModelBas->addCard(Arr::get($params, 'ID_CARD')));
				
				//опять проверю информацию о карте Инфомрация быть должна.
				$data=$ModelBas->getInfoCard(Arr::get($params, 'ID_CARD'));
				echo Debug::vars('65 Проверю информацию по карте '.Arr::get($params, 'ID_CARD').' в панеле', $data , Arr::get($data, 'res')); //exit;
				
				//Проверю по UID
				
				echo Debug::vars('74 вот результат проверки по UID '.Arr::get($data, 'res').'. Ответ:',  $ModelBas->getInfoUID(Arr::get($data, 'res')));
				$res=$ModelBas->getInfoUID(Arr::get($data, 'res'));
				
				echo Debug::vars('77 вот результат проверки по UID: '. Arr::get(Arr::get($res, 'res'), 'identifier_number'));
				
				echo $ModelBas->checkCardUid(Arr::get($params, 'ID_CARD'), Arr::get($data, 'res') );
				
				
				//удаляю карту
				$data3=$ModelBas->delCard(Arr::get($params, 'ID_CARD'));// удаляю карту из панели
				//$data2=$ModelBas->delUid(Arr::get($data, 'res'));// удаляю карту из панели
				
				echo Debug::vars('71 удаляю карту '.Arr::get($params, 'ID_CARD').' из панели. Вот результат операции удаления:', $data3); //exit;
				
				
				//и опять проверю информацию о карте. Карты в панеле быть не должно.
				$data4=$ModelBas->getInfoCard(Arr::get($params, 'ID_CARD'));
				echo Debug::vars('89 еще раз проверю информацию о карте '.Arr::get($params, 'ID_CARD').' в панеле. Осталось ли там что-нибудь?', $data4);
				echo Debug::vars('90 а вот результат проверки по UID '.Arr::get($data, 'res').'. Ответ:',  $ModelBas->getInfoUID(Arr::get($data, 'res')));
				exit;
			
			} else {
				
				echo Debug::vars('85 нет связи', 'id_dev='.Arr::get($value, 'ID_DEV'),  $ModelBas); //exit;
			}
		}
	} else {
			Log::instance()->add(Log::DEBUG, 'event-153 Нет устройств Bas-ip');
	}
		
		
		//Log::instance()->add(Log::DEBUG, 'event-142 stop minion basgetevent.php. Выборка событий завершена. Время выполнения timeExecute='. (microtime(1)-$t1));
	}


	
	
}