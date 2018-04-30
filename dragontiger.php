<?php
$table="dragontigerdb";	//ตั้งค่าตัวแปร
$max=200;
$access_token = 'E59LsP4Qyl9C7k4ndzuHaZHBhtXbbXFkV0bcbQntePoucQvo2s+Z25zHbHjqKmx4kImxIv/6VHV1hVxPLmyRGNAyFGUqp3Ag/6VMM74Y4ndtc3FG09EJHTT3jesHlLHmbLlegNIav/FWweB2zx0MAgdB04t89/1O/w1cDnyilFU=';

$content = file_get_contents('php://input');		//รับค่าจากล่องข้อความใน line
$events = json_decode($content, true);			//แก้ระหัส json ที่ line ส่งมา
require('function.php');				//เรียกฟังก์ชั่นที่สร้างไว้
if (!is_null($events['events'])) {			//ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
	foreach ($events['events'] as $event) {		//วนลูปใน array ทีละตัว
		$replyToken = $event['replyToken'];	//รับค่า  replyToken เพื่อรอส่งกลับ
		$text=$event['message']['text'];	//รับค่าข้อความที่ส่งเข้ามาในตัวแปร text
		$type=$event['message']['type'];	//รับค่า type
		$text=clear($text);					//กำจัดเว้นวรรคและการเอ็นเตอร์
		$lineid=$event['source']['userId'];	//รับค่า userId จากไลน์เก็บไว้ในตัวแปร lineid
		$groupid=$event['source']['groupId'];//รับค่า groupId จากไลน์เก็บไว้ในตัวแปร groupid
		$wrong=" ❌รูปแบบการแทงผิด❌";							//สร้างข้อความถ้าเกินพิมพ์พนันผิด
		
		$url='https://api.line.me/v2/bot/profile/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile
		$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
		$ch = curl_init($url);					//เริ่ม curl
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
		//curl_setopt($ch, CURLOPT_PROXY, $proxy);			//ตั้งความปลอดภัย
		//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
		$result = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร result
		$res=json_decode($result,true);				//แก้ json เป็น array
		if($res['userId']==''){
			$url='https://api.line.me/v2/bot/group/'.$groupid.'/member/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile แบบ group
			$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
			$ch = curl_init($url);					//เริ่ม curl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
			//curl_setopt($ch, CURLOPT_PROXY, $proxy);			//ตั้งความปลอดภัย
			//curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyauth);
			$result = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร result
			$res=json_decode($result,true);				//แก้ json เป็น array
		}
		$id=getid($lineid,$table);					//เรียกฟังก์ชั่น เพื่อหา id จาก lineid
		$netid=getid($lineid,'money');		//รับ id ค่าเงิน
		$status=select(1,$table,"status");	//รับค่า status
		$lap=select(1,$table,"NET");	//รับค่ารอบที่
		$admin=select($id,$table,"admin"); //รับค่า admin
		$sarop=select(1,$table,"admin");	//รับค่าว่าสรุปยัง
		$poll=select(1,$table,'LineID');		//รับค่าผลการสรุป
		$net=select($netid,'money','NET');	//รับค่าเงินที่มีอยู่
		update($id,$table,'NET',$net);		//update ค่าเงิน
		$arr=str_split($text);						//แยกการรับค่ามาทีละตัวเก็บไว้ใน arr
		$arr1=explode('-',$text);
		
		if($text=='ox'||$text=='Ox'){
			$replytext="groupId:$groupid
lineId:$lineid";
		}
		if(($arr[0]=="t"||$arr[0]=="T"||$arr[0]=="d"||$arr[0]=="D"||$arr1[0]=="td"||$arr1[0]=="Td")&&$status==2&&$id==0){
			$replytext="คุณ ".$res['displayName']." กรุณาพิม play";
		}
		//ขึ้นด้วย t คือเริ่มแทง
		elseif(($arr[0]=="t"||$arr[0]=="T"||$arr[0]=="d"||$arr[0]=="D"||$arr1[0]=="td"||$arr1[0]=="Td")&&$status==2){
			$var=explode('-',$text);
			//ถ้ายังไม่ลงทะเบียนให้บอทแจ้งทุกข้อความ ว่าให้ลงทะเบียนก่อน
			if($net>=$var[1]+sum($id,$table)){
				$ans=updatedragontiger($id,$text);
				if($ans==1){
					$replytext='❌คุณ '.$res['displayName']." รูปแบบการแทงผิด❌";
				}
				else{
					$replytext=check($id);
				}
			}
			else{
				$replytext='❌คุณ '.$res['displayName']." ยอดเงินไม่พอ คงเหลือ $net บาท❌";
			}
		}
		elseif(($arr[0]=="t"||$arr[0]=="T"||$arr[0]=="d"||$arr[0]=="D"||$arr1[0]=="td"||$arr1[0]=="Td")&&$status==1){
			$replytext="❌ปิดรอบแล้ว❌";
		}
		//ขึ้นด้วย x คือลบออก
		if(($arr[0]=="x"||$arr[0]=="X")&&$status==2){
			$var=substr($text,1);
			$ans=cancledragontiger($id,$var);
			if($ans==1){
				$replytext='คุณ '.$res['displayName']." ❌รูปแบบการแทงผิด❌";
			}
			else{
				$replytext=check($id);
			}
		}
		elseif(($arr[0]=="x"||$arr[0]=="X")&&$status==1){
			$replytext="❌ปิดรอบแล้ว❌";
		}
		//check
		if($text=='check'||$text=='Check'){
			$replytext=check($id);
		}
		//check ยอดเงินปัจจุบัน
		if($text=='@id'){
			$replytext="คุณ ".$res['displayName']." ยอดเงินปัจจุบัน $net บาท";
		}
		
		//คำสั่งเฉพาะ admin
		if($admin==1){
			//ถ้าพิมมาว่า ปรับ ให้อัพเดทชื่อทุกคน
			if($text=="ปรับ"){								
				config($access_token,$groupid,$table);
				$replytext="อัพเดทชื่อสำเร็จ";
			}
			//เซ็ตรอบแรก
			if(($text=="op"||$text=="Op")&&$status==0){
				update(1,$table,"status",1);
				update(1,$table,"NET",1);
				update(1,$table,'admin',0);
				$replytext="เตรียมเริ่มรอบแรก...";
			}
			elseif($text=="op"||$text=="Op"){
				$replytext='คุณได้เปิดรอบไปแล้ว❌';
			}
			//ปิดรอบ
			if(($text=='End'||$text=='end')&&$status==1&&$poll==0){
				update(1,$table,'status',0);
				$replytext='ปิดรอบทั้งหมด...';
			}
			elseif(($text=='End'||$text=='end')&&$status==0){
				$replytext='ใช้คำสั่ง op เพื่อเปิดรอบ❌';
			}
			elseif(($text=='End'||$text=='end')){
				$replytext='กรุณาปิดรอบและสรุปผลก่อน❌';
			}
			//เปิดรอบ ใช้ช่อง net เป็นช่องนับรอบ
			if($type=='sticker'&&$status==0){
				$replytext='กรุณาเปิดรอบก่อน❌';
			}
			elseif($type=="sticker"&&$status==1){
				if($sarop==0){
					$manymessage=2;
					update(1,$table,"status",2);
					update(1,$table,"admin",1);
					$replytext1='♠️♥️เฮงๆรวย♦️♣️';
					$replytext2="เปิดรอบที่".$lap';
				}
				else{
					$replytext='❌กรุณาสรุปผลก่อน❌';
				}
			}
			//ปิดรอบ
			if($type=="sticker"&&$status==2){
				$manymessage=2;
				update(1,$table,"status",1);
				$replytext1='♠️♥️กติกา♦️♣️
สมัครพิมพ์ play
พิมพ์ T12-200 คือ แทงขา 1 และขา 2 200 จำนวนเงิน
แทงขั้นต่ำขาละ 50-200 บาท
เติมเงินรอบแรกของวันรับ 20% เติม500 บทขึ้นไปลุ้นจับทองปิดบ้าน
ฝากขั้นต่ำ 200 บาท
  กสิกรไทย xxx-xxx-xxxx
  พร้อมเพย์ xxx-xxx-xxxx
🚩ถอนเงินหลัง live จบ ทุกๆ 2ชั่วโมง';				
				$replytext2="ปิดรอบที่ ".$lap;
			}
			
			//check ผล
			if(($arr[0]=="s"||$arr[0]=="S")&&$status==1){
				$var1=substr($text,1);
				$var=explode(',',$var1);
				$tfront=substr($var[0],0,1);
				$nfront=substr($var[0],1);
				$tback=substr($var[1],0,1);
				$nback=substr($var[1],1);
				if($tfront==1){
					$tfront='ดำ⚫';
				}
				elseif($tfront==2){
					$tfront='แดง🔴';
				}
				if($tback==1){
					$tback='ดำ⚫';
				}
				elseif($tback==2){
					$tback='แดง🔴';
				}
				$replytext="ยืนยันสรุป @ok หรือ Clear เพื่อสรุปผลไหม่";
				update(1,$table,'LineID',$var1);
			}
			elseif(($arr[0]=="s"||$arr[0]=="S")&&$status==2){
				$replytext='❌กรุณาปิดรอบก่อน❌';
			}
			//@ok
			if($text=='@ok'&&$poll!=0){
				$replytext=resultdragontiger($poll);
				update($netid,'money','NET',select($id,$table,'NET'));
				update(1,$table,'LineID',0);
				update(1,$table,'NET',$lap+1);
				update(1,$table,'admin',0);
			}
			elseif($text=='@ok'&&$poll==0){
				$replytext='❌สรุปผิด กรุณาสรุปใหม่❌';
			}
		}
		//คำสั่งลงทะเบียน
		if(($text=="play"||$text=='Play')&&$id==0){
			if($res['displayName']!=''){
				$var=codename($res['displayName']);
				insert($table,$var,$lineid,0);
				if($netid==0){
					insert('money',$var,$lineid,0);
					create($lineid);
				}
				$replytext="คุณ ".$res['displayName']." ลงทะเบียนแล้ว";
			}
			else{
				$replytext="ไม่สามารถลงทะเบียนได้";
			}
		}
		//ถ้าลงทะเบียนแล้วก็ให้บอกว่าพร้อมเล่น
		elseif(($text=="play"||$text=='Play')&&$id!=0){
			$replytext="คุณ ".$res['displayName']." พร้อมเล่นแล้ว";
		}
		//สร้างข้อความตอบกลับ
		$messages = [
			[
			'type' => 'text',
			'text' => $replytext
			]
		];
		if($manymessage==2){
			$messages = [
				[
				'type' => 'text',
				'text' => $replytext1
				],
				[
				'type' => 'text',
				'text' => $replytext2
				]
			];
		}
		$url = 'https://api.line.me/v2/bot/message/reply';		//url สำหรับตอบกลับ
		$data = [
			'replyToken' => $replyToken,				//replayToken ใส่ตรงนี้
			'messages' => $messages,
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
}
