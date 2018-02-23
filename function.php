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
	$sql = "UPDATE $table SET $column='$text' WHERE id=$id";
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
function sum($id){
	global $con;
	$sql="SELECT * FROM highlow WHERE ID=2";
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
function updatedragontiger($id,$column,$bet){
	global $con;
	$sql="SELECT * FROM dragontiger WHERE ID=$id";
	$result=$con->query($sql);
	$row = $result->fetch_assoc();
	update($id,"dragontiger","มังกร",0);
	update($id,"dragontiger","เสือ",0);
	update($id,"dragontiger","เสมอ",0);
	$n=0;
	foreach($row as $i=>$v){
		if($n>=7&&$i==$column&&$row['เสือ']!=0){
			$rtext="คุณ ".json_decode($row["Name"],true)." เปลี่ยนจากแทง เสือ เป็นแทง $i $bet บาท ยอดเดิม $row['NET'] บาท ";
			update($id,"dragontiger",$i,$bet);
		}
		elseif($n>=7&&$i==$column&&$row['มังกร']!=0){
			$rtext="คุณ ".json_decode($row["Name"],true)." เปลี่ยนจากแทง มังกร เป็นแทง $i $bet บาท ยอดเดิม $row['NET'] บาท";
			update($id,"dragontiger",$i,$bet);
		}
		elseif($n>=7&&$i==$column&&$row['เสมอ']!=0){
			$rtext="คุณ ".json_decode($row["Name"],true)." เปลี่ยนจากแทง เสมอ เป็นแทง $i $bet บาท ยอดเดิม $row['NET'] บาท";
			update($id,"dragontiger",$i,$bet);
		}
		elseif($n>=7&&$i==$column&&($v==0||$v==$bet)){
			$rtext="คุณ ".json_decode($row["Name"],true)." แทง $i $bet บาท ยอดเดิม $row['NET'] บาท";
			update($id,"dragontiger",$i,$bet);
		}
		$n++;
	}
	update($id,'dragontiger','play',1);
	return $rtext;
}
function resultdragontiger($f,$s){
	global $con;
	$sql="SELECT * FROM dragontiger";
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
			foreach($row as $i=>$v){
				if($n==7&&$v!=0){
					if($arr[0]==1){
						$money+=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				elseif($n==8&&$v!=0){
					if($arr[1]==1){
						$money+=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				elseif($n==9&&$v!=0){
					if($arr[2]==1){
						$money+=$v*8;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." ได้ $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
					else{
						$money-=$v;
						$row['NET']+=$money;
						update($row['ID'],'dragontiger','NET',$row['NET']);
						$rtext.="คุณ ".json_decode($row['Name'],true)." เสีย $money บาท ยอดรวม ".$row['NET'].' บาท
';
					}
				}
				$n++;
			}
			update($id,'dragontiger','play',0);
		}
	}
	$rtext='สรุปรอบที่ '.select(1,'dragontiger','NET').'
'.$rtext;
	return $rtext;
}
