<?php
    ob_start();
    date_default_timezone_set('Pacific/Auckland');
    
    $restrict_session_check = true;
    require "database.php";
    $today = date("md");

    $current_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://";
    $current_link .= "{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}";
    $current_link = str_replace("/birthday_email_new", "/unsubscribe", $current_link);

    $query = "SELECT s.id as sub_id, s.deals, c.id as client_id, c.lead_by, i.id,c.name,a.name as x, a.email as adviser_email, l.name as y,i.appt_date,i.appt_time,i.address,i.leadgen,i.assigned_to,i.assigned_date,i.type_of_lead,i.issued,i.date_issued,i.notes FROM submission_clients s LEFT JOIN issued_clients_tbl i ON s.client_id = i.name LEFT JOIN adviser_tbl a ON i.assigned_to = a.id LEFT JOIN leadgen_tbl l ON i.leadgen = l.id LEFT JOIN clients_tbl c ON i.name = c.id order by i.date_issued desc";
    //echo $query;
    $result = mysqli_query($con,$query);

    WHILE($rows = mysqli_fetch_array($result)):
        
    $deals = json_decode($rows["deals"]);
    $index = 0;
    //Go through deals and check if there is a cancelled deal.
    foreach($deals as $deal){
        if($deal->status="Issued"){            
            if(isset($deal->clawback_status)){
                if($deal->clawback_status!="Cancelled"){             
                    //First condition check if there is birthday and email
                    if(!isset($deal->unsubscribed)){
                        if(isset($deal->birthday) && isset($deal->email)){
                            if((!empty($deal->birthday) && !empty($deal->email))){
                                $bday = substr($deal->birthday,4);
                                if($today==$bday){
                                    $submissions_id = $rows["sub_id"];
                                    $unsub_key = "";
    
                                    if((!isset($deal->unsub_key))) {
                                        $unsub_key = md5(uniqid());
                                        CreateUnsubKey($unsub_key, $submissions_id, $index, $con, true);
                                    }
                                    else{
                                        $unsub_key = $deal->unsub_key;
                                    }
                                    
                                    $to = $deal->email;
                                    $subject = 'Happy Birthday';
                                    $from = 'EliteInsure Team <admin@eliteinsure.co.nz>';
                                    
                                    // To send HTML mail, the Content-type header must be set
                                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                                    
                                    // Create email headers
                                    $headers .= 'From: '.$from."\r\n".
                                        'Reply-To: '.$from."\r\n" .
                                        'X-Mailer: PHP/' . phpversion();
                                    
                                    // Compose a simple HTML email message
                                    $message = '<html><body>';
                                    $message .= '<h1 style="color:#000;">Hi ' . $rows["name"]  . ',</h1>';
                                    $message .= '<p style="color:#000;font-size:18px;">
                                        We at EliteInsure wish you great health and fortune on your birthday.<br>
                                        We are grateful that you are one of our many trusting clients. <br>
                                        May the next year be greater for you and your family! 
                                    </p>';
    
                                    $message .= '
                                        <h3>
                                            Regards,
                                            <br>
                                            EliteInsure
                                        </h3>
                                    ';
    
                                    $message .= '
                                    <hr>
                                    <small>
                                        Don\'t like receiving our emails? Click this <a href="' . $current_link . '?id=' . $submissions_id . "&key=" . $unsub_key . '&primary=true">link</a> to unsubscribe.
                                    </small>
                                    ';
    
                                    $message .= '</body></html>';
                                    // Sending email
                                    if(mail($to, $subject, $message, $headers)){
                                        echo 'Your mail has been sent successfully.';
                                    } else{
                                        echo 'Unable to send email. Please try again.';
                                    }
                                }

                            }
                        }
                    }

                    if(!isset($deal->unsubscribed2)){
                        if(isset($deal->secondary_birthday) && isset($deal->secondary_email)){
                            if((!empty($deal->secondary_birthday) && !empty($deal->secondary_email))){
                                $bday = substr($deal->secondary_birthday,4);
                                if($today==$bday){    
                                    $submissions_id = $rows["sub_id"];
                                    $unsub_key = "";
    
                                    if((!isset($deal->unsub_key2))) {
                                        $unsub_key = md5(uniqid());
                                        CreateUnsubKey($unsub_key, $submissions_id, $index, $con, false);
                                    }
                                    else{
                                        $unsub_key = $deal->unsub_key2;
                                    }
                                    
                                    $to = $deal->secondary_email;
                                    $subject = 'Happy Birthday';
                                    $from = 'EliteInsure Team <admin@eliteinsure.co.nz>';
                                    
                                    // To send HTML mail, the Content-type header must be set
                                    $headers  = 'MIME-Version: 1.0' . "\r\n";
                                    $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
                                    
                                    // Create email headers
                                    $headers .= 'From: '.$from."\r\n".
                                        'Reply-To: '.$from."\r\n" .
                                        'X-Mailer: PHP/' . phpversion();
                                    
                                    // Compose a simple HTML email message
                                    $message = '<html><body>';
                                    $message .= '<h1 style="color:#000;">Hi ' . $deal->life_insured  . ',</h1>';
                                    $message .= '<p style="color:#000;font-size:18px;">
                                        We at EliteInsure wish you great health and fortune on your birthday.<br>
                                        We are grateful that you are one of our many trusting clients. <br>
                                        May the next year be greater for you and your family! 
                                    </p>';
    
                                    $message .= '
                                        <h3>
                                            Regards,
                                            <br>
                                            EliteInsure
                                        </h3>
                                    ';
    
                                    $message .= '
                                    <hr>
                                    <small>
                                        Don\'t like receiving our emails? Click this <a href="' . $current_link . '?id=' . $submissions_id . "&key=" . $unsub_key . '&primary=false">link</a> to unsubscribe.
                                    </small>
                                    ';
    
                                    $message .= '</body></html>';
                                    // Sending email
                                    if(mail($to, $subject, $message, $headers)){
                                        echo 'Your mail has been sent successfully.';
                                    } else{
                                        echo 'Unable to send email. Please try again.';
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }
        $index++;
    }

    endwhile;

    function CreateUnsubKey($key, $id, $index, $con, $primary){
        $query = "SELECT * FROM submission_clients WHERE id = $id";
        $result = mysqli_query($con, $query);
        $row = mysqli_fetch_assoc($result);

        $deals = json_decode($row["deals"]);
        $ctr = 0;

            foreach($deals as $deal){
                //This is the deal that needs the key
                if($ctr==$index){     
                    if($primary){
                        $deal->unsub_key = $key;
                    }
                    else{
                        $deal->unsub_key2 = $key;
                    }
                    break;
                }
                $ctr++;
            }

        $deals = json_encode($deals);

        $query = "UPDATE submission_clients SET deals = '$deals' WHERE id = $id";
        $result = mysqli_query($con, $query);
    }
  ob_end_flush();
?>