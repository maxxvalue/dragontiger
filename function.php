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
	$sql="SELECT * FROM $table WHERE ID=2";
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
	global $con;
	$table='dragontigerdb';
	$sql="SELECT * FROM dragontigerdb WHERE ID=$id";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$arr=explode('-',$text);
	$arr_f=str_split($arr[0]);
	$f1=substr($arr[0],0,1);
	$f2=substr($arr[0],1,9);
	$f3=substr($arr[0],10);
	$var_b=explode(',',$arr[1]);
	$b1=$var_b[0];
	$b2=$var_b[1];
	$b3=$var_b[2];
	$reply="";
	$var0=array('0',1,2);
	$right=0;
	if($row['play']!=0&&in_array($arr[0],$var0)&&$arr[1]!=''){
		cleartable($id);
	}
	if($f1==1&&$b1!=''){////
		update($id,$table,'play',1);
		$reply.="➡️แทง 🐯เสือ $b1 บาท
";
		update($id,$table,'เสือ',$b1);
		if($f2=="คู่"&&$b2!=''){////
			$reply.="➡️แทง ♊คู่ ฝั่ง 🐯เสือ $b2 บาท
";
			update($id,$table,'เสือคู่',$b2);
			if($f3=="ดำ"&&$b3!=''){////
				$reply.="➡️แทง ⚫ดำ ฝั่ง 🐯เสือ $b3 บาท
";
				update($id,$table,'เสือดำ',$b3);
			}
			elseif($f3=="แดง"&&$b3!=''){////
				$reply.="➡️แทง 🔴แดง ฝั่ง 🐯เสือ $b3 บาท
";
				update($id,$table,'เสือแดง',$b3);
			}
		}
		elseif($f2=="คี่"&&$b2!=''){////
			$reply.="➡️แทง 🔯คี่ ฝั่ง 🐯เสือ $b2 บาท
";
			update($id,$table,'เสือคี่',$b2);
			if($f3=="ดำ"&&$b3!=''){////
				$reply.="➡️แทง ⚫ดำ ฝั่ง 🐯เสือ $b3 บาท
";
				update($id,$table,'เสือดำ',$b3);
			}
			elseif($f3=="แดง"&&$b3!=''){////
				$reply.="➡️แทง 🔴แดง ฝั่ง 🐯เสือ $b3 บาท
";
				update($id,$table,'เสือแดง',$b3);
			}
		}
	}
	elseif($f1==2&&$b1!=''){////
		update($id,$table,'play',1);
		$reply.="➡️แทง 🐲มังกร $b1 บาท
";
		update($id,$table,'มังกร',$b1);
		if($f2=="คู่"&&$b2!=''){////
			$reply.="➡️แทง ♊คู่ ฝั่ง 🐲มังกร $b2 บาท
";
			update($id,$table,'มังกรคู่',$b2);
			if($f3=="ดำ"&&$b3!=''){////
				$reply.="➡️แทง ⚫ดำ ฝั่ง 🐲มังกร $b3 บาท
";
				update($id,$table,'มังกรดำ',$b3);
			}
			elseif($f3=="แดง"&&$b3!=''){////
				$reply.="➡️แทง 🔴แดง ฝั่ง 🐲มังกร $b3 บาท
";
				update($id,$table,'มังกรแดง',$b3);
			}
		}
		elseif($f2=="คี่"&&$b2!=''){////
			$reply.="➡️แทง 🔯คี่ ฝั่ง 🐲มังกร $b2 บาท
";
			update($id,$table,'มังกรคี่',$b2);
			if($f3=="ดำ"&&$b3!=''){////
				$reply.="➡️แทง ⚫ดำ ฝั่ง 🐲มังกร $b3 บาท
";
				update($id,$table,'มังกรดำ',$b3);
			}
			elseif($f3=="แดง"&&$b3!=''){////
				$reply.="➡️แทง 🔴แดง ฝั่ง 🐲มังกร $b3 บาท
";
				update($id,$table,'มังกรแดง',$b3);
			}
		}
	}
	elseif($f1==3&&$b1!=''){
		update($id,$table,'play',1);
		$reply.="➡️แทง ⚖️เสมอ $b1 บาท
";
		update($id,$table,'เสมอ',$b1);
	}
	else{
		$reply="❌รูปแบบการแทงผิด❌";
	}
	$reply="คุณ ".json_decode($row['Name'],true).'
'.$reply.'คงเหลือ '.$row['NET'].' บาท';
	return $reply;
}
function resultdragontiger($f,$s){
	global $con;
	$sql="SELECT * FROM dragontigerdb";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$arr=array("0","0","0");
	$money=0;
	$rtext="";
	if($f>$s){
		$arr[0]=1;
	}
	elseif($f<$s){
		$arr[1]=1;
	}
	else{
		$arr[2]=1;
	}
	while($row = $result->fetch_assoc()){
		if($row['play']==1){
			$n=0;
			foreach($row as $v){
				if($n==7&&$v!=0){
					if($arr[0]==1){
						$money+=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','เสือ',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','เสือ',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				elseif($n==8&&$v!=0){
					if($arr[1]==1){
						$money+=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','มังกร',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','มังกร',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				elseif($n==9&&$v!=0){
					if($arr[2]==1){
						$money+=$v*8;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','เสมอ',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontigerdb','NET',$row['NET']);
						update($row['ID'],'dragontigerdb','เสมอ',0);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				$n++;
			}
			update($row['ID'],'dragontigerdb','play',0);
		}
	}
	$rtext='สรุปรอบที่ '.select(1,'dragontigerdb','NET').'
'.$rtext;
	return $rtext;
}
function conclude(){
	global $con;
	$sql="SELECT * FROM dragontigerdb";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	$tiger=0;
	$dragon=0;
	$tie=0;
	while($row = $result->fetch_assoc()){
		foreach($row as $n=>$v){
			if($n=='เสือ'){
				$tiger+=$v;
			}
			if($n=='มังกร'){
				$dragon+=$v;
			}
			if($n=='เสมอ'){
				$tie+=$v;
			}
		}
	}
	update(1,'dragontigerdb','เสือ',$tiger);
	update(1,'dragontigerdb','มังกร',$dragon);
	update(1,'dragontigerdb','เสมอ',$tie);
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
