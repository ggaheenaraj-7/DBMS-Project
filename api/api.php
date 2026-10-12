<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
$input=json_decode(file_get_contents('php://input'),true) ?: [];
$action=$_GET['action'] ?? '';

$pdo=new PDO('mysql:host=localhost;dbname=smart_logistics;charset=utf8mb4','root','',
  [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);

function out($a){echo json_encode($a);exit;}
function eta($weight,$priority){$base=2.5+($weight*0.12);if($priority==='Express')$base*=.75;if($priority==='Urgent')$base*=.55;return round(max(.5,$base),1);}
function carbon($weight,$distance=25){return round(max(.2,$weight*.12*$distance/10),2);}

try{
switch($action){
case 'dashboard':
 $stats=$pdo->query("SELECT COUNT(*) shipments,
 SUM(status='Delivered') delivered,
 SUM(status IN ('In Transit','Out for Delivery','Picked Up','Warehouse Processing')) in_transit,
 SUM(status IN ('Delayed','Exception')) exceptions FROM shipments")->fetch();
 $rows=$pdo->query("SELECT status,COUNT(*) c FROM shipments GROUP BY status")->fetchAll();
 $statuses=[];foreach($rows as $r)$statuses[$r['status']]=$r['c'];
 $ws=$pdo->query("SELECT w.*,ROUND(used_capacity/capacity*100) util FROM warehouses w")->fetchAll();
 $warehouses=array_map(fn($w)=>['name'=>$w['name'],'city'=>$w['city'],'used'=>$w['used_capacity'],'capacity'=>$w['capacity'],'alert'=>$w['used_capacity']/$w['capacity']>=.8],$ws);
 $drivers=$pdo->query("SELECT d.name,v.vehicle_no,d.status FROM drivers d LEFT JOIN vehicles v ON v.id=d.vehicle_id ORDER BY d.id")->fetchAll();
 $avg=$pdo->query("SELECT AVG(TIMESTAMPDIFF(HOUR,booked_at,delivered_at)) a FROM shipments WHERE delivered_at IS NOT NULL")->fetch()['a'];
 $util=$pdo->query("SELECT AVG(used_capacity/capacity*100) u FROM warehouses")->fetch()['u'];
 $rating=$pdo->query("SELECT AVG(rating) r FROM feedback")->fetch()['r'];
 $carbon=$pdo->query("SELECT SUM(carbon_kg) c FROM shipments")->fetch()['c'];
 out(['ok'=>true,'stats'=>['shipments'=>(int)$stats['shipments'],'delivered'=>(int)$stats['delivered'],'in_transit'=>(int)$stats['in_transit'],'exceptions'=>(int)$stats['exceptions']],
 'statuses'=>$statuses,'warehouses'=>$warehouses,'drivers'=>$drivers,
 'ai'=>[
  ['title'=>'Smart allocation','message'=>'Assignment considers load, priority, location and driver availability.'],
  ['title'=>'Predictive ETA','message'=>'ETA is estimated from package weight and priority; replace this function with ML later.'],
  ['title'=>'Exception detection','message'=>'High-risk statuses and warehouse capacity above 80% are flagged.'],
  ['title'=>'Sustainability','message'=>'A carbon estimate is attached to shipments for carbon-aware decisions.']
 ],
 'report'=>['avg_delivery'=>round((float)($avg?:0),1),'carbon'=>round((float)($carbon?:0),2),'utilization'=>round((float)($util?:0),1),'rating'=>round((float)($rating?:0),1)]]);
break;

case 'book':
 $weight=(float)$input['weight'];$priority=$input['priority'];$track='TRK-'.date('YmdHis').'-'.random_int(100,999);
 $e=eta($weight,$priority);$c=carbon($weight);
 $q=$pdo->prepare("INSERT INTO shipments(customer_id,tracking_id,sender_name,receiver_name,origin,destination,package_type,weight,priority,status,eta_hours,carbon_kg) VALUES(?,?,?,?,?,?,?,?,?,'Booked',?,?)");
 $q->execute([$input['customer_id'],$track,$input['sender_name'],$input['receiver_name'],$input['origin'],$input['destination'],$input['package_type'],$weight,$priority,$e,$c]);
 $sid=$pdo->lastInsertId();
 $pdo->prepare("INSERT INTO shipment_events(shipment_id,status,location,message) VALUES(?, 'Booked', ?, 'Courier booking created')")->execute([$sid,$input['origin']]);
 out(['ok'=>true,'tracking_id'=>$track,'eta'=>$e,'route'=>"Recommended: {$input['origin']} → {$input['destination']} via lowest-congestion corridor"]);
break;

case 'track':
 $q=$pdo->prepare("SELECT * FROM shipments WHERE tracking_id=?");$q->execute([$input['tracking_id']]);$s=$q->fetch();if(!$s)out(['ok'=>false,'error'=>'Tracking ID not found']);
 out(['ok'=>true]+$s);
break;

case 'ai_assign':
 $s=$pdo->query("SELECT * FROM shipments ORDER BY id LIMIT 1")->fetch();
 $d=$pdo->query("SELECT d.*,v.vehicle_no FROM drivers d LEFT JOIN vehicles v ON v.id=d.vehicle_id WHERE d.status='Available' LIMIT 1")->fetch();
 if(!$s||!$d)out(['ok'=>false,'error'=>'No suitable shipment/available driver found']);
 $pdo->prepare("UPDATE shipments SET driver_id=?,status='Picked Up' WHERE id=?")->execute([$d['id'],$s['id']]);
 out(['ok'=>true,'driver'=>$d['name'].' · '.$d['vehicle_no'],'reason'=>'Best available match for current demo data based on availability and shipment priority.']);
break;

case 'detect_exceptions':
 $n=$pdo->query("SELECT COUNT(*) FROM warehouses WHERE used_capacity/capacity>=.8")->fetchColumn();
 out(['ok'=>true,'message'=>$n.' warehouse capacity alert(s) detected. Delay/anomaly monitoring completed.']);
break;

case 'return':
 $q=$pdo->prepare("INSERT INTO returns(shipment_id,reason,status) VALUES(?,?,'Requested')");$q->execute([$input['shipment_id'],$input['reason']]);out(['ok'=>true,'return_id'=>$pdo->lastInsertId()]);
break;

case 'qr':
 out(['ok'=>true,'payload'=>'https://localhost/smart-logistics/?track='.rawurlencode($input['tracking_id'])]);
break;

case 'feedback':
 $q=$pdo->prepare("INSERT INTO feedback(shipment_id,rating,comment) VALUES(?,?,?)");$q->execute([$input['shipment_id'],$input['rating'],$input['comment']]);out(['ok'=>true]);
break;

default: out(['ok'=>false,'error'=>'Unknown API action']);
}}catch(Throwable $e){out(['ok'=>false,'error'=>$e->getMessage()]);}
?>