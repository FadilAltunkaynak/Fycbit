<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8" />
    <title>WebSocket Tester</title>

    <!-- Pusher (required by laravel-websockets) -->
    <script src="https://js.pusher.com/7.2/pusher.min.js"></script>

    <!-- Laravel Echo (UMD build → exposes global Echo) -->
    <script src="{{ asset('js/app.js') }}"></script>

    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 20px; }
        .card { background: #fff; padding: 20px; margin-bottom: 20px;
                border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        input, select { width: 100%; padding: 10px; margin: 8px 0;
                        border: 1px solid #ccc; border-radius: 6px; }
        button { background:#007bff; color:#fff; padding:10px 18px;
                 border:none; border-radius:6px; cursor:pointer; }
        button:hover { background:#0056b3; }
        #logs1 { background:#000; color:#0f0; padding:10px; border-radius:8px;
                height:20px; overflow-y:auto; font-family:monospace; }
        #logs { background:#000; color:#0f0; padding:10px; border-radius:8px;
                height:150px; overflow-y:auto; font-family:monospace; }
    </style>
</head>
<body>

    <div class="card">
        <h2>WebSocket Config</h2>
        <label>Channel Type</label>
        <select id="channelType">
            <option value="public">Public</option>
            <option value="private">Private</option>
            <option value="presence">Presence</option>
        </select>

        <label>Channel Name</label>
        <input id="channelName" placeholder="orders">

        <label>Event Name</label>
        <input id="eventName" placeholder="OrderCreated">

        <button onclick="connectWS()">Connect & Subscribe</button>
    </div>

    <div class="card">
        <h2>ConnectLogs</h2>
        <div id="logs1"></div>
    </div>
    <div class="card">
        <h2>WebSocket Data Logs</h2>
        <div id="logs"></div>
    </div>

<script>
function log1(msg) {
    const logs = document.getElementById("logs1");
    logs.innerHTML += "\n" + msg;
    logs.scrollTop = logs.scrollHeight;
}
function log(msg) {
    const logs = document.getElementById("logs");
    logs.innerHTML += "<br>" + msg;
    logs.scrollTop = logs.scrollHeight;
}

function connectWS() {
    const type = document.getElementById("channelType").value;
    const name = document.getElementById("channelName").value;
    const event = document.getElementById("eventName").value;

    log1("Connecting...");

    log1("Connected to WS");

    let channel;

    if (type === "public") {
        channel = Echo.channel(name);
    } 
    else if (type === "private") {
        channel = Echo.private(name);
    } 
    else {
        channel = Echo.join(name);
    }

    log1(`Subscribed → ${type}.${name}`);

    // Listen event
    channel.listen(event, (data) => {
        log(`EVENT RECEIVED: ${event} → ` + JSON.stringify(data));
        console.log(`EVENT RECEIVED: ${event} → `, data);
    });
}
</script>

</body>
</html>
