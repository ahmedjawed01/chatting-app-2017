$('.upload-btn').on('click', function (){
    $('#upload-input').click();
    $('.progress-bar').text('0%');
    $('.progress-bar').width('0%');
});

$('#upload-input').on('change', function(){

  $('.progress').css('display', 'block');

  var files = $(this).get(0).files;

  if (files.length > 0){
    // create a FormData object which will be sent as the data payload in the
    // AJAX request
    var formData = new FormData();

    // loop through all the selected files and add them to the formData object
    for (var i = 0; i < files.length; i++) {
      var file = files[i];

      // add the files to formData object for the data payload
      formData.append('uploads[]', file, file.name);
    }

    $.ajax({
      url: 'http://localhost:3000/upload',
       xhrFields: {
        withCredentials: false //for cros request
      },
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      success: function(files){
          console.log('upload successful!\n' + files);

          var msg = {
            senderId: clientId,
            senderName: clientName,
            receiverId: receiverId,
            data: files,
            msgType: 'image'
          };
          console.log(msg);
          socket.emit('message', msg);

          // for(i in files) {
          //   $('#messages').append('<img src="'+baseUrl+'/images/uploads/'+files[i]+'" alt="'+files[i]+'" class="img-responsive img-rounded">');
          // }

          $("#messages").scrollTop($("#messages")[0].scrollHeight);

          $('.progress').css('display', 'none');
      },
      xhr: function() {
        // create an XMLHttpRequest
        var xhr = new XMLHttpRequest();

        // listen to the 'progress' event
        xhr.upload.addEventListener('progress', function(evt) {

          if (evt.lengthComputable) {
            // calculate the percentage of upload completed
            var percentComplete = evt.loaded / evt.total;
            percentComplete = parseInt(percentComplete * 100);

            // update the Bootstrap progress bar with the new percentage
            $('.progress-bar').text(percentComplete + '%');
            $('.progress-bar').width(percentComplete + '%');

            // once the upload reaches 100%, set the progress bar text to done
            if (percentComplete === 100) {
              $('.progress-bar').html('Done');
            }

          }

        }, false);

        return xhr;
      }
    });

  }
});
