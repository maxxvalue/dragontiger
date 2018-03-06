<?php
$table="potakdb";	//ตั้งค่าตัวแปร
$max=200;
$access_token = '4YlJlgw/oJC3ik03YOZ1RFUBrmTI8UvdlTnXLZ/bC6qyZk7nrEUWkExCHYyIss7CU8ZKk14baE6yh282T7SpO/4tPQDLs3BcBa0OcZkewK2i6N1sGbuZXmFoXwyOrLz7oyeblwRhqsdkTNn2wbkZxQdB04t89/1O/w1cDnyilFU=';

$content = file_get_contents('php://input');		//รับค่าจากล่องข้อความใน line
$events = json_decode($content, true);			//แก้ระหัส json ที่ line ส่งมา
require('function.php');				//เรียกฟังก์ชั่นที่สร้างไว้
if (!is_null($events['events'])) {			//ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
	foreach ($events['events'] as $event) {		//วนลูปใน array ทีละตัว
		$replyToken = $event['replyToken'];	//รับค่า  replyToken เพื่อรอส่งกลับ
		$text=$event['message']['text'];	//รับค่าข้อความที่ส่งเข้ามาในตัวแปร text
		$type=$event['message']['type'];	//รับค่า type
		$arr2=explode(" ",$text);					//แยกเพื่อดูผล
		$text=clear($text);					//กำจัดเว้นวรรคและการเอ็นเตอร์
		$lineid=$event['source']['userId'];	//รับค่า userId จากไลน์เก็บไว้ในตัวแปร lineid
		$groupid=$event['source']['groupId'];//รับค่า groupId จากไลน์เก็บไว้ในตัวแปร groupid
		$wrong=" รูปแบบการแทงผิด❌";							//สร้างข้อความถ้าเกินพิมพ์พนันผิด
		
		$url='https://api.line.me/v2/bot/profile/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile
		$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
		$ch = curl_init($url);					//เริ่ม curl
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
		$result = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร result
		$res=json_decode($result,true);				//แก้ json เป็น array
		if($res['userId']==''){
			$url='https://api.line.me/v2/bot/group/'.$groupid.'/member/'.$lineid;	//กำหนด url เพื่อเตรียมส่งรับค่า profile แบบ group
			$headers=array('Authorization: Bearer ' . $access_token);//กำหนด headers เพื่อเตรียมรับค่า profile
			$ch = curl_init($url);					//เริ่ม curl
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,TRUE);		//ปรับให้เป็นแบบตอบกลับ
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//ตั้งค่า headers
			$result = curl_exec($ch);				//ส่งค่าไปให้ไลน์และรับกลับด้วยตัวแปร result
			$res=json_decode($result,true);				//แก้ json เป็น array
		}
		$id=getid($lineid,$table);					//เรียกฟังก์ชั่น เพื่อหา id จาก lineid
		$netid=getid($lineid,'money');		//รับ id ค่าเงิน
		$net=select($netid,'money','NET');	//ยอดเงินปัจจุบัน
		$status=select(1,$table,"status");	//สถานะรอบ
		$lap=select(1,$table,"NET");	//จำนวนรอบ
		$sarop=select(1,$table,"admin");	//รับค่าว่าสรุปยัง
		$poll=select(1,$table,'LineID');		//รับค่าผลการสรุป
		$bet=select(1,$table,'play');	//รับค่าเงินแต่ละขา
		$arr=str_split($text);						//แยกการรับค่ามาทีละตัวเก็บไว้ใน arr
		//ตัว t นำหน้าแปลว่าต้องการแทง
		$ottf=array(1,2,3,4);
		//ถ้ายังไม่ลงทะเบียนให้บอทแจ้ง ว่าให้ลงทะเบียนก่อน
		if(($arr[0]=="t"||$arr[0]=="T")&&$status==1&&$id==0){
			$replytext="คุณ ".$res['displayName']." กรุณาพิม play";
		}
		elseif(($arr[0]=="t"||$arr[0]=="T")&&$status==1){
			$sum=sum($id,$table);
			$i=0;
			$all=0;
			$btext=substr($text,1);
			$barr=str_split($btext);
			foreach($barr as $v){
				if(!in_array($v,$ottf)){
					$i=1;
				}
				else{
					$all++;
				}
			}
			if($net<($all*$bet)){
				$replytext="❌คุณ ".$res['displayName']." มียอดเงินไม่เพียงพอ คงเหลือ $net บาท❌";
			}
			elseif($i==1){
				$replytext="❌คุณ ".$res['displayName'].$wrong;
			}
			else{
				if($sum!=0){
					clearpotak($id);
				}
				foreach($ottf as $v){
					if($barr[0]==$v||$barr[1]==$v||$barr[2]==$v||$barr[3]==$v){
						update($id,$table,'P'.$v,1);
						update($id,$table,'play',1);
					}
				}
				updatepotak();
				$var=bresult();
				$p1=$var[0];
				$p2=$var[1];
				$p3=$var[2];
				$p4=$var[3];
				if($sum==0){
					$replytext="➡️คุณ ".$res['displayName']." แทงขา $btext ขาละ $bet บาท คงเหลือ $net บาท
1-$p1 | 2-$p2 | 3-$p3 | 4-$p4";
				}
				else{
					$replytext="🔀คุณ ".$res['displayName']." เปลี่ยนเป็นแทงขา $btext ขาละ $bet บาท คงเหลือ $net บาท
1-$p1 | 2-$p2 | 3-$p3 | 4-$p4";
				}
			}
		}
		elseif(($arr[0]=="t"||$arr[0]=="T")&&$status==0){
			$replytext="❌ปิดรอบแล้ว❌";
		}
		//check ตัวที่แทง
		if($text=='check'||$text=='Check'){
			$replytext=check($id,$bet,$net);
		}
		//check ยอดเงินปัจจุบัน
		if($text=='@id'){
			$replytext="คุณ ".$res['displayName']." ยอดเงินปัจจุบัน $net บาท";
		}
		//คำสั่งเฉพาะ admin
		if(select($id,$table,"admin")==1){
			//ถ้าพิมมาว่า ปรับ ให้อัพเดทชื่อทุกคน
			if($text=="ปรับ"){								
				config($access_token,$groupid,$table);
				$replytext="อัพเดทชื่อสำเร็จ";
			}
			//เปิดรอบ ใช้ช่อง net เป็นช่องนับรอบ
			if($type=="sticker"&&$status==0){
				if($sarop==0){
					$manymessage=2;
					update(1,$table,"status",1);
					update(1,$table,"admin",1);
					$replytext1='♠️♥️กติกาโป๊ะแตก♦️♣️
®️พิมพ์ play เพื่อสมัครสมาชิกก่อน®️
พิมพ์ t ตามด้วยขาที่ต้องการแทง โดยจำนวนเงินที่ลงในแต่ละขาจะถูกกำหนดไว้โดยแอดมินดังนั้นไม่ต้องใส่จำนวนเงิน
ขาใดมีแต้มมากที่สุดจะได้รับเงินแทงจากขาอื่น ๆ ทั้งหมด (ถ้าแทงถูก 2 คน ได้รับคนละครึ่ง)';
					$replytext2="เปิดรอบที่ ".$lap;
				}
				else{
					$replytext='❌กรุณาสรุปผลก่อน❌';
				}
			}
			//ปิดรอบ
			if($type=="sticker"&&$status==1){
				$manymessage=2;
				update(1,$table,"status",0);
				$replytext1='♠️♥️กติกาโป๊ะแตก♦️♣️
®️พิมพ์ play เพื่อสมัครสมาชิกก่อน®️
พิมพ์ t ตามด้วยขาที่ต้องการแทง โดยจำนวนเงินที่ลงในแต่ละขาจะถูกกำหนดไว้โดยแอดมินดังนั้นไม่ต้องใส่จำนวนเงิน
ขาใดมีแต้มมากที่สุดจะได้รับเงินแทงจากขาอื่น ๆ ทั้งหมด (ถ้าแทงถูก 2 คน ได้รับคนละครึ่ง)';
				$replytext2="ปิดรอบที่ ".$lap;
			}
			//เซ็ตรอบแรก
			if($text=="op"||$text=="Op"){
				update(1,$table,"status",0);
				update(1,$table,"NET",1);
				update(1,$table,'admin',0);
				$replytext="เตรียมเริ่มรอบแรก";
			}
			
			//ปรับว่าลงตาละเท่าไหร่
			if($arr2[0]=='ขาละ'&&$status==0){
				update(1,$table,'play',$arr2[1]);
				$replytext="ปรับเป็นแทงขาละ $arr2[1] บาท";
			}
			//ปรับว่าหักกี่%
			if($arr2[0]=='%'){
				update(1,$table,'vat',$arr2[1]);
				$replytext="ปรับเป็นหัก $arr2[1]%";
			}
			
			//สรุปผลตอนจบรอบ
			if(($arr[0]=="s"||$arr[0]=="S")&&$status==0){
				$replytext="ยืนยันสรุป ขา $arr[1] มีแต้มมากที่สุดหรือไม่❓";
				update(1,$table,'LineID',$arr[1]);
			}
			elseif(($arr[0]=="s"||$arr[0]=="S")&&$status==1){
				$replytext='❌กรุณาปิดรอบก่อน❌';
			}
			//ยืนยัน
			if($text=="@ok"&&$poll!=0){
				if(in_array($poll,$ottf)){
					update($id,$table,'NET',$net);
					$replytext=resultpotak($poll);
					update($netid,'money','NET',select($id,$table,'NET'));
					update(1,$table,'LineID',0);
					update(1,$table,'admin',0);
					update(1,$table,"NET",$lap+1);
				}
				else{
					$replytext='❌สรุปผิด กรุณาสรุปใหม่❌';
				}
			}
		}
		//คำสั่งลงทะเบียน
		if(($text=="play"||$text=="Play")&&$id==0){
			$var=codename($res['displayName']);
			insert($table,$var,$lineid,0);
			if($netid==0){
				insert('money',$var,$lineid,1000);
			}
			$replytext="คุณ ".$res['displayName']." ลงทะเบียนแล้ว";
		}
		//ถ้าลงทะเบียนแล้วก็ให้บอกว่าพร้อมเล่น
		elseif(($text=="play"||$text=="Play")&&$id!=0){
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
