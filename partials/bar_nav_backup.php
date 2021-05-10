<?php include "bootstrap.html"; ?>

<nav class="navbar navbar-default">
  <div class="container-fluid">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar" >
              <span class="sr-only">Toggle navigation</span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
              <span class="icon-bar"></span>
            </button>
      <a class="navbar-brand" href="main">INDET "the EliteInsure Tracker"</a>
    </div>
     <div class="navbar-collapse collapse" id='navbar'>
    <ul class="nav navbar-nav navbar">
      <!--
        <li>
          <a href="main"><span class="glyphicon glyphicon-home" aria-hidden="true"></span> <span class="glyphicon-class">Home</span>
          </a>
        </li>
      -->
      <!--ADMIN ONLY -->
      <?php
        if($_SESSION["myusertype"]=="Telemarketer"){

      ?>
        <li><a href="main"><span class="glyphicon glyphicon-home"></span> Home</a></li>
        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Clients
          <span class="caret"></span></a>
          <ul class="dropdown-menu">
            <li><a href="callbacks" class='mw' >Callbacks</a></li>
          </ul>
        </li>

        <li class="dropdown">
          <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Other Data
          <span class="caret"></span></a>
          <ul class="dropdown-menu">
              <li><a href="products" class='mw' >Products</a></li>
              <li><a href="scripts" class='mw' >Scripts</a></li>
              <li><a href="script_groups" class='mw' >Script Groups</a></li>
              <li><a href="insurance_types" class='mw' >Insurance Types</a></li>
              <li><a href="insurance_companies" class='mw' >Insurance Companies</a></li>
          </ul>
        </li>

      <?php
        }
        if($_SESSION['myusertype']=="Admin"){
      ?>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Adviser
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="add_adviser" class='mw' >Add Adviser</a></li>
          <li><a href="adviser_profiles" class='mw'>Adviser Profiles</a></li>
          <li><a href="add_team" class='mw' >Add Team</a></li>
          <li><a href="teams" class='mw'>Teams</a></li>
        </ul>
      </li>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Lead Generator
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="add_leadgen" class='mw' >Add Face to Face Marketer</a></li>
          <li><a href="leadgen_profiles" class='mw'>Face to Face Profiles</a></li>
          <li><a href="add_telemarketer" class='mw' >Add Telemarketer</a></li>
          <li><a href="telemarketer_profiles" class='mw'>Telemarketer Profiles</a></li>
        </ul>
      </li>
      <?php
        }
        
        if($_SESSION['myusertype']=="Admin"||$_SESSION['myusertype']=="User"){
      
      ?>
      <!--ADMIN ONLY -->
        
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Clients
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="add_client" class='mw' >Add Clients</a></li>
          <li><a href="client_profiles" class='mw'>Clients Profiles</a></li>

          <?php
            if($_SESSION['myusertype']=="Admin"){
          ?>
            <li><a href="clients_bin" class='mw'>Clients Bin</a></li>

          <?php
            }
          ?>
        </ul>
      </li>
       <?php
       
      }
            if($_SESSION['myusertype']=="Admin"){
          ?>
          <li class="dropdown">
            <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-user"></span> Deals
            <span class="caret"></span></a>
            <ul class="dropdown-menu">
                <li><a href="add_submission_client" class='mw' >Add Submission</a></li>
                <li><a href="submission_client_profiles" class='mw'>Submission Clients Profiles</a></li>
                <li><a href="add_issued_client" class='mw' >Add Issued Policy</a></li>
                <li><a href="issued_client_profiles" class='mw'>Issued Clients Profiles</a></li>
                <li><a href="cancelled_deals" class='mw'>Cancelled Deals</a></li>
            </ul>
          </li>

        <?php
          }
        ?>
      <!--ADMIN ONLY -->
      <?php
        if($_SESSION['myusertype']=="Admin"){
      ?>
      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-pencil"></span> Create Report
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="create_invoice" class='mw' >Create Invoice</a></li>
          <!--<li><a href="create_summary" class='mw'>Create Invoice Summary</a></li>-->
          <li><a href="create_production_report" class='mw' >Create Adviser Report</a></li>
          <li><a href="create_deal_tracker_report" class='mw' >Create Deal Tracker Report</a></li>
          <li><a href="create_team_deal_tracker_report" class='mw' >Create Team Deal Tracker Report</a></li>
          <!--
            <li><a href="create_deal_tracker_summary" class='mw' >Create Deal Tracker Summary</a></li>
          -->
          <li><a href="create_lead_gen_report" class='mw'>Create Lead Gen Report</a></li>
          <li><a href="create_client_data_report" class='mw'>Create Client Data Report</a></li>
          </ul>
      </li>

      <li class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#"><span class="glyphicon glyphicon-list-alt"></span> Reports
        <span class="caret"></span></a>
        <ul class="dropdown-menu">
          <li><a href="invoices" class='mw'>Invoice</a></li>
          <!--<li><a href="summaries" class='mw'>Invoice Summary</a></li>-->
          <li><a href="deals_reports" class='mw'>Production Reports</a></li>
          <li><a href="deal_tracker_reports" class='mw'>Deal Tracker Reports</a></li>
          <li><a href="team_deal_tracker_reports" class='mw'>Team Deal Tracker Reports</a></li>
          <!--
              <li><a href="deal_tracker_summaries" class='mw'>Deal Tracker Summaries</a></li>
          -->
          <li><a href="lead_gen_reports" class='mw'>Lead Gen Reports</a></li>
          <li><a href="client_databases" class='mw'>Client Database Reports</a></li>
        </ul>
      </li>

      <?php
        }
      ?>
      <!--ADMIN ONLY -->

    </ul>

    <ul class="nav navbar-nav navbar-right padded">
          <!--ADMIN ONLY -->
          <?php
            if($_SESSION['myusertype']=="Admin"){
          ?>
          <li>
            <a href="bin_entries"><span class="glyphicon glyphicon-trash"></span> Bin Entries</a>
          </li>
          <li>
            <a href="users"><span class="glyphicon glyphicon-user"></span> Users</a>
          </li>

          <li>
            <a href="settings"><span class="glyphicon glyphicon-cog"></span> Account Settings</a>
          </li>

        <!--ADMIN ONLY -->
        <?php
          }
        ?>
        <li><a href="index"><span class="glyphicon glyphicon-log-in"></span> Logout</a></li>
    </ul>
    </div>
  </div>
</nav>
  
  <script type="text/javascript">
$(function(){
  
$('.a').click(function(e){
  e.preventDefault();

var loc=$(this).attr('href');
$.confirm({
    title: 'Delete all',
    content: "Input Password: <input type='password' id='confirmpass' class='form-control'>",
    buttons: {
        confirm: function () {
          if($('#confirmpass').val()=="DELPASS"){
              location.href = loc;
          }
          else{
            $.dialog({
               title: 'Incorrect Password',
       content: 'Please try again!',
});
          }
       },
        cancel: function () {
     
        },

    }
});
     
});


$('.a_single').click(function(e){
  e.preventDefault();

var loc=$(this).attr('href');
$.confirm({
    title: 'Alert',
    content: "Are you sure you want to delete this permanently?",
    buttons: {
        confirm: function () {
    
              location.href = loc;

       },
        cancel: function () {
          
     
        },

    }
});
     
});



$('.a_single_email').click(function(e){
  e.preventDefault();

var loc=$(this).attr('href');
$.confirm({
    title: 'Alert',
    content: "Are you sure you want to send this?",
    buttons: {
        confirm: function () {
    $.confirm({
    title: 'Email Sent!',
    content: 'Invoice pdf has been sent.',
    buttons:{
      ok:function(){
        location.href = loc;
      }
    }
    });
              

       },
        cancel: function () {
          
     
        },

    }
});
     
});




$('.a_email').click(function(e){


  e.preventDefault();

var loc=$(this).attr('href');
$.confirm({
    title: 'Select Period to send email',
    content: '<div class="form"><select name="mymonth" class="form-control col-sm-2" id="mymonth" required=""><option value="" disabled="" selected="">Select month</option><option value="1">January</option><option value="2">February</option><option value="3">March</option><option value="4">April</option><option value="5">May</option><option value="6">June</option><option value="7">July</option><option value="8">August</option><option value="9">September</option><option value="10">October</option><option value="11">November</option><option value="12">December</option></select><select name="mydate" class="form-control col-sm-2" id="mydate" required=""><option value="" disabled="" selected="">Select period</option><option value="01"> 1-15</option><option id="nextdate" value="15"> 15 up to end of the month</option></select><select name="myyear" id="myyear" class="form-control col-sm-2" required=""><option value="2018">2018</option><option value="2017">2017</option><option value="2016">2016</option><option value="2015">2015</option><option value="2014">2014</option><option value="2013">2013</option><option value="2012">2012</option><option value="2011">2011</option><option value="2010">2010</option><option value="2009">2009</option><option value="2008">2008</option><option value="2007">2007</option><option value="2006">2006</option><option value="2005">2005</option><option value="2004">2004</option><option value="2003">2003</option><option value="2002">2002</option><option value="2001">2001</option><option value="2000">2000</option><option value="1999">1999</option><option value="1998">1998</option><option value="1997">1997</option><option value="1996">1996</option><option value="1995">1995</option><option value="1994">1994</option><option value="1993">1993</option><option value="1992">1992</option><option value="1991">1991</option><option value="1990">1990</option><option value="1989">1989</option><option value="1988">1988</option><option value="1987">1987</option><option value="1986">1986</option><option value="1985">1985</option><option value="1984">1984</option><option value="1983">1983</option><option value="1982">1982</option><option value="1981">1981</option><option value="1980">1980</option></select></div>',
    buttons: {
        confirm: function () {
    
             if($('#mymonth').val()!==null && $('#mydate').val()!==null){
              var month=0;
              if($('#mymonth').val()<10){
                month='0'+$('#mymonth').val();
              }else{
                month=$('#mymonth').val();
              }
                location.href = "email_pdf?id=all&startingdate="+$('#myyear').val()+month+$('#mydate').val();
              }
              else{
              $.dialog({
                title: 'Missing Information',
                content: 'Please try again!',
              });
            }

       },
        cancel: function () {
          
     
        },

    }
});
     




});






});
</script>