<?php
//12.03.2025.
//Добавлен вывод состоняи ONLINE.
?>
<script>
 //https://learn.javascript.ru/function-object
$(function() {
	
     $(".btn").click(
       function() {
         var bname = $(this).attr('org_name');
         var bprice = $(this).attr('org_id');
         var ip_deb = $(this).attr('ip_dev');

	
         $(".kartka h1").text(bname);
         $(".kartka ttt").html(bprice);
         $(".kartka  h2").html(ip_deb);
		 
		 document.getElementById("id_org2").value = $(this).attr('org_id');
		 document.getElementById("id_org1").value = $(this).attr('org_id1');
		 document.getElementById("ipp").value = $(this).attr('ip_dev');
		 document.getElementById("login1").value = $(this).attr('login');
		 document.getElementById("pass1").value = $(this).attr('pass');
		 
       });
	

   });
 
   	$(function() {		
  		$("#tablesorter").tablesorter({sortList:[[0,0]]});
  	});	
	
 
</script>
<?php
$data = Session::instance()->get('alertErr', null);
Session::instance()->delete('alertErr');
$IPError = Session::instance()->get('alertIPErr', null);
Session::instance()->delete('alertIPErr');
$data1 = Session::instance()->get('alertOk', null);
Session::instance()->delete('alertOk');
//echo Debug::vars('93 OK', $deviceList);exit;
//
if (!is_null($data)){
	
	$sd = implode(",", $data);
    echo "<div class='alert alert-danger'>$sd</div>";
	

}
if (!is_null($IPError)){
	
	$sd = ($IPError);
    echo "<div class='alert alert-danger'>$sd</div>";
	

}
$deviceName = '';

if (!is_null($data1)){
	//$id_dev = $_GET['id_dev'];
	
	foreach ($deviceList as $device) {
		if ($device['ID_DEV'] == $id_dev) {
			$deviceName = $device['NAME'];
			break;
		}
	}
	
	echo "<div class='alert alert-success'>Изменения для устройства \"" . iconv('windows-1251', 'utf-8', $deviceName) . "\" успешно</div>";
}
    

?>


<div class="panel panel-primary">
	<div class="panel-heading">
		<h3 class="panel-title"><?echo __('bas_device_list').' ('.count($deviceList).')';?></h3>
	</div>
	<div class="panel-body">
		<?php
				$_trustPeriodInMinute=24*60;//доверенное вермя в минутах
				$trastPeriod=$_trustPeriodInMinute*60*1000;// доверительный период в секундах: 60 минут по 50 секунд. Умножение на 1000 - специфика времени вызвной панели. Если время последнего принятого события меньше этого период - все хорошо. Если больше - плохо, надо выделить желтым цветом.
		$statusColor=array(1=>'active', 2=>'success', 3=>'info', 4=>'warning', 5=>'danger' );
		$statusMess=array(1=>'Нет данных об устройстве', 2=>'Устройство работает штатно', 3=>'не используется', 4=>'Нет событий от устройства  за более чем _trustPeriodInMinute минут', 5=>'Нет связи с утройством' );
		?>
			<div>
			<h2>Легенда</h2>
			<table class="table table-striped table-hover table-condensed">
				<thead>
				<tr>
					<th><?php echo __('Статус');?></th>
					<th><?php echo __('Прим.');?></th>
				</tr>
				</thead>
				<tbody>
				<?php foreach ($statusColor as $key=>$value)
				{
					echo '<tr class="'.$value.'">';
					echo '	<td>'.$key.'</td>';
					echo '	<td>'.__(Arr::get($statusMess, $key), array('_trustPeriodInMinute'=>$_trustPeriodInMinute)).'</td>';
					echo '</tr>';
					
				}
				?>
				</tbody>
			</table>			
		</div>
		
		<div>
		<?php
			echo Form::open('bas/auth');
		?>
			<h2>Таблица состояния устройств на <?php echo date ('d.m.Y H:i:s', time());?></h2>
			<h3>Всего устройств <?php echo count($deviceList);
			$onLineCount=0;
			foreach($deviceList as $key=>$value)
			{
				if(Arr::get($value,'ONLINE') == 1) $onLineCount++;
			}?>
			<h3>На связи <?php echo $onLineCount;?> устройств(а).</h3>
			
			<table id="tablesorter" class="table table-condensed tablesorter">
			 <caption style="caption-side: top;">
				Сводная информация о состоянии вызывных па
			</caption>
				<thead>
				<tr>
					<th><?php echo __('npp');?></th>
					<th>Статус</th>
					<th><?php echo __('id_dev');?></th>
					<th><?php echo __('online');?></th>
					<th><?php echo __('Для записи');?></th>
					<th><?php echo __('Для удаления');?></th>
					<th><?php echo __('dev_name');?></th>
					<th><?php echo __('ip');?></th>
					<th><?php echo __('about');?></th>
					<th><?php echo __('lastevent');?></th>
					<th><?php echo __('lastequest');?></th>
					<th><?php echo __('to_do');?></th>
					
				</tr>
				
				</thead>
				
				<tbody>
				<?php 
				$data=array();
				$npp=0;
				if($deviceList){
				foreach($deviceList as $key=>$value)
				{
					
					
					$status=0;// серенький - про запас. Подойдет для отображения неопределенных состояний.
					
					$status=2;// хеленый - все в порядке
					
					if((Arr::get($value, 'LASTEVENT')>0) and((time() *1000) - Arr::get($value, 'LASTEVENT') > $trastPeriod)){
						
						$status=4;//желтый для привлечени внимания
					}
					if ((Arr::get($value, 'ABOUT')=='no connect') OR (Arr::get($value, 'ONLINE')==0)) {

						$status='5';//красный - тревога!!! что-то не так, требуется срочное вмешательство!!!
					}
					

					echo '<tr class="'.Arr::get($statusColor, $status).'">';
						echo '<td>'.++$npp. '</td>';
						echo '<td>'.$status.'</td>';
						echo '<td>'.Arr::get($value, 'ID_DEV'). '</td>';
						echo '<td>'.(Arr::get($value, 'ONLINE')? HTML::image('static/images/green-check.png') : ''); '</td>';
						
						
						echo '<td>';
							echo Arr::get($value, 'COUNT1');
						echo '</td>';
						echo '<td>';
							echo Arr::get($value, 'COUNT2');
						echo '</td>';
						
						
						echo '<td>'.iconv('windows-1251','UTF-8', Arr::get($value, 'NAME')).'</td>';
						echo '<td>'.HTML::anchor('http://' . long2ip(Arr::get($value, 'IP')), long2ip(Arr::get($value, 'IP'))).'</td>';
						echo '<td>'.Arr::get($value, 'ABOUT', '---').'</td>';
						echo '<td>';
							
							echo (is_null(Arr::get($value, 'LASTEVENT')))? '-' : date ('d.m.Y H:i:s', Arr::get($value, 'LASTEVENT', 0)/1000);
							echo '</td>';
							if(Arr::get($value, 'INSERTTIME'))
							{	
								echo '<td>'.date ('d.m.Y H:i:s', strtotime(Arr::get($value, 'INSERTTIME'))).'</td>';
							} else {
								echo '<td>Нет данных.</td>';
							}
						echo '<td>'.'<label class="btn btn-line dark btn-xs popup-contact" for="modalm-1" 
							org_name="'. iconv('windows-1251','UTF-8',Arr::get($value, 'NAME')).'" 
							org_id="'.Arr::get($value, 'ID_DEV', -1).'"
							org_id1="'.Arr::get($value, 'ID_DEV', -1).'"
							ip_dev="'.long2ip(Arr::get($value, 'IP', 0)).'"
							login="'.(Arr::get($value, 'LOGIN', 0)).'"
							pass="'.(Arr::get($value, 'PASS', 0)).'"
							>Редактировать</label></td>';
							
						
					
					
					
					echo '</tr>';
					
					}
				}
				?>
				</tbody>
			</table>
			
			<?php
			
			echo Form::close();
			
	?>
		</div>
	
</div>
<?php 
	echo Kohana::version();
	echo phpversion();
echo '<br>';
	Profiler::stop($token);
	echo __('Время выполнения :time сек.', array(':time'=> number_format (Arr::get(Arr::get(Profiler::stats(array($token)), 'average'), 'time'), 3, ',', ' ')));

?>
</div>



<div class="modalm">


	<div class="panel-body">
	<input class="modalm-open" id="modalm-1" type="checkbox" hidden>
	<div class="modalm-wrap" aria-hidden="true" role="dialog">
		
		<div class="modalm-dialog">
			<div class="modalm-header">
				<h2>Редактирование параметров панели bas-ip</h2>
				<label class="btnm-close" for="modalm-1" aria-hidden="true">x</label>
			</div>
			<div class="modalm-body">
			<h2>Укажите IP адрес для панели</h2>
			
				<div class="row">
					<div class="kartka">
					  <h1></h1>
					  с адресом  
					  <h2></h2>
					  
					  </div>
			</div>
			
			<?php
 /*			echo Debug::vars('134', $deviceList);
			$patter_IP='^(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$';// шаблон IP адреса
			 */
			?>
			
			<form action="bas/control_no_model" method="post" enctype="multipart/form-data">
					Изменения IP
					<h4></h4>
					<input type="input" name="new_IP" id="ipp" pattern="^(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$" >
					<input type="hidden" name="id_dev" id="id_org1" >
					<input type="hidden" name="todo" value="bas_changeIP2" >
					<input type="submit" name="submit" value="Новый IP адрес">
			</form> 
			 <br>
			  
			<form action="bas/update" method="post" enctype="multipart/form-data">	 
					Изменения Логина и Пароля
					<h4></h4>
				 Логин <input type="text" name="login" id="login1" placeholder="Логин">
				 <h4></h4>
				 Пароль <input type="password" name="password" id="pass1" placeholder="Пароль">

				 <input type="hidden" name="id_dev" id="id_org2" >
				 <input type="hidden" name="todo" value="bas_changeIP2" >
				 <input type="submit" name="submit" value="Новый Логин и Пароль">
				
			</form> 
			</div>
			<div class="modalm-footer">
				<h4>Редактирование свойств панели bas-IP</h4>
				
			</div>
		</div>
	</div>
	</div>

</div>


	