<?php
ob_start();
session_start();
if (! isset($_SESSION['name'])) {  
  header('location: ../index.php');
} 
if ($_SESSION['name'] != 'angryboys-chatbox') {  
  header('location: ../index.php');
} 

include('header.php');
include("../connection.php");
include('../inc/smiley.php');

$receiver_id = isset($_GET['r']) ? $_GET['r'] : null;

$smiley_array = get_clickable_smileys('../img/smileys/', 'comment_textarea_alias'); 
$col_array = make_columns($smiley_array, 15); ?>

<div class="container messages">

  <div class="row text-center">
    <div class="col-md-12">
    <img src="../img/logo.png" alt="CHAT APP 2017">
    </div>
  </div>
  
  <div class="messages__lists">
    <p class="messages__lists--headline">Conversations 
      <span style="font-size:10px;color:#ff0;" id="status"></span></p>


    <ul class="conversation-list customScroll" id="conversationList"></ul>
  </div>

  <div class="messages__view">
    

    <div id="conversationWrapper" class="messages__wrapper">

  	 	<ul class="messages__view-list customScroll" id="messages"></ul>	

      <div class="progress">
        <div class="progress-bar" role="progressbar"></div>
      </div>						

    </div>


    <div class="messages__view--form">

      <form id="messageForm" method="POST">
        <textarea name="msgContent" id="msgText" class="form-field" data-placeholder="Write a new message to..."></textarea>

        <button style="right: 150px" class="btn btn-info message-btn emoticon-btn" type="button"><span class="fa fa-smile-o"></span></button>

        <button style="right: 95px" class="btn message-btn upload-btn" type="button"><span class="fa fa-image"></span></button>
        <input id="upload-input" type="file" name="uploads[]" multiple="multiple"></br>
        <button type="submit" id="submitMessage" class="btn btn-success message-btn">Send</button>
      </form>

      <div class="table-responsive emoticons">
        <table class="table table-bordered table-striped">
          <?php foreach ($col_array as $col) : ?>
            <tr>
            <?php $i = 0; $col_length = count($col); 
            for (; $i < $col_length; $i++) : ?>
              <td class="text-center"><?php echo $col[$i]; ?></td>
            <?php endfor; ?>
            </tr>
          <?php endforeach; ?>
        </table>
      </div>



    </div>

  </div>

</div>


<script type="text/javascript">

var socket = io.connect('http://localhost:3000');

var clientId = "<?php echo $_SESSION['userid']; ?>";
var clientName = "<?php echo $_SESSION['username']; ?>";
var receiverId = "<?php echo $receiver_id; ?>";

socket.on('connect', function(data) {
    $('#status').text('connected');
    socket.emit('join', { 
      clientId: clientId, 
      clientName: clientName, 
      receiverId:receiverId 
    });
});

socket.on('disconnect', function(data) {
  $('#status').text('disconnected');
  $('#total').html('');
  $('#conversationList').html('');
  $('#messages').html('');
});

socket.on('joined', function(member) {
  // sessionStorage.member = JSON.stringify(member);
  socket.emit('showMessage',{senderId:clientId,receiverId:receiverId});
});

socket.on('member', function(members) {
  $('#status').text('Connected: ' + members.length);
  $('#conversationList').html(" ");
  members.forEach(function(member) {
    var currentInbox = (receiverId == member.clientId) ? " current" : "";
    $('#conversationList').append('<li class="inbox' + currentInbox + '"><a class="showMessage" href="?r=' + member.clientId + '" data-id="' + member.clientId + '"><i class="fa fa-fw fa-user"></i> ' + member.clientName + '</a></li>');
  });
});

// $(document).delegate('a.showMessage','click',function(e) {
//  e.preventDefault();
//  console.log('senderId:'+$(this).data('id'));
//  console.log('receiverId:'+clientId);
//  socket.emit('showMessage',{senderId:$(this).data('id'),receiverId:clientId});
// });

function parse_smileys(str = '', image_url = '', smileys = null)
{
  if (str.constructor === Array || image_url == '')
  {
    return str;
  }

  // if ( ! is_array(smileys))
  // {
  //   if (FALSE === (smileys = _get_smiley_array()))
  //   {
  //     return str;
  //   }
  // }

  var smileys = '<?php echo json_encode(_get_smiley_array()); ?>';
  smileys = JSON.parse(smileys);
  // console.log(JSON.parse(smileys));
  console.log(smileys['8-/'][0]);

  // // Add a trailing slash to the file path if needed
  // image_url = preg_replace("/(.+?)\/*$/", "\\1/",  image_url);

  // foreach (smileys as key => val)
  // {
  //   $str = str_replace(key, "<img src=\"".image_url.$smileys[$key][0]."\" width=\"".$smileys[$key][1]."\" height=\"".$smileys[$key][2]."\" alt=\"".$smileys[$key][3]."\" style=\"border:0;\" />", $str);
  // }

  // var sm = JSON.parse(smileys);
  for (var key in smileys) {
     if (smileys.hasOwnProperty(key)) {
        // console.log(smileys[key][0]);
        str = str.replace(key, '<img src="'+image_url+smileys[key][0]+'" width="'+smileys[key][1]+'" height="'+smileys[key][2]+'" alt="'+smileys[key][3]+'" style="border:0;display:inline-block;vertical-align: middle;" />');
     }
  }
  
  return str;
}

function nl2br (str, is_xhtml=false) {
    var breakTag = (is_xhtml || typeof is_xhtml === 'undefined') ? '<br />' : '<br>';
    return (str + '').replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1' + breakTag + '$2');
}

socket.on('message', function(messages) {
  $('#messages').html('');
  messages.forEach(function(msg) {
    var message = '';
    switch(msg.msgType) {
      case 'string':
          message += nl2br(parse_smileys(msg.data, '../img/smileys/'));
        break;
      case 'image':
          for(i in msg.data) {
            message += '<img src="../storage/'+msg.data[i]+'" alt="'+msg.data[i]+'" class="img-responsive img-rounded" style="margin: 20px 0;">';
          }
        break;
    }

   $('#messages').append('<li>' + msg.senderName + ': ' + message + '</li>');
  });

  $("#messages").scrollTop($("#messages")[0].scrollHeight);
});

$('#submitMessage').on('click',function(e) {
  e.preventDefault();
  var msg = {
    senderId: clientId,
    senderName: clientName,
    receiverId: receiverId,
    data: $('textarea[name="msgContent"]').val(),
    msgType: 'string'
  };
  console.log(msg);
  socket.emit('message', msg);
  $('textarea[name="msgContent"]').val(' ').focus();
  $('#chatForm').submit();
});

$('.emoticon-btn').on('click', function(e) {
  $('.emoticons').toggle('fast');
});

</script>

<?php echo smiley_js('comment_textarea_alias', 'msgText') ?>
<?php include('footer.php'); ?>