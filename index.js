var app = require('express')();
var querystring = require('querystring');
var http = require('http');
var server = require('http').Server(app);
var io = require('socket.io')(server);
var bodyParser = require('body-parser')
const request = require('request-promise');


server.listen(8080, function() {
    console.log('Socket server is running.');
});


var onlineUsers=new Map();
io.on('connection', function(socket) {
    console.log("Connected");
    var handshakeData = socket.request;
    onlineUsers.set(handshakeData._query['user_id'],socket.id);

    socket.join("Online");

    socket.on("disconnect", async () => {
        socket.leave("Online");
    });
})


app.post('/sendGame', function(req, res) {
    var content = JSON.parse(JSON.stringify(req.body));
    if(content!=null){
        io.to("Online").emit("change_game",content);
    }
    res.end();
});

app.post('/sendQuestion', function(req, res) {
    var content = JSON.parse(JSON.stringify(req.body));
    if(content!=null){
        io.to("Online").emit("change_question",content);
    }
    res.end();
});

app.post('/sendBalance', function(req, res) {
    var content = JSON.parse(JSON.stringify(req.body));
    if(content!=null){
        io.to(onlineUsers.get(data.user_id)).emit("change_balance",content);
    }
    res.end();
});

