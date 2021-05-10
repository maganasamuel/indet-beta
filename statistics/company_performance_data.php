<!-- Third Column -->
<div class="row">
    <div class="col-sm-12">
        <h4>
            <strong style="text-decoration: underline;"> All-Time Performance </strong>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Net API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->totals->issued_deals_api - $data->totals->cancelled_deals_api, 2) ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Submissions:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->total_submissions; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Submission API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->total_submission_api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Issued Policies:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->totals->issued_deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Issued API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->totals->issued_deals_api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Pending Applications:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->totals->pending_deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Pending API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->totals->pending_deals_api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Cancellations:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->totals->cancelled_deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Cancellations API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->totals->cancelled_deals_api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total KiwiSavers:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->totals->kiwisaver_deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total KiwiSavers API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->totals->kiwisaver_deals_api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->total_leads ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Face-To-Face Marketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data_total ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Telemarketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->telemarketers_data_total ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Total Self-Generated Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->self_gen_data_total ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <h4>
            <strong style="text-decoration: underline;"> Current Annual Performance </strong>
        </h4>
    </div>
</div>


<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Net API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->api - $data->cancellations_data->api, 2) ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Submissions:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->submissions_data->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Submission API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->submissions_data->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Issued Policies:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->issued_data->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Issued API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Cancellations:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->cancellations_data->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Cancellations API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->cancellations_data->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual KiwiSavers:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->kiwisavers_data->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual KiwiSavers API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->kiwisavers_data->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data->quantity + $data->telemarketers_data->quantity + $data->self_gen_data->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Face-To-Face Marketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Telemarketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->telemarketers_data->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Annual Self-Generated Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->self_gen_data->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <h4>
            <strong style="text-decoration: underline;"> Current Quarterly Performance </strong>
        </h4>
    </div>
</div>
<?php
$today = date('Ymd');
$current_quarter = 0;

if ($today <= $data->second_quarter->to->format("Ymd") && $today >= $data->second_quarter->from->format("Ymd")) {
    $current_quarter = 1;
} elseif ($today <= $data->third_quarter->to->format("Ymd") && $today >= $data->third_quarter->from->format("Ymd")) {
    $current_quarter = 2;
} elseif ($today <= $data->fourth_quarter->to->format("Ymd") && $today >= $data->fourth_quarter->from->format("Ymd")) {
    $current_quarter = 3;
}

$curr_month = date('m');
?>



<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Net API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->quarters[$current_quarter]->api - $data->cancellations_data->quarters[$current_quarter]->api, 2) ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Submissions:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->submissions_data->quarters[$current_quarter]->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Submission API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->submissions_data->quarters[$current_quarter]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Issued Policies:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->issued_data->quarters[$current_quarter]->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Issued API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->quarters[$current_quarter]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Cancellations:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->cancellations_data->quarters[$current_quarter]->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Cancellations API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->cancellations_data->quarters[$current_quarter]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly KiwiSavers:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->kiwisavers_data->quarters[$current_quarter]->quantity; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly KiwiSavers API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->kiwisavers_data->quarters[$current_quarter]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data->quarters[$current_quarter]->quantity + $data->telemarketers_data->quarters[$current_quarter]->quantity + $data->self_gen_data->quarters[$current_quarter]->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Face-To-Face Marketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data->quarters[$current_quarter]->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Telemarketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->telemarketers_data->quarters[$current_quarter]->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Quarterly Self-Generated Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->self_gen_data->quarters[$current_quarter]->quantity ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-12">
        <h4>
            <strong style="text-decoration: underline;"> Current Monthly Performance </strong>
        </h4>
    </div>
</div>



<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Net API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->months[$curr_month - 1]->api - $data->cancellations_data->months[$curr_month - 1]->api, 2) ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Submissions:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->submissions_data->months[$curr_month - 1]->deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Submission API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->submissions_data->months[$curr_month - 1]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Issued Policies:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->issued_data->months[$curr_month - 1]->deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Issued API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->issued_data->months[$curr_month - 1]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Cancellations:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->cancellations_data->months[$curr_month - 1]->deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Cancellations API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->cancellations_data->months[$curr_month - 1]->api, 2); ?>
        </h4>
    </div>
</div>


<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly KiwiSavers:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->kiwisavers_data->months[$curr_month - 1]->deals; ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly KiwiSavers API:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            $<?php echo number_format($data->kiwisavers_data->months[$curr_month - 1]->api, 2); ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php
            echo $data->bdms_data->months[$curr_month - 1]->deals + $data->telemarketers_data->months[$curr_month - 1]->deals + $data->self_gen_data->months[$curr_month - 1]->deals
            ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Face-To-Face Marketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->bdms_data->months[$curr_month - 1]->deals ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Telemarketer Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->telemarketers_data->months[$curr_month - 1]->deals ?>
        </h4>
    </div>
</div>

<div class="row">
    <div class="col-sm-6">
        <h4>
            <strong>Monthly Self-Generated Leads:</strong>
        </h4>
    </div>
    <div class="col-sm-6">
        <h4>
            <?php echo $data->self_gen_data->months[$curr_month - 1]->deals ?>
        </h4>
    </div>
</div>