$(document).ready(function(){

$("#mysearch").keyup(function(){
   _this = this;
        // Show only matching TR, hide rest of them
        $.each($("table tbody tr"), function() {
            if($(this).text().toLowerCase().indexOf($(_this).val().toLowerCase()) === -1)
               $(this).hide();
            else
               $(this).show();                
        });
});


$("#deleteall").click(function(){
event.preventDefault();

$('#myModal').show();



});

$(".close").click(function(){

$('#myModal').hide();

});


$("#confirmbutton").click(function(){

var pass=$('#confirmpassword').val();
if(pass=="beta"){
var href = $("#deleteall").attr('href');
window.location.href = href;
alert("Successfully deleted all");
}
else{
	alert("Incorrect Password");
	$('#myModal').hide();
}


});




});
