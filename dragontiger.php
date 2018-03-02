<?php
$access_token = 'SIkXpp6ylNgQ42HEg2wkOD9hG7YRkzHbsORCxZ3p9iWLynhB9/t7hD9zdoMdUbXYqaibnclBOqZUfj+jLpWyuwWRGpvWd2hMaZvRf+2LyXbbRi9SDZrlRfGcWm6w3ry+fatBxeFmvx4S0zjEzvvFFAdB04t89/1O/w1cDnyilFU=';

$content = file_get_contents('php://input');		//รับค่าจากล่องข้อความใน line
$events = json_decode($content, true);			//แก้ระหัส json ที่ line ส่งมา
if (!is_null($events['events'])) {			//ตรวจสอบว่ามีข้อมูลส่งมาหรือไม่
	foreach ($events['events'] as $event) {		//วนลูปใน array ทีละตัว
	$replyToken = $event['replyToken'];	//รับค่า  replyToken เพื่อรอส่งกลับ
	$type=$event['message']['type'];
	$packageId=$event['message']['packageId'];
	$stickerId=$event['message']['stickerId'];
	$replytext="$type $packageId $stickerId";
	$messages = [
			[
			'type' => 'text',
			'text' => $replytext
			]
		];
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
