<?php
$servername = "den1.mysql6.gear.host";
$username = "line";
$password = "Og8B4hjP4_2?";
$database = "Line";
$con = new mysqli($servername, $username, $password,$database);
	//ตั้งค่าเพื่อใช้ sql ทั้งโปรแกรมแบบ global
function getid($lineid,$table){ 					//ฟังก์ชั่นหาค่า id จาก lineid
	global $con;
	$sql = "SELECT * FROM $table";			//select ทุกค่าจาก database
	$result = $con->query($sql);			
	while($row=$result->fetch_assoc()){		//ไล่ทุก id
		if($lineid==$row["LineID"]){		//ค้นหา column LineID
			return $row["ID"];				//คืนค่า column ID
			}
		}
	return 0;								//แต่ถ้าวิ่งหาทุก id แล้วไม่เจอ lineid ที่ตรงกัน คืนค่า 0
}
function select($id,$table,$column){		//ฟังก์ชั่นคืนค่าจาก database
	global $con;
	$sql = "SELECT * FROM $table WHERE ID=$id";//select จาก table และ id จากที่รับค่ามา
	$result = $con->query($sql);
	$row=$result->fetch_assoc();
	return $row[$column];					//คืนค่าที่ต้องการ
}
function update($id,$table,$column,$text){	//เปลี่ยนค่าใน database
	global $con;
	$sql = "UPDATE $table SET $column='$text' WHERE ID=$id";
	$con->query($sql);
}
function insert($table,$name,$lineid,$NET){				//เพิ่มค่ามาใน database
	global $con;
	$sql = "INSERT INTO $table (Name, LineID,NET)
	VALUES ('$name','$lineid',$NET)";
	$con->query($sql);
}
function codename($text){				//ฟังก์ชั่นปรับค่าชื่อให้มี \
	$backs=array();						//สร้าง backs ให้เป็น array
	$text2=json_encode($text);			//แปลงค่า text ให้กลายเป็น json
	$arr=str_split($text2);				//แยกส่วน text2 ให้กลายเป็น array ใน arr
	for($n=0;$arr[$n]!='';$n++){		//ไล่ตรวจสอบว่าตัวไหนเป็น \
		if($arr[$n]=="\\"){
			$backs[]=$n;				//ถ้าตัวนั้นเป็น \ ให้เก็บค่าตัวชี้ไว้ใน backs
		}
	}
	$backs[]=count($arr)-1;				//ใส่ค่าตัวชี้สุดท้ายใน backs
	$substr=array();					//กำหนด substr เป็น array
	$substr[]=substr($text2,0,$backs[0]+1);//แยกส่วนตัวแรกก่อน โดยให้ตัวสุดท้ายเป็น \
	for($n=0;$n<=count($backs)-2;$n++){	//แยกส่วนตัวที่เหลือ โดยให้ตัวสุดท้ายเป็น \
		$substr[]=substr($text2,$backs[$n]+1,$backs[$n+1]-$backs[$n]);
	}
	for($n=0;$n<=count($substr)-2;$n++){//ใส่ \ ต่อในแต่ละส่วนแล้วนำมาต่อกัน
		$rtext.=$substr[$n]."\\";
	}
	$rtext.=$substr[count($substr)-1];	//ต่อส่วนสุดท้ายเข้าด้วยกัน
	return $rtext;						//คืนค่าออกมา
}
function config($access_token,$groupid,$table){						//ปรับชื่อทุกคน
	global $con;
	$sql="SELECT * FROM $table";
	$result = $con->query($sql);
	while($row=$result->fetch_assoc()){
		$lineid=$row['LineID'];
		$url='https://api.line.me/v2/bot/profile/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile
		$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
		$ch = curl_init($url);					//เริ่ม curl
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);		//ปลดระบบความปลอดภัย
		$res = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร res
		$r=json_decode($res,true);				//แก้ json เป็น array
		if($r['displayName']==''){
			$url='https://api.line.me/v2/bot/group/'.$groupid.'/member/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile แบบ group
			$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
			$ch = curl_init($url);					//เริ่ม curl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);		//ปลดระบบความปลอดภัย
			$res = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร res
			$r=json_decode($res,true);				//แก้ json เป็น array
		}
		update($row['ID'],$table,"Name",codename($r['displayName']));
	}
}
function clear($text){   //ลบอักขระช่องว่างและอักขระเอ็นเตอร์
	$search=array(" ","
");
	$reply=str_replace($search,"",$text);
	return $reply;
}
function sum($id,$table){
	global $con;
	$sql="SELECT * FROM $table WHERE ID=$id";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$sum=0;
	$n=0;
	foreach($row as $v){
		if($n>=7){
			$sum+=$v;
		}
		$n++;
	}
	return $sum;
}
function updatedragontiger($id,$text){
	$table='dragontigerdb';
	$var=explode('-',$text);
	$front=$var[0];
	$bet=$var[1];
	update($id,$table,'play',1);
	if($front=='t'||$front=='T'){
		update($id,$table,'เสือ',$bet);
	}
	elseif($front=='t1'||$front=='T1'){
		update($id,$table,'เสือคี่',$bet);
	}
	elseif($front=='t2'||$front=='T2'){
		update($id,$table,'เสือคู่',$bet);
	}
	elseif($front=='tb'||$front=='Tb'){
		update($id,$table,'เสือดำ',$bet);
	}
	elseif($front=='tr'||$front=='Tr'){
		update($id,$table,'เสือแดง',$bet);
	}
	elseif($front=='d'||$front=='D'){
		update($id,$table,'มังกร',$bet);
	}
	elseif($front=='d1'||$front=='D1'){
		update($id,$table,'มังกรคี่',$bet);
	}
	elseif($front=='d2'||$front=='D2'){
		update($id,$table,'มังกรคู่',$bet);
	}
	elseif($front=='db'||$front=='Db'){
		update($id,$table,'มังกรดำ',$bet);
	}
	elseif($front=='dr'||$front=='Dr'){
		update($id,$table,'มังกรแดง',$bet);
	}
	elseif($front=='td'||$front=='Td'){
		update($id,$table,'เสมอ',$bet);
	}
	else{
		return 1;
	}
	if($bet==0){
		return 1;
	}
}
function cancledragontiger($id,$front){
	$table='dragontigerdb';
	$bet=0;
	if($front==1){
		update($id,$table,'เสือ',$bet);
	}
	elseif($front==11){
		update($id,$table,'เสือคี่',$bet);
	}
	elseif($front==12){
		update($id,$table,'เสือคู่',$bet);
	}
	elseif($front==13){
		update($id,$table,'เสือดำ',$bet);
	}
	elseif($front==14){
		update($id,$table,'เสือแดง',$bet);
	}
	elseif($front==2){
		update($id,$table,'มังกร',$bet);
	}
	elseif($front==21){
		update($id,$table,'มังกรคี่',$bet);
	}
	elseif($front==22){
		update($id,$table,'มังกรคู่',$bet);
	}
	elseif($front==23){
		update($id,$table,'มังกรดำ',$bet);
	}
	elseif($front==24){
		update($id,$table,'มังกรแดง',$bet);
	}
	elseif($front==3){
		update($id,$table,'เสมอ',$bet);
	}
	else{
		return 1;
	}
}
function resultdragontiger($text){
	global $con;
	$table='dragontigerdb';
	$sql="SELECT * FROM $table";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$var=explode(',',$text);
	$tfront=substr($var[0],0,1);
	$nfront=substr($var[0],1);
	$tback=substr($var[1],0,1);
	$nback=substr($var[1],1);
	$num=array(1,2,3,4,5,6,7,8,9,10,11,12,13);
	$res1=array('เสือ'=>0,'มังกร'=>0);
	$res5=array('เสือคู่'=>0,'มังกรคู่'=>0);
	$res7=array('เสือคี่'=>0,'มังกรคี่'=>0);
	$res9=array('เสือดำ'=>0,'เสือแดง'=>0,'มังกรดำ'=>0,'มังกรแดง'=>0);
	$res8=array('เสมอ'=>0);
	if(in_array($nfront,$num)&&in_array($nback,$num)&&($tfront==1||$tfront==2)&&($tback==1||$tback==2)){
		//check เสือ มังกร เสมอ
		if($nfront>$nback){
			$res1['เสือ']=1;
		}
		elseif($nfront<$nback){
			$res1['มังกร']=1;
		}
		else{
			$res8['เสมอ']=1;
		}
		//check เสือคู่ เสือคี่
		if($nfront%2==0){
			$res5['เสือคู่']=1;
		}
		else{
			$res7['เสือคี่']=1;
		}
		//check มังกรคู่ มังกรคี่
		if($nback%2==0){
			$res5['มังกรคู่']=1;
		}
		else{
			$res7['มังกรคี่']=1;
		}
		//check เสือดำ เสือแดง
		if($tfront==1){
			$res9['เสือดำ']=1;
		}
		else{
			$res9['เสือแดง']=1;
		}
		//check มังกรดำ มังกรแดง
		if($tback==1){
			$res9['มังกรดำ']=1;
		}
		else{
			$res9['มังกรแดง']=1;
		}
		//คำนวณผลส่งออกในตัวแปร reply
		$reply='สรุปผลรอบที่ '.$row['NET'];
		while($row=$result->fetch_assoc()){
			if($row['play']==1){
				$money=0;
				//คิดเงินฝั่งถูก
				foreach($res1 as $n=>$v){
					if($v==1&&$row[$n]!=0){
						$money+=$row[$n];
						$row[$n]=0;
					}
				}
				foreach($res5 as $n=>$v){
					if($v==1&&$row[$n]!=0){
						$money+=$row[$n]*1.05;
						$row[$n]=0;
					}
				}
				foreach($res7 as $n=>$v){
					if($v==1&&$row[$n]!=0){
						$money+=$row[$n]*0.75;
						$row[$n]=0;
					}
				}
				foreach($res9 as $n=>$v){
					if($v==1&&$row[$n]!=0){
						$money+=$row[$n]*0.9;
						$row[$n]=0;
					}
				}
				foreach($res8 as $n=>$v){
					if($v==1&&$row[$n]!=0){
						$money+=$row[$n]*8;
						$row[$n]=0;
					}
				}
				//คิดเงินฝั่งผิด
				foreach($res1 as $n=>$v){
					if($row[$n]!=0&&$res8['เสมอ']==1){
						$money-=$row[$n]/2;
						$row[$n]=0;
					}
					elseif($row[$n]!=0){
						$money-=$row[$n];
						$row[$n]=0;
					}
				}
				foreach($res5 as $n=>$v){
					if($row[$n]!=0){
						$money-=$row[$n];
						$row[$n]=0;
					}
				}foreach($res7 as $n=>$v){
					if($row[$n]!=0){
						$money-=$row[$n];
						$row[$n]=0;
					}
				}
				foreach($res9 as $n=>$v){
					if($row[$n]!=0){
						$money-=$row[$n];
						$row[$n]=0;
					}
				}
				foreach($res8 as $n=>$v){
					if($row[$n]!=0){
						$money-=$row[$n];
						$row[$n]=0;
					}
				}
				$money=number_format($money);
				//ส่งไปเก็บค่าใน database
				update($row['ID'],'money','NET',($row['NET']+$money));
				//เปลี่ยนเป็น 0
				update($row['ID'],$table,'play',0);
				//สร้างข้อความสรุปผล
				if($money>0){
					$reply.='
คุณ '.json_decode($row['Name'],true)." +$money = ".($row['NET']+$money).' บาท';
				}
				elseif($money<0){
					$reply.='
คุณ '.json_decode($row['Name'],true)." $money = ".($row['NET']+$money).' บาท';
				}
				else{
					$reply.='
คุณ '.json_decode($row['Name'],true)." ไม่ได้ไม่เสีย = ".($row['NET']+$money).' บาท';
				}
				cleartable($row['ID']);
			}
		}
	}
	else{
		$reply='❌สรุปผิด กรุณาสรุปใหม่❌';
	}
	return $reply;
}
function sendline($lineid,$access_token,$messages){
	$sent=[
			[
				'type' => 'text',
				'text' => $messages
			]
		];
	$url = 'https://api.line.me/v2/bot/message/push';		//url สำหรับตอบกลับ
	$data = [
		'to' => $lineid,				//replayToken ใส่ตรงนี้
		'messages' => $sent,
	];
	$post = json_encode($data);						//web สำหรับตอบกลับ
	$headers = array('Content-Type: application/json', 'Authorization: Bearer ' . $access_token);
													//headers สำหรับตอบกลับ
	$ch = curl_init($url);							//เริ่ม curl 
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");//ปรับเป็นแบบ post
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);	//ใส่ข้อความที่จะส่ง
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);	//ส่ง header
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);	
	curl_exec($ch);									//ส่งไปให้ไลน์ตอบกลับ
}
function cleartable($id){
	global $con;
	$sql = "UPDATE dragontigerdb SET เสือ=0,เสือคู่=0,เสือคี่=0,เสือดำ=0,เสือแดง=0,มังกร=0,มังกรคู่=0,มังกรคี่=0,มังกรดำ=0,มังกรแดง=0,เสมอ=0 WHERE ID=$id";
	$con->query($sql);
}
function check($id){
	global $con;
	$table='dragontigerdb';
	$sql="SELECT * FROM $table WHERE ID=$id";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$reply='คุณ '.json_decode($row['Name'],true).'
';
	$n=0;
	$res=array('🐯เสือ(1)','☘️คี่ ฝั่ง 🐯เสือ(11)','🍀คู่ ฝั่ง 🐯เสือ(12)','⚫ดำ ฝั่ง 🐯เสือ(13)','🔴แดง ฝั่ง 🐯เสือ(14)','🐉มังกร(2)','☘️คี่ ฝั่ง 🐉มังกร(21)','🍀คู่ ฝั่ง 🐉มังกร(22)','⚫ดำ ฝั่ง 🐉มังกร(23)','🔴แดง ฝั่ง 🐉มังกร(24)','⚖️เสมอ(3)');
	foreach($row as $v){
		if($v!=0&&$n>=7){
			$reply.='➡️แทง '.$res[$n-7]." $v บาท
";
		}
		$n++;
	}
	$reply.='คงเหลือ '.$row['NET'].' บาท';
	return $reply;
}
