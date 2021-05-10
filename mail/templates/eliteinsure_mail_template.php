<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>PHPMailer Test</title>
</head>
<body>
    <div style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
        <?php if(isset($banner_img)){
            if(!empty($banner_img)){
                if(file_exists($banner_img))
                    echo "<img src='$banner_img' style='text-align:center; width:50%; margin-left: 25%; margin-right: 25%;'>";
            }
        }
    ?>
    </div>
    <div style='width:50%; margin-left: 25%; margin-right: 25%; font-family: arial;'>
        <h1 style="color: #1f497d;"><?php echo $subject ?></h1>
        <h4 style="text-align:left; "><?php echo $message ?></h4>
    <hr>
    
        <table style="height: 100px;" width="412">
        <tbody>
        <tr>
        <td width="121">&nbsp;<img src="images/eliteinsure_logo.png" width="110" height="70" /></td>
        <td width="246">
        <p style="margin: 0;"><span style="font-family: arial black, sans-serif; color: #1f497d; font-size: 8.85pt;">Jesse Dwight Hernandez</span></p>
        <p style="margin: 0;"><span style="font-family: arial, helvetica, sans-serif; color: #1f497d; font-size: 8.85pt;">IT Support,</span></p>
        <p style="margin: 0;"><span style="font-family: arial, helvetica, sans-serif; color: #1f497d; font-size: 8.85pt;">Telephone: 0508 123 467</span></p>
        <p style="margin: 0;"><span style="font-family: arial, helvetica, sans-serif; color: #1f497d; font-size: 8.85pt;">Email: <a style="color: #1f497d;" href="mailto:jesse@eliteinsure.co.nz">jesse@eliteinsure.co.nz</a></span></p>
        <p style="margin: 0;"><span style="font-family: arial, helvetica, sans-serif; color: #1f497d; font-size: 8.85pt;">Website: <a style="color: #1f497d;" href="http://www.eliteinsure.co.nz/">www.eliteinsure.co.nz</a></span></p>
        </td>
        </tr>
        </tbody>
        </table>
        <p><span style="font-family: arial, helvetica, sans-serif; font-size: 8pt;">DISCLAIMER: This communication contains information which is confidential and may also be privileged. It is for the exclusive use of the intended recipient(s). If you are not the intended recipient(s) please note that any distribution, copying or use of this communication or the information in it is strictly prohibited. If you have received this communication in error please notify us by email (<a href="mailto:admin@eliteinsure.co.nz">admin@eliteinsure.co.nz</a>) and then delete the email from your system together with any copies of it. All communication sent to and from the firm are subject to monitoring of content. By using this method of communication you give consent to the monitoring of such communications. Any views or opinions are solely those of the author and do not necessarily represent those of the firm unless specifically stated</span></p>
        <p><span style="font-family: arial, helvetica, sans-serif; font-size: 8pt;">&nbsp;</span></p>
        </body>
        </html>

    </div>