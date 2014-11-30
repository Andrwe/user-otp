$(document).ready(function(){
    $(".otp_submit_action").click( function(){        
      // Serialize the data
      var post = $( "#user_otp" ).serialize();
			if (this.value === 'Update') {
				post = post + '&otp_action=replace_otp';
			} else if (this.value === 'Delete') {
				post = post + '&otp_action=delete_otp';
			}
      // Ajax foo
      $.post( OC.filePath('user_otp', 'ajax', 'personalSettings.php'), post, function(data){
          if( data.status === 'success' ){
            $( "#user_otp" ).submit();
          }else{
            alert("Error : " + data.data.message);
          }
      });
      return false;
    }); 
});
