<!DOCTYPE html>
<html lang="en" >
<head>
  <meta charset="UTF-8">
  <title>Tennis HTML5 Canvas Game</title>
  <link rel="stylesheet" href="./style/style.css">

</head>
<body>
<!-- partial:index.partial.html -->
<div class="container">
  <p>Move mouse to control the paddle. You are the Right player</p>
</div>
<canvas id="gameCanvas" width="800" height="600"></canvas>
<!-- partial -->
  <script type="text/javascript">
  	var canvas; //handle for info about our display area
var canvasCtx; //underlying graphic info

var ballX = 50;
var ballY = 50;
var ballX_coards = 50;
var ballY_coards = 50;

var ballSpeedX = 10;
var ballSpeedY = 4;

var player1Score = 0;
var player2Score = 0;

var paddle1Y = 250;
var paddle2Y = 250;

var showWinScreen = false;

const PADDLE_HEIGHT = 100;
const PADDLE_THICKNESS = 10;
const WINNING_SCORE = 3;

function calculateMousePosition(evt) {
  // bounding the black area on our rect
  var rect = canvas.getBoundingClientRect();
  var root = document.documentElement;
  var mouseX = evt.clientX - rect.left - root.scrollLeft;
  var mouseY = evt.clientY - rect.top - root.scrollTop;
  return {
    x: mouseX,
    y: mouseY
  };
}

function handleMouseClick(evt) {
  if (showWinScreen) {
    player1Score = 0;
    player2Score = 0;
    showWinScreen = false;
  }

}
window.onload = function() {
  canvas = document.getElementById('gameCanvas');
  canvasCtx = canvas.getContext('2d');
  var framesPerSecond = 30;

  // connection to the server
  var websocket = new WebSocket("ws://<?php echo $_SERVER['SERVER_NAME'];?>:8090/demo/php-socket.php"); 
  websocket.onopen = function(event) {   
    setInterval(function() {
      moveEverything();
      drawEverything();
    }, 1000 / framesPerSecond);
  }

  canvas.addEventListener('mousedown', handleMouseClick);
  canvas.addEventListener('mousemove', function(evt) {
    var mousePos = calculateMousePosition(evt);
    var messageJSON = {
        chat_user : "player2",
        chat_message : mousePos.y - (PADDLE_HEIGHT / 2)
      };
      websocket.send(JSON.stringify(messageJSON));
  });

  websocket.onmessage = function(event) {
    var Data =  JSON.parse(event.data);
    if(Data.message=="player1"){
      paddle1Y=Data.value;
    }
    if(Data.message=="player2"){
      paddle2Y=Data.value;
    }
    if(parseInt(Data.message)){
      ballX_coards=Data.message;
      ballY_coards=Data.value;
    }    
    if(Data.message=="player1Score"){
      player1Score++; //MUST BE BEFORE BALL RESET
      ballReset();
    }
    if(Data.message=="player2Score"){
      player2Score++; //MUST BE BEFORE BALL RESET
      ballReset();
    }
  };
};

function ballReset() {
  if (player1Score >= WINNING_SCORE || player2Score >= WINNING_SCORE) {
    showWinScreen = true;
  }
  ballSpeedX = -ballSpeedX;
  ballX = canvas.width / 2;
  ballY = canvas.height / 2;
}

function computerMovement() {
  var paddle2YCenter = paddle2Y + (PADDLE_HEIGHT / 2);
  if (paddle2YCenter < ballY - 35) {
    paddle2Y += 6;
  } else if (paddle2YCenter > ballY + 35) {
    paddle2Y -= 6;
  }
}

function moveEverything(websocket) {
  if (showWinScreen) {
    return;
  }
  //computerMovement();
}
function drawNet() {
  //for loop
  //start variable i at 0 
  //count upto 600 at intervals of 40 (jumping 40px at a time)
  //
  for (var i = 0; i < canvas.height; i += 40) {
    colorRect(canvas.width / 2 - 1, i, 2, 20, 'white');
  }
}

function drawEverything() {
  // Tennis board background
  colorRect(0, 0, canvas.width, canvas.height, 'GREEN');

  if (showWinScreen) {
    canvasCtx.fillStyle = 'white';
    if (player1Score >= WINNING_SCORE) {
      canvasCtx.fillText('player 1 Won!', 350, 200);
    } else if (player2Score >= WINNING_SCORE) {
      canvasCtx.fillText('Player 2 Won!', 350, 200);
    }
    canvasCtx.fillText('Click to continue', 350, 500);
    return;
  }
  drawNet();
  //right player/computer paddle
  colorRect(0, paddle1Y, PADDLE_THICKNESS, PADDLE_HEIGHT, 'white');
  //Left player paddle
  colorRect(canvas.width - PADDLE_THICKNESS, paddle2Y, PADDLE_THICKNESS, PADDLE_HEIGHT, 'white');
  //Tennis ball
  colorCircle(ballX_coards, ballY_coards, 10, 'white');
  canvasCtx.fillText(player1Score, 100, 100);
  canvasCtx.fillText(player2Score, canvas.width - 100, 100);

}

function colorCircle(centerX, centerY, radius, drawColor) {
  canvasCtx.fillStyle = drawColor;
  canvasCtx.beginPath();
  canvasCtx.arc(centerX, centerY, radius, 0, Math.PI * 2, true);
  canvasCtx.fill();
}

function colorRect(leftX, topY, width, height, drawColor) {
  canvasCtx.fillStyle = drawColor;
  canvasCtx.fillRect(leftX, topY, width, height);
}


  </script>
</body>
</html>