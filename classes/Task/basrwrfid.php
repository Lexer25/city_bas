    <?php defined('SYSPATH') or die('No direct script access.');
	
/*
Вер. 1.5
12.03.2025
[*]
Перед начало работы приложение смотрит состояние панели в таблице bas_param, поле ONLINE.
Сбор событий начинается только для тех устройств, для которых ONLINE==1.
Параметр ONLINE заполняется приложением приложением C:\AsyCardAronit\Basip.exe (работает асинхронно, быстро).

Вер 1.4.
13.02.2025
[*]
Выявлена ошибка: если контроллер не удаляет карты, то прекращалась и загрузка карт.
Ошибка устранена. Теперь карты загружаются штатно.

Вер. 1.3
28.01.2025
[*]
В панелях с API менее 2 нет возможности получить UID по номеру карты.
Как следствие, карты не удаляются, и при этом причина не указывалась.
Добавлена обработка этой ситуации: при удалении карты выполняется проверка версии API. Если версия ниже 2, то карта не удаляется, в лог-файле делается запись об этом. 

если панели нет на связи, то в БД записывалось сообщение ERR Authorisation error.
Исправленно на ERR Device offline.

Вер. 1.2
16/01/2025
Ошика: Не удалялись карты.
Признак ошибки: при записи карты в логе было сообщение, что карту уже имеется в панели.
[*]
при удалении карты вызывался метод delCard(), и внего передавался не номер карты, а ранее определенный UID. В результате этого карта не удалялась.
Замено на вызов метода delUid(), и в него передается ранее определенный UID карты. Удалдение заработало штатно.

Вер. 1.1
25.03.2024 
[+]
валидация номера карты при записи в панель.
при отсутствии связи в таблицу cardidx заносится этот факт. Теперь пишет, что ERR Authorisation error
[-]

[*]
отредактированы сообщения о записи карт в панель. Теперь их формат близок к АСерверу.
[ToDo]
Выявилась ошибка подклюения, т.к. задан неправильный IP адрес 0.0.0.0. Это надо корректно обрабатывать. Задача для basoop.
C:\xampp\php\php.exe c:\xampp\htdocs\bas\modules\minion\minion --task=basrwrfid
*/
 
    class Task_basrwrfid extends Minion_Task {
		
		    protected $_options = array(
        // param name => default value
        'id_dev'   => '7',
       
		);
	
        
        protected function _execute(array $params)
        {
			$t1=microtime(1);
           Log::instance()->add(Log::DEBUG, 'rw-14 start basrwrfid');
		//получаю список контроллеров, в которые надо загрузить карты
					
			$sql='select d.id_dev, bp2.intvalue from device  d
            join bas_param bp on bp.id_dev=d.id_dev  and bp.param=\'IP\'
			join bas_param bp2 on bp2.id_dev=d.id_dev  and bp2.param=\'ONLINE\'
            join servertypelist stl on stl.id_server=d.id_server
            join servertype srt on srt.id=stl.id_type
            where srt.sname=\'bas\'';
			//and bp2.intvalue=1';
			//$sql=$sql.' and d2.id_dev=354';

		$query = DB::query(Database::SELECT, $sql)
				->execute(Database::instance('fb'))
				->as_array()
				;
		//echo Debug::vars('68', $sql, $query); exit;
		//Log::instance()->add(Log::DEBUG, '69 будут обработаны устройства:  '.Debug::vars($query));		//exit;
		foreach ($query as $key=>$value)
		{
			//echo Debug::vars('68', $key, $value); exit;
			if(Arr::get($value, 'INTVALUE') == 1)
			{				
			$ModelBas=new Model_Basoop();
			$ModelBas->init_dev(Arr::get($value, 'ID_DEV'));
			$version= $this-> majorApiVer($ModelBas->api_version);
			//echo Debug::vars('65', $ModelBas, $this-> majorApiVer($ModelBas->api_version)); exit;
				Log::instance()->add(Log::DEBUG, "\r\n\r\n".'rw=34 basrwrfid Начало работы с панелью '.$ModelBas->baseurl.', id_dev= '.$ModelBas->id_dev. ', version='.$ModelBas->api_version);
				
					if($ModelBas->statusOnline)// если панель на связи, то начинаю обработку очереди карт.
					{
							//Log::instance()->add(Log::DEBUG, 'rw=37 Панель '.$ModelBas->baseurl.' на связи '.$ModelBas->statusOnline.', '.$ModelBas->device_model.', '.$ModelBas->framework_version.', '.$ModelBas->firmware_version.', '.$ModelBas->api_version);
						
							if($ModelBas->account_type == 'admin')// если авторизация выполнена и текущая роль admin, то начинаем работы с картами.
							{
								Log::instance()->add(Log::DEBUG, '260 авторизация в устройстве '.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.' выполнена успешно.');
								$result=$ModelBas->getCardList(Arr::get($value, 'ID_DEV'));
								//echo Debug::vars('My_debug 169', $result); exit;
								//Log::instance()->add(Log::DEBUG, '44 список карт для '.$ModelBas->baseurl.' для обработки '.Debug::vars($result));
								if($result){
									Log::instance()->add(Log::DEBUG, '49 список карт для '.$ModelBas->baseurl.'. Количество карт для обработки '. count($result));
									foreach($result as $key=>$value)
									{
										//echo Debug::vars('My_debug 255', $value); exit;
										//Log::instance()->add(Log::DEBUG, '290 Обрабатывается карта '.Debug::vars($value));
										
												
										switch (Arr::get($value, 'OPERATION'))
										{
											case 1:// запись карты в панель
											//echo Debug::vars('My_debug 259', $value); //exit;
													
												// валидация номера карты	
												$post=Validation::factory($value);
												$post->label('ID_CARD', 'Номер карты');
												$post->rule('ID_CARD', 'not_empty')
													->rule('ID_CARD', 'digit')
													//->rule('ID_CARD', 'regex', array(':value', '/^[0-9]$u/' ))
													->rule('ID_CARD', 'min_length', array(':value', 3))
													->rule('ID_CARD', 'max_length', array(':value', 20));
												
												if($post->check())
													{
													
														//Log::instance()->add(Log::DEBUG, '71 Валидация номера RFID для '.$ModelBas->baseurl.'. key="'. Arr::get($value, 'ID_CARD').'" выполнена успешно');
														$data=$ModelBas->addCard(Arr::get($value, 'ID_CARD'));
														//Log::instance()->add(Log::DEBUG, '56 результат выполнения команды запись карты в '.$ModelBas->baseurl.'. Ответ: '. Debug::vars($data).' Данные команды для записи карты '.Debug::vars($value));
														Log::instance()->add(Log::DEBUG, '62 Command destination:: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'".');
														switch (Arr::get($data, 'status'))
														{	
															case 200:
																//$result=Arr::get($data, 'res');
																
																// запись прошла успешно, в ответ получен UID Arr::get(Arr::get($data, 'res'), 'uid')
																

																if($ModelBas->checkCardUid(Arr::get($value, 'ID_CARD'), Arr::get(Arr::get($data, 'res'), 'uid'))) 
																{
																	//сверка прошла успешно. Карта действительно записана в панель.
																	
																	
																} else {
																	
																	//сверка прошла с ошибкой. Карты нет в панели
																	Log::instance()->add(Log::DEBUG, '134 ВНИМАНИЕ!!! При записи карт в id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: OK 200 uid '. Arr::get(Arr::get($data, 'res'), 'uid') .' сверка прошла с ошибкой.' );
																
																	
																}
																//фиксирую результат записи в таблице caridx
																	$ModelBas->fixCardIdxOK($value, $data);
																	
																	//удалить строку из таблицы cardindev
																	$ModelBas->delFromCardindev($value);
																	Log::instance()->add(Log::DEBUG, '65 Answer destination: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: OK 200 uid '. Arr::get(Arr::get($data, 'res'), 'uid') );
																
																break;
															case 400:
																//echo Debug::vars('61',$value, $data); exit;
																//зафиксировать ответ в таблице cardidx рассматриваю этот ответ как Идентификатор уже существует.
																
			/*    Identifier with that type and number already exist
				Cannot create identifier with the same input code number as master code
				Cannot create identifier with same card number as master card
				You cannot create face_id identifier on this panel
				Wrong face_id image data
				Validation error. Required fields are not provided.
				Missed json body or wrong param. Response sample:
			*/
																//фиксирую результат записи в таблице caridx
																$ModelBas->fixCardIdxOK($value, $data);
																
																//удалить строку из таблицы cardindev
																$ModelBas->delFromCardindev($value);
																
																Log::instance()->add(Log::DEBUG, '91 Answer destination: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: OK 400 '. Arr::get(Arr::get($data, 'res'), 'error') );
																break;
															case 0:
																//всем записать большое количество попыток, чтобы прекратить записи в дальшейшем
																//выход из метода, полный выход
																Log::instance()->add(Log::DEBUG, '80 answer Questions: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: 0');
																break;
															
															case 500:
																//всем записать большое количество попыток, чтобы прекратить записи в дальшейшем
																//выход из метода, полный выход
																Log::instance()->add(Log::DEBUG, '84 Answer destination: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: 500 '. Arr::get(Arr::get($data, 'res'), 'error') );
																break;
															
															default:
																//зафиксировать ответ в таблице cardidx как ошибку
																//увеличить количество попытко на 1
																Log::instance()->add(Log::DEBUG, '90 answer Questions: writekey id_dev='.Arr::get($value, 'ID_DEV').' IP '.$ModelBas->baseurl.',  key="'. Arr::get($value, 'ID_CARD').'". Answer: default'. Debug::vars($data)); 
																break;
															
														}
																
													} else { //валидация карты прошла с ошибками. Увеличиваю количество попыток в cardindev на 1, фиксирую ошибку в cardidx
														
														Log::instance()->add(Log::DEBUG, '75 Ошибка валидации для '.$ModelBas->baseurl.'. key="'. Arr::get($value, 'ID_CARD').'" ' .Arr::get($post->errors('validation'), 'ID_CARD'). ' Номер карты в панель записываться не будет.');
														
														Model::factory('GetCommand')->attCommand(Arr::get($value, 'ID_CARDINDEV'));//Валидация прошла неуспешно, увеличиваю количество попыт
														
													}
												
												
												
											
											break;
											case 2:// удаление карты из панели
											
										
										/* 	Log::instance()->add(Log::DEBUG, '153 Command destination: deletekey id_dev='.Arr::get($value, 'ID_DEV').', key="'. Arr::get($value, 'ID_CARD').'"');
													if(Arr::get($version, 0) == 1){
												Log::instance()->add(Log::DEBUG, 'rw=34 basrwrfid Версия API '.$ModelBas->api_version.' не поддерживает удаление карт. Необходимо обновить прошивку панели до версии 2 и выше.');
												//exit;

											} */	
											
											//получаю UID карты
												$data=$ModelBas->getInfoCard(Arr::get($value, 'ID_CARD'));//получил результат запроса UID по номеру карты
												//Log::instance()->add(Log::DEBUG, '304 Удаление карт для '.$ModelBas->baseurl.'. Результат поиска UID в панели '. Debug::vars($data));
												//echo Debug::vars('My_debug 198', $value, $data); //exit;
												//echo Debug::vars('296 удаляю карту '.Arr::get($value, 'ID_CARD').'. Результат проверки ', $data); exit;
												switch (Arr::get($data, 'status'))
												{	
													case 200:
													//запрос выполнен успешно. Теперь надо проверитьсодержимое res
														//получен UID результат запроса
														if(Arr::get($data, 'res')) //если имеется UID
														{
															//Log::instance()->add(Log::DEBUG, '286 Удаление карты для IP '.$ModelBas->baseurl.', которая найдена в контроллере '. Debug::vars($data, $value));
															
															//$data2=$ModelBas->delCard(Arr::get($data, 'res'));// удаляю карту из панели
															$data2=$ModelBas->delUid(Arr::get($data, 'res'));// удаляю карту из панели
															if(Arr::get($data2, 'status') == 200)
															{
																// если удаление прошло успешно, то
																//удалить строку из таблицы cardindev
																$ModelBas->delFromCardindev($value);//удаляю строку с командой из таблицы cardindev
																Log::instance()->add(Log::DEBUG, '179 Answer destination: deletekey id_dev='.Arr::get($value, 'ID_DEV').', key="'. Arr::get($value, 'ID_CARD').'" Answer: 200 OK');
														
															} else {
																// не смогу удалить карту из панели
																//$ModelBas->incrementAttemptsCardindev($data);//увеличиваю количество попыток attempts в таблице cardindev
																//$ModelBas->fixCardIdxErr($value, $data);//фиксирую сообщение об ошибке в таблицу cardidx
																Log::instance()->add(Log::DEBUG, '179 Answer destination: deletekey id_dev='.Arr::get($value, 'ID_DEV').', key="'. Arr::get($value, 'ID_CARD').'" Answer: ERR'. Debug::vars('178', $data));
														
															}
														}
														//echo Debug::vars('309', $data, $value); exit;
														if( is_Null(Arr::get($data, 'res'))) //этой карты нет в устройстве. 
														{
															Log::instance()->add(Log::DEBUG, '241 Answer destination: deletekey id_dev='.Arr::get($value, 'ID_DEV').', key="'. Arr::get($value, 'ID_CARD').'" Answer: OK');
														
															//Log::instance()->add(Log::DEBUG, '186 Удаление карты для '.$ModelBas->baseurl.'. Карты в контроллере нет, удаляю только строку из таблицы cardindev.'. Debug::vars($value));
															$ModelBas->delFromCardindev($value);//удаляю строку с командой из таблицы cardindev
														}
														break;
													default:
													// не смогу удалить карту из панели, т.к ответ за запрос неправильный
													Log::instance()->add(Log::DEBUG, '199 Answer destination: deletekey id_dev='.Arr::get($value, 'ID_DEV').', key="'. Arr::get($value, 'ID_CARD').'" Answer: ERR'. Debug::vars($data));
														
														//$ModelBas->iincrementAttemptsCardindev($data);//увеличиваю количество попыток attempts в таблице cardindev
													break;
														//не смог получить UID  от панели
												}
											
											break;
											
											
										}
										
								
										
									}
								} else {
									Log::instance()->add(Log::DEBUG, '373 Список карт устройства '.Arr::get($value, 'ID_DEV').' пуст.');	
								}
							} else {
								//увеличить attempt, запись результат в cardidx
													
								$ModelBas->fixCardIdxErrNoConnect('Authorisation error', Arr::get($value, 'ID_DEV')); //$messErr, $id_dev
								Log::instance()->add(Log::DEBUG, '136 Аворизация для устройства '.$ModelBas->baseurl.' не выполнена. Ответ устрйоства: '. Debug::vars($ModelBas->account_type));	
							}
						//Log::instance()->add(Log::DEBUG, date("H:i:s").' rw- 137 работа с контроллером '.$ModelBas->baseurl.' завершена.'."\r\n\r\n");
					} else {
								// панель не на связи
								Log::instance()->add(Log::DEBUG, 'event-151 с контроллером '.$ModelBas->baseurl.' связи нет'); 
								//todo это можно зафиксировать в БД СКУД, чтобы показывать: связи нет. Аналог шэлт контрола.
								//а еще хорошо бы сделать пометка для карт этого контроллера, что авторизация прошла с ошибками.
								$ModelBas->fixCardIdxErrNoConnect('ERR Device offline ', Arr::get($value, 'ID_DEV')); //$messErr, $id_dev
							}
				//Удаление экземпляр класса
				unset($ModelBas);
			}	else {

				//$ModelBas->fixCardIdxErrNoConnect('ERR Device offline ', Arr::get($value, 'ID_DEV')); //$messErr, $id_dev
				Log::instance()->add(Log::DEBUG, "\r\n\r\n".'rw=34 basrwrfid Начало работы с панелью id_dev= '.Arr::get($value, 'ID_DEV'). ' панель помечена как неактивная.');
		
				Model::factory('basoop')->fixCardIdxErrNoConnect('ERR Device offline ', Arr::get($value, 'ID_DEV')); //$messErr, $id_dev
			}	
			
		}
				
		
		Log::instance()->add(Log::DEBUG, '142 stop minion basrwrfid.php. timeExecute='. (microtime(1)-$t1));
	
	}
	
	/** 29.01.2025 Функция получает номер версии API
	* @- строка с версий,
	*output - версия в виде массива.
	*/
	public function majorApiVer($version)
	{
		return Arr::get(explode(".", $version), 0);
		
	}

	
    }
	