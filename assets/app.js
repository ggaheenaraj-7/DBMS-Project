const API='api/api.php';
const $=s=>document.querySelector(s);
async function api(action, data={}){const res=await fetch(`${API}?action=${action}`,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(data)});return res.json();}
function money(n){return '₹'+Number(n||0).toLocaleString('en-IN')}
async function loadDashboard(){
  const d=await api('dashboard');
  if(!d.ok)return;
  $('#stats').innerHTML=[
    ['Shipments',d.stats.shipments,'Total packages'],
    ['Delivered',d.stats.delivered,'Completed deliveries'],
    ['In Transit',d.stats.in_transit,'Active shipments'],
    ['Exceptions',d.stats.exceptions,'AI-detected risk']
  ].map(x=>`<div class="card"><span class="muted">${x[0]}</span><strong>${x[1]}</strong><span class="muted">${x[2]}</span></div>`).join('');
  const total=Math.max(1,d.stats.shipments);
  $('#statusBars').innerHTML=Object.entries(d.statuses).map(([k,v])=>`<div class="bar-row"><div class="bar-label"><span>${k}</span><b>${v}</b></div><div class="bar"><i style="width:${Math.min(100,v/total*100)}%"></i></div></div>`).join('');
  $('#aiPanel').innerHTML=d.ai.map(a=>`<div class="ai-item"><b>${a.title}</b><br>${a.message}</div>`).join('');
  $('#warehouses').innerHTML=d.warehouses.map(w=>`<p><b>${w.name}</b><br><span class="muted">${w.city} · ${w.used}/${w.capacity} used · ${w.alert?'⚠ Capacity alert':'✓ Normal'}</span></p>`).join('');
  $('#drivers').innerHTML=d.drivers.map(x=>`<p><b>${x.name}</b><br><span class="muted">${x.vehicle_no} · ${x.status}</span></p>`).join('');
  $('#reportRows').innerHTML=[
    ['Average delivery time',d.report.avg_delivery+' hours','Predictive operations'],
    ['Estimated carbon',d.report.carbon+' kg CO₂','Shipment footprint estimate'],
    ['Warehouse utilization',d.report.utilization+'%','Capacity monitoring'],
    ['Customer rating',d.report.rating+'/5','Feedback intelligence']
  ].map(r=>`<tr><td>${r[0]}</td><td>${r[1]}</td><td>${r[2]}</td></tr>`).join('');
}
$('#bookingForm').addEventListener('submit',async e=>{e.preventDefault();const data=Object.fromEntries(new FormData(e.target));const r=await api('book',data);$('#bookingResult').innerHTML=r.ok?`<div class="panel">Shipment created. Tracking ID: <b>${r.tracking_id}</b> · AI ETA: <b>${r.eta} hours</b> · Route: ${r.route}</div>`:`<div class="panel">${r.error}</div>`;loadDashboard()});
async function trackShipment(){
 const id=$('#trackingInput').value.trim();if(!id)return;
 const r=await api('track',{tracking_id:id});
 if(!r.ok){$('#trackingResult').innerHTML=`<div class="panel">${r.error}</div>`;return}
 const order=['Booked','Registered','Picked Up','Warehouse Processing','In Transit','Destination Warehouse','Out for Delivery','Delivered'];
 const current=order.indexOf(r.status);
 $('#trackingResult').innerHTML=`<div class="panel"><h3>${r.tracking_id}</h3><p>${r.origin} → ${r.destination} · ${r.package_type} · ${r.weight} kg</p><p><b>${r.status}</b> · Predicted ETA ${r.eta} hours · Carbon ${r.carbon} kg CO₂</p><div class="timeline">${order.map((x,i)=>`<div class="step ${i<=current?'done':''}">${x}</div>`).join('')}</div></div>`;
}
async function runAI(){const r=await api('ai_assign',{shipment_id:1});alert(r.ok?`AI recommendation: ${r.driver} (${r.reason})`:r.error);loadDashboard()}
async function simulateDelay(){const r=await api('detect_exceptions');alert(r.message);loadDashboard()}
async function reverseLogistics(){const r=await api('return',{shipment_id:1,reason:'Customer requested return'});alert(r.ok?`Return created: ${r.return_id}`:r.error)}
async function generateQR(){const id=$('#trackingInput').value.trim()||'TRK-DEMO-001';const r=await api('qr',{tracking_id:id});$('#qrText').textContent=r.payload}
async function submitFeedback(){const r=await api('feedback',{shipment_id:1,rating:Number($('#rating').value),comment:$('#feedback').value});alert(r.ok?'Feedback saved':'Could not save feedback');loadDashboard()}
async function start(){loadDashboard();$('#trackingInput').value='TRK-DEMO-001';trackShipment()}
start();
