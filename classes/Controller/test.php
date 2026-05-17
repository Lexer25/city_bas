<?php defined('SYSPATH') or die('No direct script access.');

/*
20.04.2024 отладка получения события от панели.
Адрес http://26.98.93.81:8080/bas/test
https://snipp.ru/php/json-php?ysclid=lv8kv04clf157208780 -тут коды ошибок парсера json. Надо будет вставить в основную программу.
*/
class Controller_test extends Controller{
	
	//public $baseurl = '10.200.12.4';
	public $baseurl = '10.200.15.4';
	public $account_type = '-';
	public $server_type = 'bas';
	public $statusOnline = false;
	public $tokenBearer='';// токен авторизации
	public $id_dev='';
	public $id_ctrl='';
	public $id_dev_door0='';
	public $id_dev_door1='';
	public $name='';
	public $device_model='';
	public $framework_version='';
	public $device_name=''; //модель устройства
	public $firmware_version='';
	public $api_version='';
	public $user='admin';
	public $pass='171120';
	public $errmess;//текст сообщения об ошибке


		public function action_index()
		{
			$this->init($this->baseurl);
			
			//$data=$this->getLog(1549286120038, 50);
			//$data=$this->getSnapshot();
			//$this->savePhoto('444', $data);//работае. сохраняется файл с данными в формате webp. есть функция PHP imagecreatefromwebp преобразования файлов, но я с ней пока не разбирался.
			
			echo Debug::vars('30', $this->about()); exit;
			
		}
		
		
		public function getLog($from = 0, $limit=50)// получить лог-файл
		{
			
		//	https://virtserver.swaggerhub.com/basip/panel-web-api/2.3.0/log/items?locale=en&from=1549286120038&to=1549286120038&limit=20&page_number=2&sort_type=asc&filter_field=category&filter_type=equal&filter_value=access
		
					//2024-04-20 08:44:47,448458	192.168.1.5	10.200.15.4	HTTP	579	GET /api/v1/log/items?locale=ru&limit=10&page_number=7&filter_field=category&filter_type=equal&filter_value=access HTTP/1.1 
			
			$data1=array(
					'locale'=>'ru',
					//'from'=>$from,
					'limit'=>10,
					'page_number' => 7,
					'filter_field'=>'category', 
					'filter_type'=>'equal', 
					'filter_value'=>'access');					
			
		// мой запроса 2024-04-20 09:00:14,754895	192.168.1.5	10.200.15.4	HTTP	379	GET /api/v1/log/items?locale=en&from=1713015696315&limit=50&page_number=2&sort_type=asc&filter_field=category&filter_type=equal&filter_value=access HTTP/1.1 
		  //веб-панель 2024-04-20 09:15:49,764416	192.168.1.5	10.200.15.4	HTTP	598	GET /api/v1/log/items?locale=ru&limit=10&page_number=8&from=1711919100000&filter_field=category&filter_type=equal&filter_value=access HTTP/1.1 
		$data2=array(
					//'from'=>1549286120038,
					//'from'=>1549286120038,
					'locale'=>'ru',
					'from'=>$from,
					//'limit'=>$limit,
					'limit'=>50,
					'page_number' => 1,
					'sort_type' => 'asc',
					'filter_field'=>'category', 
					'filter_type'=>'equal', 
					'filter_value'=>'access');
		$request = Request::factory('http://'.$this->baseurl.'/api/v1/log/items')
				->query($data1)
				->headers("Accept", "application/json")
				->headers("Content-Type", "application/json")
				->headers("Authorization", 'Bearer '.$this->tokenBearer)
				->method('GET');
			//echo Debug::vars('30', 'event-776 request log send data', $request);
			
			$response=$request->execute();
			//return json_decode ($request->body(), true);
			//echo Debug::vars('69','event-781 response log ',  $response);
			//Log::instance()->add(Log::DEBUG, 'event-781 response status log '. Debug::vars(Arr::get($response, 'status')));
			Log::instance()->add(Log::DEBUG, 'event-495 request log '. Debug::vars($request));
			Log::instance()->add(Log::DEBUG, 'event-496 Ожидаю события '. Debug::vars($response->body()));
			echo Debug::vars('84', Arr::get(json_decode ($response->body(), true), 'list_items'));
			
		}
		
		
	/*
	20.05.2023 
	Получение технической информации о панели bas-ip.
	
	*/
	
	public function about()
	{
	
		$request = Request::factory('http://'.$this->baseurl.'/api/info')
		//$request = Request::factory('http://10.200.20.2/api/info')
				->headers("Accept", "application/json")
				-> method(Request::GET);
				//->headers("Authorization", $token);
		try
		{	
			$response=$request->execute();
			return json_decode ($response->body(), true);
		} catch (Kohana_Request_Exception $e) {
			$this->statusOnline=False;
			return $e->getMessage();
		}
	}
	
	
		/*
		сохранине фотографии на диск
		
		 
		 */
		 public function savePhoto($namePhoto='333', $data)
		 {
			$path="C:\\xampp\\htdocs\\grzPhoto\\";
			$path="C:\\xampp\\htdocs\\\parkresident\\grzPhoto\\";
				$fp = fopen($path.$namePhoto.'.jpg', "w"); // Открываем файл в режиме записи	
				$test = fwrite($fp, $data); // Запись в файл
				fclose($fp); //Закрытие файла
				
			 return;
		}
		 
	
	/*
	21.05.2024 
	Получение фоторгафии.
	
	*/
	
	public function getSnapshot()
	{
	
		//$request = Request::factory('http://'.$this->baseurl.'/api/photo/file')
			$request = Request::factory('http://'.$this->baseurl.'/api/v1/photo/file')
				->headers("Accept", "application/json")
				->headers("Content-Type", "application/json")
				->headers("Authorization", 'Bearer '.$this->tokenBearer)
				->method('GET');
			//echo Debug::vars('30', 'event-776 request log send data', $request);
			
			$response=$request->execute();
			//return json_decode ($request->body(), true);
			//echo Debug::vars('69','event-781 response log ',  $response);
			//echo Debug::vars('69','event-781 response log ',  $response);
			//Log::instance()->add(Log::DEBUG, 'event-781 response status log '. Debug::vars(Arr::get($response, 'status')));
			//Log::instance()->add(Log::DEBUG, 'event-495 request log '. Debug::vars($request));
			//Log::instance()->add(Log::DEBUG, 'event-496 Ожидаю события '. Debug::vars($response->body()));
			//echo Debug::vars('84', json_decode ($response->body(), true)); exit;
			echo Debug::vars('84', $response->body()); 
			return $response->body(); 
	}
	
	
		
		public function init()// провожу авторизацию. в ответ я должен получить Bearer, который затем надо вставлять во все запросы
		
		{
			//$this->baseurl=$ip;
			$a=$this->makeLogin();
			
			if($a)
			{
			$this->statusOnline=True;
			$this->account_type=Arr::get($a, 'account_type');
			$this->tokenBearer=Arr::get($a, 'token');
			$about=$this->about();
			
			$this->device_model=Arr::get($about, 'device_model');
			$this->framework_version=Arr::get($about, 'framework_version');
			$this->device_name=Arr::get($about, 'device_name');
			$this->firmware_version=Arr::get($about, 'firmware_version');
			$this->api_version=Arr::get($about, 'api_version');
				
			} else {
				$this->account_type='no';
				
			}
			return;
		
		}
		
		
		public function makeLogin()// первая авторизация. в ответ получаем много чего, в т.ч. и Bearer
	{
		$user=$this->user;
		$pass=$this->pass;

		
		//$token=md5($this->pass);
		
		$request = Request::factory('http://'.$this->baseurl.'/api/v1/login')
			->query(array('username'=>$this->user, 'password'=>md5($this->pass)))
			->headers("Accept", "application/json")
			//-> method(Request::GET);
			->headers("Authorization", 'Bearer ')
			-> method('GET');
		try{
			$response=$request->execute();
			//Log::instance()->add(Log::DEBUG, '17 '.Debug::vars($request, json_decode ($response->body(), true)));	
			//echo Debug::vars('147',json_decode ($response->body(), true));	
				$this->statusOnline=true;		
				return json_decode ($response->body(), true);
			} catch (Kohana_Request_Exception $e){
				Log::instance()->add(Log::DEBUG, '253 Проблема с авторизацией для '.$this->baseurl.' '.$e->getMessage());	
			//echo Debug::vars('152'); exit;
		}
	}
	
}

