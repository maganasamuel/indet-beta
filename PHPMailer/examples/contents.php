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
    <div>
       
    <table
        style="width: 498.75pt; background: #FDFDFD; margin-left: 6.75pt; margin-right: 6.75pt" width="665"
        cellspacing="0" cellpadding="0" border="0" align="left">
        <tbody>
            <tr>
                <td style="width: 498.75pt; padding: 0in 0in 0in 0in" width="665" valign="top">
                    <div align="center">
                        <table cellspacing="0" cellpadding="0" border="0">
                            <tbody>
                                <tr>
                                    <td style="width: 469.9pt; background: white; padding: 0in 0in 0in 0in" width="627"
                                        valign="top">
                                        <table cellspacing="0" cellpadding="0" border="0">
                                            <tbody>
                                                <tr style="height: 119.7pt">
                                                    <td colspan="2"
                                                        style="width: 456.4pt; padding: 0in 0in 0in 0in; height: 119.7pt"
                                                        width="609" valign="top">
                                                        <table cellspacing="0" cellpadding="0"
                                                            border="0">
                                                            <tbody>
                                                                <tr style="height: 15.75pt">
                                                                    <td style="width: 467.0pt; padding: 7.5pt 0in 0in 15.0pt; height: 15.75pt"
                                                                        width="623" valign="top"></td>
                                                                    <td style="width: 30.65pt; padding: 0in 0in 0in 0in; height: 15.75pt"
                                                                        width="41">
                                                                        <p 
                                                                            style="mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                                            <span
                                                                                style="font-size: 12.0pt; color: #282828">&nbsp;</span>
                                                                            <!-- o ignored -->
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr style="height: 54.15pt">
                                                                    <td colspan="2"
                                                                        style="width: 497.65pt; padding: 15.0pt 15.0pt 15.0pt 15.0pt; height: 54.15pt"
                                                                        width="664" valign="top">
                                                                        <table
                                                                            style="width: 467.65pt; margin-left: 6.75pt; margin-right: 6.75pt"
                                                                            width="624" cellspacing="0" cellpadding="0"
                                                                            border="0" align="left">
                                                                            <tbody>
                                                                                <tr style="height: 148.05pt">
                                                                                    <td style="width: 467.65pt; padding: 15.0pt 15.0pt 15.0pt 15.0pt; height: 148.05pt"
                                                                                        width="624" valign="top">
                                                                                        <p 
                                                                                            style="margin-bottom: 12.0pt; line-height: 150%; mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                                                            <span
                                                                                                style="font-size: 20.0pt; line-height: 150%; font-family: 'Arial','sans-serif'; color: #75B443"><?php echo $subject ?></span>
                                                                                            <!-- o ignored -->
                                                                                        </p>
                                                                                        <p 
                                                                                            style="margin-bottom: 12.0pt; line-height: 150%; mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                                                            <span
                                                                                                style="font-size: 10.5pt; line-height: 150%; font-family: 'Arial','sans-serif'; color: #282828"><?php echo $message ?>
                                                                                            </span>
                                                                                        </p>
                                                                                    </td>
                                                                                </tr>
                                                                            </tbody>
                                                                        </table>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table
                                                            style="margin-left: 6.75pt; margin-right: 6.75pt"
                                                            cellspacing="0" cellpadding="0" border="0" align="left">
                                                            <tbody>
                                                                <tr>
                                                                    <td style="width: 467.65pt; background: #FDFDFD; padding: 0in 0in 0in 0in"
                                                                        width="624" valign="top"></td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                        <table
                                                            style="background: #FDFDFD; margin-left: 9.1pt; margin-right: 6.8pt"
                                                            cellspacing="0" cellpadding="0" border="0" align="left">
                                                            <tbody>
                                                                <tr style="height: 231.5pt">
                                                                    <td style="width: 6.25in; padding: 7.5pt 7.5pt 7.5pt 7.5pt; height: 231.5pt"
                                                                        width="600" valign="top">
                                                                        <p 
                                                                            style="margin-bottom: 12.0pt; text-align: center; text-indent: -27.75pt; mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly"
                                                                            align="center"><span
                                                                                style="color: #282828"><a
                                                                                    href="http://www.nzfunds.co.nz"
                                                                                    target="_blank"
                                                                                    rel="noreferrer"><span
                                                                                        style="font-size: 10.0pt; font-family: 'Arial','sans-serif'; color: #282828">www.nzfunds.co.nz</span><span
                                                                                        style="color: #282828">
                                                                                    </span></a></span><!-- o ignored -->
                                                                        </p>
                                                                        <p 
                                                                            style="mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                                            <span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #282828">New
                                                                                Zealand Funds Management Limited ('NZ
                                                                                Funds') is the issuer of the NZ Funds
                                                                            </span><span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #333333">Advised</span><span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #282828">
                                                                                Portfolio Service, the NZ Funds
                                                                                KiwiSaver Scheme and the NZ Funds
                                                                                Managed Superannuation Service. Product
                                                                                disclosure statements for the NZ Funds
                                                                                Managed Portfolio Service, the NZ Funds
                                                                                KiwiSaver Scheme and the NZ Funds
                                                                            </span><span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #333333">Advis</span><span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #282828">ed
                                                                                Superannuation Service are available on
                                                                                our website at </span><span
                                                                                style="color: black"><a
                                                                                    href="http://www.nzfunds.co.nz"
                                                                                    target="_blank"
                                                                                    rel="noreferrer"><span
                                                                                        style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #282828">www.nzfunds.co.nz.
                                                                                    </span></a></span><!-- o ignored -->
                                                                        </p>
                                                                        <p 
                                                                            style="text-indent: -27.75pt; mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                                            <span
                                                                                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'; color: #282828"><br>DISCLAIMER:
                                                                                This document has been provided for
                                                                                information purposes only. The content
                                                                                of this document is not intended as a
                                                                                substitute for specific professional
                                                                                advice on investments, financial
                                                                                planning or any other matter. While the
                                                                                information provided in this document is
                                                                                stated accurately to the best of our
                                                                                knowledge and belief, NZ Funds, its
                                                                                directors, employees and related parties
                                                                                accept no liability or responsibility
                                                                                for any loss, damage, claim or expense
                                                                                suffered or incurred by any party as a
                                                                                result of reliance on the information
                                                                                provided and opinions expressed in this
                                                                                document except as required by law. Past
                                                                                performance is not indicative of future
                                                                                performance. <br><br>Copyright © New
                                                                                Zealand Funds Management Limited, All
                                                                                rights reserved. </span>
                                                                            <!-- o ignored -->
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>
                                                    </td>
                                                </tr>
                                                <tr style="height: 161.75pt">
                                                    <td style="width: 447.4pt; padding: 0in 0in 0in 0in; height: 161.75pt"
                                                        width="597" valign="top"></td>
                                                    <td style="width: 9.0pt; padding: 0in 0in 0in 0in; height: 161.75pt"
                                                        width="12">
                                                        <p 
                                                            style="mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                            <span style="color: #282828">&nbsp; </span>
                                                            <!-- o ignored -->
                                                        </p>
                                                    </td>
                                                </tr>
                                                <tr style="height: 4.0pt">
                                                    <td style="width: 447.4pt; padding: 0in 0in 0in 0in; height: 4.0pt"
                                                        width="597" valign="top"></td>
                                                    <td style="width: 9.0pt; padding: 0in 0in 0in 0in; height: 4.0pt"
                                                        width="12">
                                                        <p 
                                                            style="mso-element: frame; mso-element-frame-hspace: 9.0pt; mso-element-wrap: around; mso-element-anchor-vertical: paragraph; mso-element-anchor-horizontal: column; mso-height-rule: exactly">
                                                            <span style="color: #282828">&nbsp; </span>
                                                            <!-- o ignored -->
                                                        </p>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
            <tr>
                <td style="width: 498.75pt; padding: 0in 0in 0in 0in" id="social" width="665" valign="top"></td>
            </tr>
        </tbody>
    </table>
        <p >
            <hr>
            <strong><span
                style="font-size: 7.5pt; font-family: 'Arial','sans-serif'">Legal
                Information:&nbsp; </span></strong><span
            style="font-size: 7.5pt; font-family: 'Arial','sans-serif'">This message is for the
            named person's use only.&nbsp; It may contain confidential, proprietary or legally privileged
            information. No confidentiality or privilege is waived or lost by any mistransmission.&nbsp; If you
            receive this message in error, please immediately delete it and all copies of it from your system,
            destroy any hard copies of it and notify the sender.&nbsp; You must not, directly or indirectly, use,
            disclose, distribute, print, or copy any part of this message if you are not the intended
            recipient.&nbsp; New Zealand Funds Management Limited (NZ Funds) and any related parties reserve the
            right to monitor all e-mail communications through its networks.&nbsp; NZ Funds neither represents,
            warrants nor guarantees that the integrity of this message has been maintained and that it is free of
            any error, virus or other defect.&nbsp; NZ Funds accepts no liability for any loss, cost or damage
            resulting from the receipt of this message.&nbsp; Any views expressed in this message are those of the
            individual sender, except where the message states otherwise and the sender is authorised to state them
            to be the views of NZ Funds.</span><span
            style="font-size: 12.0pt; font-family: 'Times New Roman','serif'">
            <!-- o ignored --></span>
        </p>
    </div>
</div>
</body>
</html>
