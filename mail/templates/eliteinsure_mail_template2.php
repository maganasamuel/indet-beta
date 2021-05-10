<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title>PHPMailer Test</title>
</head>
<body style="background-color:#0c4664;">
    <!--Container-->
    <div style="margin-left:20%; width: 60%; margin-top: 5rem;">
        <table width="100%" cellspacing="0" cellpadding="0" border="0" align="center">
            <tbody>
                <tr>
                    <td style="mso-line-height-rule:exactly;width: 100%; height: 100%;" align="center">
                        <a href="#" style="text-align:right; color:#FFF; margin-top: 1rem; width:100%;">
                            <img 
                            src="images/location-arrow-solid.svg" 
                            alt="triangle with all three sides equal"
                            height="25"
                            width="25" />
                        Click here to view online</a>
                    </td>
                </tr>
            </tbody>
        </table>
        <!--Paper-->
        <div style="padding:5% 5%; background-color:white; width: 100%;">

            <!--Header-->
            <div style="font-family: Arial, Helvetica, sans-serif; font-size: 11px;">
                <?php if (isset($banner_img)) {
    if (!empty($banner_img)) {
        if (file_exists($banner_img)) {
            echo "<img src='$banner_img' style='text-align:center; width:100%;'>";
        }

    }
}
?>
            </div>

            <!--Body-->
            <div style='width:100%; font-family: arial; margin-top:5rem;'>
                <?php echo $message ?>


            </div>


            <!--Footer-->
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
            <div>
                <hr>
            <img src="images/partners_footer.png" style="width:100%;" />
            </div>
        </div>

    </div>
</body>
</html>