<?php
$table="dragontigerdb";	//ตั้งค่าตัวแปร
$max=200;
$access_token = 'SIkXpp6ylNgQ42HEg2wkOD9hG7YRkzHbsORCxZ3p9iWLynhB9/t7hD9zdoMdUbXYqaibnclBOqZUfj+jLpWyuwWRGpvWd2hMaZvRf+2LyXbbRi9SDZrlRfGcWm6w3ry+fatBxeFmvx4S0zjEzvvFFAdB04t89/1O/w1cDnyilFU=';

$content = file_get_contents('php://input');		//รับค่าจากล่องข้อความใน line
$events = json_decode($content, true);			//แก้ระหัส json ที่ line ส่งมา
require('function.php');				//เรียกฟังก์ชั่นที่สร้างไว้
if (!is_null($events['events'])) {			//ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
	foreach ($events['events'] as $event) {		//วนลูปใน array ทีละตัว
		$replyToken = $event['replyToken'];	//รับค่า  replyToken เพื่อรอส่งกลับ
		$text=$event['message']['text'];	//รับค่าข้อความที่ส่งเข้ามาในตัวแปร text
		$text=clear($text);					//กำจัดเว้นวรรคและการเอ็นเตอร์
		$lineid=$event['source']['userId'];	//รับค่า userId จากไลน์เก็บไว้ในตัวแปร lineid
		$groupid=$event['source']['groupId'];//รับค่า groupId จากไลน์เก็บไว้ในตัวแปร groupid
		$wrong=" รูปแบบการแทงผิด";							//สร้างข้อความถ้าเกินพิมพ์พนันผิด
		
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
		$arr=str_split($text);						//แยกการรับค่ามาทีละตัวเก็บไว้ใน arr
		//คำสั่งลงทะเบียน
		if(($text=="play"||$text=='Play')&&$id==0){
			$var=codename($res['displayName']);
			insert($table,$var,$lineid,1000);
			$replytext="คุณ ".$res['displayName']." ลงทะเบียนแล้ว";
		}
		//ถ้าลงทะเบียนแล้วก็ให้บอกว่าพร้อมเล่น
		elseif(($text=="play"||$text=='Play')&&$id!=0){
			$replytext="คุณ ".$res['displayName']." พร้อมเล่นแล้ว";
		}
		//ถ้ายังไม่ลงทะเบียนให้บอทแจ้งทุกข้อความ ว่าให้ลงทะเบียนก่อน
		elseif($id==0){
			$replytext="คุณ ".$res['displayName']." กรุณาพิม play";
		}
		if(($arr[0]=="t"||$arr[0]=="T")&&select(1,$table,"status")==1){
			//ขึ้นด้วย t คือเริ่มแทง
			$var=substr($text,1);
			$replytext=updatedragontiger($id,$var);
		}
		
		//ส่งข้อความให้ admin
		/*if(select(1,$table,'status')==1){
			conclude();
			$admintext="เสือ: ".select(1,$table,'เสือ').' มังกร: '.select(1,$table,'มังกร').' เสมอ: '.select(1,$table,'เสมอ');
			sendline('Ucb19a6fc85ac19afe4a2247ad4c944f0',$access_token,$admintext);
		}*/
		
		//คำสั่งเฉพาะ admin
		if(select($id,$table,"admin")==1){				
			//ถ้าพิมมาว่า ปรับ ให้อัพเดทชื่อทุกคน
			if($text=="ปรับ"){								
				config($access_token,$groupid,$table);
				$replytext="อัพเดทชื่อสำเร็จ";
			}
			//เปิดรอบ ใช้ช่อง net เป็นช่องนับรอบ
			if($text=="เปิด"&&select(1,$table,"status")==0){
				$manymessage=2;
				update(1,$table,"status",1);
				$replytext1='🎲🎲กติกาเสือมังกร🎲🎲
☑️พิมพ์ พร้อม เพื่อลงทะเบียนก่อน☑️
พิม b ตามด้วยตัวที่ต้องการจะเล่นต่อด้วยขีด (-) แล้วตามด้วยจำนวนเงิน
♠️♥️วิธีนับแต้ม♦️♣️
วัดผลเพียงแต้มเท่านั้น ไม่นำดอกเข้ามาเกี่ยวของ โดยไพ่ที่คะแนนต่ำสุดคือ A และคะแนนสูงสุดคือ K
🐯1 = เสือ จ่าย 1 ต่อ
🐉2 = มังกร จ่าย 1 ต่อ
⚖️3 = เสมอ จ่าย 8 ต่อ';
				$replytext2="เปิดรอบที่ ".select(1,$table,"NET");
			}
			//ปิดรอบ
			if($text=="ปิด"&&select(1,$table,"status")==1){
				$manymessage=2;
				update(1,$table,"status",0);
				$replytext1='🎲🎲กติกาเสือมังกร🎲🎲
☑️พิมพ์ พร้อม เพื่อลงทะเบียนก่อน☑️
พิม b ตามด้วยตัวที่ต้องการจะเล่นต่อด้วยขีด (-) แล้วตามด้วยจำนวนเงิน 
♠️♥️วิธีนับแต้ม♦️♣️
วัดผลเพียงแต้มเท่านั้น ไม่นำดอกเข้ามาเกี่ยวของ โดยไพ่ที่คะแนนต่ำสุดคือ A และคะแนนสูงสุดคือ K
🐯1 = เสือ จ่าย 1 ต่อ
🐉2 = มังกร จ่าย 1 ต่อ
⚖️3 = เสมอ จ่าย 8 ต่อ';
				$replytext2="ปิดรอบที่ ".select(1,$table,"NET");
				update(1,$table,"NET",select(1,$table,"NET")+1);
			}
			//เซ็ตรอบแรก
			if($text=="เริ่ม"){
				update(1,$table,"status",0);
				update(1,$table,"NET",1);
				$replytext="เตรียมเริ่มรอบแรก";
			}
			
			//check ผล
			$number=array(1,2,3,4,5,6,7,8,9,10,11,12,13);
			if($arr2[0]=="ผล"&&select(1,$table,"status")==0){
				$num=array('0','1','2','3','4','5','6','7','8','9');
				$arr=explode(',',$text);
				$var_f=substr($arr[0],1);
				$vara_f=str_split($var_f);
				$var_b=$arr[1];
				$vara_b=str_split($var_b);
				foreach($vara_f as $n=>$v){
					if(!in_array($v,$num)){
						$i=$n;
						break;
					}
				}
				$front1=substr($var_f,0,$i);
				$front2=substr($var_f,$i);
				foreach($vara_b as $n=>$v){
					if(!in_array($v,$num)){
						$i=$n;
						break;
					}
				}
				$back1=substr($var_b,0,$i);
				$back2=substr($var_b,$i);
			}
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
