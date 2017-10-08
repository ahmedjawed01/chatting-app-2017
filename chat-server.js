var express = require('express');
var _ = require('underscore');
var app = express();
var path = require('path');
var formidable = require('formidable');
var fs = require('fs');

app.use(express.static(path.join(__dirname, 'public')));

app.use(function(req, res, next) {
    res.header("Access-Control-Allow-Origin", "*");
    res.header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    next();
});

var connections = [];
var members = [];
var messages = [];

var server = app.listen(3000);
var io = require('socket.io').listen(server);
io.sockets.on('connection', function (socket) {

	socket.once('disconnect', function() {
		var member = _.findWhere(members, { id: this.id });
		if(member) {
			members.splice(members.indexOf(member), 1);
			io.sockets.emit('member', members);
			console.log("Left: %s (remaining %s member)", member.clientName, members.length);
		}
		connections.splice(connections.indexOf(socket), 1);
		socket.disconnect();
		console.log("Disconnected: %s sockets remaining.", connections.length);
	});	

	socket.on('join', function(payload) {
		var member = _.findWhere(members, { clientId: payload.clientId });
		if(!member) {
			var newMember = {
				id: this.id,
				clientId: payload.clientId,
				clientName: payload.clientName
			};
			this.emit('joined', newMember); // for single member
			members.push(newMember);
		}
		io.sockets.emit('member', members); // for all members
		console.log("Member Joined: %s", payload.clientName);
	});

	socket.on('message', function(payload) {
		var newMsg = {
			id: payload.senderId,
			senderName: payload.senderName,
			receiverId: payload.receiverId,
			data: payload.data,
			msgType: payload.msgType
		};
		messages.push(newMsg);
		var recipientMember = _.findWhere(members, { clientId: payload.receiverId });
		console.log(recipientMember);
		console.log(payload.receiverId);
		// var recipientMsg = _.filter(messages, function(item){
		// 	return (item.id == payload.senderId && item.receiverId == payload.receiverId) || (item.id == payload.receiverId && item.receiverId == payload.senderId);
		// });
		var recipientMsg = filterMessage(messages, payload);
		io.to(recipientMember.id).emit('message', recipientMsg);
		this.emit('message', recipientMsg);
	});

	socket.on('showMessage', function(payload) {
		// var allMessages = _.filter(messages, function(item){
		// 	return (item.id == payload.senderId && item.receiverId == payload.receiverId) || (item.id == payload.receiverId && item.receiverId == payload.senderId);
		// });
		this.emit('message', filterMessage(messages, payload));
	});

	connections.push(socket);
	console.log("Connected: %s socket connected.", connections.length);
});
console.log("Polling server is running at 'http://localhost:3000'");

function filterMessage(messages, payload){
	return _.filter(messages, function(item){ return (item.id == payload.senderId && item.receiverId == payload.receiverId) || (item.id == payload.receiverId && item.receiverId == payload.senderId); });
}

app.post('/upload', function(req, res){

  // console.log(req);

  var files = [];

  // create an incoming form object
  var form = new formidable.IncomingForm();

  // specify that we want to allow the user to upload multiple files in a single request
  form.multiples = true;

  // store all uploads in the /uploads directory
  form.uploadDir = path.join(__dirname, '/storage');

  // every time a file has been uploaded successfully,
  // rename it to it's orignal name
  form.on('file', function(field, file) {
    fs.rename(file.path, path.join(form.uploadDir, file.name));
    // console.log(file.name);
    files.push(file.name);
  });

  
  // log any errors that occur
  form.on('error', function(err) {
    console.log('An error has occured: \n' + err);
  });

  // once all the files have been uploaded, send a response to the client
  form.on('end', function() {
  	console.log(files);
	console.log('end');
    // res.end('success');
    res.json(files);
  });

  // parse the incoming request containing the form data
  form.parse(req);

});