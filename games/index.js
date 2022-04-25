var https = require('https');
    fs = require('fs');

var options = {
    key: fs.readFileSync('/etc/letsencrypt/live/testy.iusvitae.pl/privkey.pem'),
    cert: fs.readFileSync('/etc/letsencrypt/live/testy.iusvitae.pl/fullchain.pem'),
    ca: fs.readFileSync('/etc/letsencrypt/ssl-dhparams.pem')
}

const app = require('express')();

var http = https.createServer(options, app);

const io = require('socket.io')(http);

const sqlite3 = require('sqlite3').verbose();
var bodyParser = require('body-parser');

app.use( bodyParser.json() );       // to support JSON-encoded bodies
app.use(bodyParser.urlencoded({     // to support URL-encoded bodies
    extended: true
}));

/*const mongoose = require('mongoose');

const Notifications = require('./models/NotificationModel');*/
let db = new sqlite3.Database('./db/sqlitedb.db', (err) => {
    if (err) {
        console.error(err.message);
    }
    console.log('Connected to the chinook database.');
});

db.serialize(function() {
    db.run("CREATE TABLE IF NOT EXISTS notifications (id INTEGER PRIMARY KEY AUTOINCREMENT, title TEXT NOT NULL, message TEXT, created_at INTEGER NOT NULL);");
    db.run("CREATE TABLE IF NOT EXISTS users (id INTEGER PRIMARY KEY AUTOINCREMENT, username TEXT NOT NULL, last_seen INTEGER UNIQUE NOT NULL, last_notification_access INTEGER);");
    /*var stmt = db.prepare("INSERT INTO lorem VALUES (?)");
    for (var i = 0; i < 10; i++) {
        stmt.run("Ipsum " + i);
    }
    stmt.finalize();*/
});

http.listen(3000, () => {
    console.log('listening on *:3000');
});


var games = {};

function game_room_join( game_id, user ) {

    if (typeof games[game_id] == 'undefined' ) {
        games[game_id] = {};
        games[game_id].users = {};
        games[game_id].chat = [];
    }
    games[game_id].users[user.user_id] = drop_hash(user);
};

function game_room_leave( game_id, user ) {
    if (
        typeof games[game_id] == 'undefined'
        || typeof games[game_id].users == 'undefined'
        || typeof games[game_id].users[user.user_id] == 'undefined'
    ) {
        console.log('== aborting because no such room or user ==' );
        return;
    }
    delete games[game_id].users[user.user_id];
}

function game_chat( game_id, user, msg ) {
    games[game_id].chat.push(
        {
            user: drop_hash(user),
            msg: msg,
            read: []
        }
    );
}

function drop_hash( user ) {
    delete user.hash_id;
    return user;
}

function read_message(game_id, user_id){
    for(let item of games[game_id].chat){
       if(!item.read.includes(user_id)) {
           item.read.push(user_id);
       }
    }
}

app.post('/notifications/newarticle', (req, res) => {
    let created_at = new Date().getTime();
    db.run(`INSERT INTO notifications(title, message, created_at) VALUES(?, ?, ?)`, [req.body.title, req.body.message, created_at], function(err) {
        if (err) {
            return console.log(err.message);
        }
        // get the last insert id
        // console.log(`A row has been inserted with rowid ${this.lastID}`);
    });
    console.log ("New article");
    console.log (req.body);
    io.emit("newarticle", {title: req.body.title, message: req.body.message});
    res.send('success');
})

let userMeta = [];

io.on('connection', (socket) => {
    console.log("query... ", JSON.stringify(socket.handshake.query));
    console.log("query... ", JSON.stringify(socket.request.query));
    userMeta[socket.id] = {};
    username = socket.handshake.query.user;
    if (username) {
        username = username.toLowerCase();
    }
    userMeta[socket.id].username = username;
    console.log('socket connected', socket.id);
    if (username){
        // userMeta[socket.id].username = username;
        db.get("SELECT * FROM users WHERE username=?", [username], (err, row) => {
            // process the row here
            if (err) {
                return console.error(err.message);
            }
            let last_seen = new Date().getTime();
            if (row){
                console.log ("ROW", row);
                let data = [last_seen, username];
                //  last_seen last_notification_access
                let sql = `UPDATE users
                        SET last_seen = ?
                        WHERE name = ?`;
                db.run(sql, data, function(err) {
                });
            }else {
                db.run(`INSERT INTO users(username, last_seen) VALUES(?, ?)`, [username, last_seen], function(err) {
                    if (err) {
                        return console.log(err.message);
                    }
                    // get the last insert id
                    // console.log(`A row has been inserted with rowid ${this.lastID}`);
                });
            }
        });
    }

  socket.on('disconnect', () => {
      if (userMeta[socket.id]!==undefined){
          let last_seen = new Date().getTime();
          username = userMeta[socket.id].username;
          let data = [last_seen, username];
          //  last_seen last_notification_access
          let sql = `UPDATE users
                        SET last_seen = ?
                        WHERE username = ?`;
          db.run(sql, data, function(err) {
          });
          delete userMeta[socket.id];
      }
      console.log('socket disconnected', socket.id);
  });

  socket.on('game_start', (data) => {
      console.log('game_start ['+data.game_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'game_start', data );
  });

  socket.on('msg', (data) => {
      console.log('msg ['+data.game_id+']');
      game_chat( data.game_id, data.user, data.msg );
      socket.emit( 'msg', {
          user: drop_hash(data.user),
          msg: data.msg,
          read: [],
      } );
      socket.to( 'game-' + data.game_id ).emit( 'msg', {
          user: drop_hash(data.user),
          msg: data.msg,
          read: [],
      } );
  });

  socket.on('ready', (data) => {
      console.log('ready ['+data.game_id+'] ['+data.user.user_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'ready', {
          user: drop_hash(data.user)
      } );
  });

  socket.on('room_ban', (data) => {
      console.log('room_ban ['+data.game_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'room_ban', data );
  });

  socket.on('room_join', (data) => {
      console.log('room_join ['+data.game_id+'] ['+data.user.user_id+']');
      game_room_join( data.game_id, data.user );
      socket.emit( 'u_room_joined', games[ data.game_id ] );
      socket.join( 'game-' + data.game_id );
      socket.to( 'game-' + data.game_id ).emit( 'room_joined', {
          user: drop_hash(data.user)
      } );
  });

  socket.on('room_leave', (data) => {
      console.log('room_leave ['+data.game_id+'] ['+data.user.user_id+']');
      game_room_leave( data.game_id, data.user );
      socket.emit( 'u_room_left' );
      socket.to( 'game-' + data.game_id ).emit( 'room_left', {
          user: drop_hash(data.user)
      } );
  });

  socket.on('room_refresh', (data) => {
      console.log('room_refresh ['+data.game_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'room_refresh' );
  });

  socket.on('room_get', (data) => {
      console.log('room_get ['+data.game_id+']');
      socket.emit( 'room_get', games[ data.game_id ] );
  });

  socket.on('unready', (data) => {
      console.log('unready ['+data.game_id+'] ['+data.user.user_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'unready', {
          user: drop_hash(data.user)
      } );
  });

  socket.on('race_refresh', (data) => {
      console.log('race_refresh ['+data.game_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'race_refresh' );
  });

  socket.on('race_finished', (data) => {
      console.log('race_finished ['+data.game_id+']');
      socket.to( 'game-' + data.game_id ).emit( 'race_finished' );
  });

  socket.on('read', (data) => {
      console.log('read game: ['+data.game_id+'] user: ['+data.user.user_id+']');
      read_message(data.game_id, data.user.user_id);
      socket.to( 'game-' + data.game_id ).emit( 'readed' );
  });

    socket.on('mynotifications', () => {
        let username = userMeta[socket.id].username;
        if (username){
            // userMeta[socket.id].username = username;
            db.get("SELECT * FROM users WHERE username=?", [username], (err, row) => {
                // process the row here
                if (err) {
                    socket.emit('yournotifications', 0);
                    return console.error(err.message);
                }else {
                    if (row) {
                        let last_notification_access = row.last_notification_access;
                        if (last_notification_access===undefined || last_notification_access==null){ last_notification_access = 0; }
                        console.log ("last_notification_access: ", last_notification_access);
                        db.get("SELECT count(*) as count FROM notifications WHERE created_at>=?", [last_notification_access], (err, row) => {
                            console.log ("COUNTED", row);
                            socket.emit('yournotifications', row["count"]);
                        });
                        console.log("USER: ", row);
                    }else {
                        socket.emit('yournotifications', 0);
                    }
                }

            });
        }
        if (userMeta[socket.id] === undefined){return;}
        console.log("notifications... ", JSON.stringify(socket.request.query));
        console.log("sending msg from " + socket.id + ": "+username);
        username = userMeta[socket.id].username;
    });

    socket.on('clearmynotifications', () => {
        let username = userMeta[socket.id].username;
        if (username){
            let last_notification_access = new Date().getTime();
            let data = [last_notification_access, username];
            //  last_seen last_notification_access
            let sql = `UPDATE users
                        SET last_notification_access = ?
                        WHERE username = ?`;
            db.run(sql, data, function(err) {
            });
            socket.emit('yournotifications', 0);
        }
        if (userMeta[socket.id] === undefined){return;}
        console.log("notifications... ", JSON.stringify(socket.request.query));
        console.log("sending msg from " + socket.id + ": "+username);
        username = userMeta[socket.id].username;
    });

});
