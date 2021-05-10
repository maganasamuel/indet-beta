<?php
date_default_timezone_set('Pacific/Auckland');

$query = "SELECT *, u.id as user_id, l.id as telemarketer_id, u.type as usertype, l.type as leadgen_type FROM users u LEFT JOIN leadgen_tbl l ON u.linked_id = l.id WHERE u.id = " . $_SESSION['myuserid'];
$result = mysqli_query($con, $query);
$agent = (object) mysqli_fetch_assoc($result);

$query = "SELECT * FROM scripts order by script_group, caption";
$result = mysqli_query($con, $query);
$app_data = new stdClass();

$app_data->isLoadingSavedData = false;
$app_data->agent_id = $agent->linked_id;
$app_data->scripts = array();       //Data to be used by PHP
$app_data->scripts_only = array();  //To load to javascript
$app_data->insurance_companies = array();
$ctr = 0;

while ($row = mysqli_fetch_assoc($result)) {
    $script = (object) $row;
    $script->script = TelemarketerScriptTranslator($row["script"]);
    $script->script_index = $ctr;

    $app_data->scripts[] = $script;
    $app_data->scripts_only[] = TelemarketerScriptTranslator($row["script"]);
    $ctr++;
}


if (!empty($_POST)) {
    $leads_data_id = $_POST["leads_data_id"];

    $query = "SELECT * FROM leads_data WHERE id = $leads_data_id";
    $result = mysqli_query($con, $query);
    $row = mysqli_fetch_assoc($result);
    $app_data->callback_id = $_POST["callback_id"];
    $app_data->isLoadingSavedData = true;
    $app_data->lead_data = $row["data"];
}

$it_query = "Select * from insurance_types ORDER BY description";
$it_result = mysqli_query($con, $it_query);

$app_data->insurance_types = array();

while ($it_rows = mysqli_fetch_assoc($it_result)) {
    $insurance_type = (object) $it_rows;

    if ($insurance_type->acronym == "") {
        $insurance_type->acronym = $insurance_type->description;
        //echo $insurance_type->acronym;
    }
    //$insurance_type->acronym == $insurance_type->description;

    $app_data->insurance_types[] = $insurance_type;
}

$p_query = "Select * from products ORDER BY acronym, name ASC";
$p_result = mysqli_query($con, $p_query);
$app_data->products = array();

while ($p_rows = mysqli_fetch_assoc($p_result)) {
    $product = (object) $p_rows;


    $p_acronym = $product->acronym;

    if ($p_acronym == "") {
        $p_acronym = $product->name;
    }

    $product->final_acronym = $p_acronym;

    $product->safe_name =  preg_replace("/[^a-zA-Z0-9\s]/", "", $p_acronym);
    $product->safe_name = str_replace(' ', '_', strtolower($product->safe_name));

    $app_data->products[] = $product;
}

$ic_query = "Select * from insurance_companies ORDER BY name";
$ic_result = mysqli_query($con, $ic_query);

$app_data->insurance_companies = array();

while ($ic_rows = mysqli_fetch_assoc($ic_result)) {
    $insurance_company = (object) $ic_rows;

    if ($insurance_company->acronym == "")
        $insurance_company->acronym = $insurance_company->name;

    $app_data->insurance_companies[] = $insurance_company;
}

echo "
    <script>
        $(function(){
            LoadScripts(" . json_encode($app_data->scripts_only) . ");
            LoadInsuranceTypes(" . json_encode($app_data->insurance_types) . ");
            LoadInsuranceCompanies(" . json_encode($app_data->insurance_companies) . ");
            LoadProducts(" . json_encode($app_data->products) . ");
        });
    </script>
    ";
?>



<style>
    .material-switch>input[type="checkbox"] {
        display: none;
    }

    .material-switch>label {
        cursor: pointer;
        height: 0px;
        position: relative;
        width: 40px;
    }

    .material-switch>label::before {
        background: rgb(0, 0, 0);
        box-shadow: inset 0px 0px 10px rgba(0, 0, 0, 0.5);
        border-radius: 8px;
        content: '';
        height: 16px;
        margin-top: -8px;
        position: absolute;
        opacity: 0.3;
        transition: all 0.4s ease-in-out;
        width: 40px;
    }

    .material-switch>label::after {
        background: rgb(255, 255, 255);
        border-radius: 16px;
        box-shadow: 0px 0px 5px rgba(0, 0, 0, 0.3);
        content: '';
        height: 24px;
        left: -4px;
        margin-top: -8px;
        position: absolute;
        top: -4px;
        transition: all 0.3s ease-in-out;
        width: 24px;
    }

    .material-switch>input[type="checkbox"]:checked+label::before {
        background: inherit;
        opacity: 0.5;
    }

    .material-switch>input[type="checkbox"]:checked+label::after {
        background: inherit;
        left: 20px;
    }

    .scrollable_column {
        height: 700px;
        overflow-y: scroll;
        //background-color:gray;
    }

    .script_button {
        white-space: normal;
        word-wrap: break-word;
        width: 100%;
    }

    .panel-transparent {
        background: none;
    }

    .panel-transparent .panel-heading {
        background: rgba(122, 130, 136, 0.2) !important;
    }

    .panel-transparent .panel-body {
        background: rgba(46, 51, 56, 0.2) !important;
    }
</style>
<div style="overflow: hidden;">
    <div class="row">
        <div class="col-sm-2 scrollable_column" id="script_buttons">
            <!-- First Column -->
            <?php
            //Show Scripts with corresponding Script Group
            $sg_query = "Select * from script_groups ORDER BY priority, name ASC";
            $sg_result = mysqli_query($con, $sg_query);
            while ($sg_row = mysqli_fetch_assoc($sg_result)) {
                extract($sg_row);
                $sg_panel_name =  preg_replace("/[^a-zA-Z0-9\s]/", "", $sg_row["name"]);
                $sg_panel_name = (str_replace(' ', '_', strtolower($sg_panel_name))) . "_panel";
                ?>


            <div class="panel-group panel-transparent">
                <div class="panel panel-transparent">
                    <div class="panel-heading">
                        <a data-toggle="collapse" href="#<?php echo $sg_panel_name ?>">
                            <h4 class="panel-title"><?php echo $sg_row["name"]; ?></h4>
                        </a>
                    </div>
                    <div id="<?php echo $sg_panel_name ?>" class="panel-collapse collapse">
                        <div class="row">
                            <?php
                                $ctr = 0;
                                foreach ($app_data->scripts as $script) {
                                    if ($script->script_group == $sg_row["name"]) {
                                        ?>
                            <div class="row">
                                <div class="col">
                                    <button type="button" class="btn btn-primary script_button" data-index="<?php echo $script->script_index ?>">
                                        <?php echo $script->caption ?>
                                    </button>
                                </div>
                            </div>
                            <p></p>
                            <?php
                                    }
                                    $ctr++;
                                }
                                ?>
                        </div>
                    </div>
                </div>
            </div>

            <?php
            }
            ?>


            <!-- First Column -->
        </div>

        <div class="col-sm-6 scrollable_column">
            <!-- Second Column -->
            <div class="row">
                <div class="col">
                    <h4 class="speech" style="margin-top:20px;" id="script_header">

                    </h4>
                </div>
            </div>
        </div>
        <div class="col-sm-4 scrollable_column">
            <div class="row">
                <div class="col">

                    <div class="form-group">
                        <div class="col">
                            <label for="name">Number Tester</label>
                        </div>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" id="number_test" name="number_test" aria-describedby="number_test" placeholder="Lead's Number">
                        </div>
                        <div class="col-sm-4">
                            <button type="text" class="form-control btn btn-primary" id="test_number"><i class="fas fa-check"></i> Test</button>
                        </div>

                        <small id="number_test_feedback" style="color:black;">Please check if the number is okay to call here.</small>
                    </div>


                    <form id="frmLead" style="display:none;" name="frmLead" novalidate="">

                        <div class="form-group">
                            <label for="exampleInputEmail1">Agent</label>
                            <input type="text" readonly="" class="form-control" id="agent" name="agent" aria-describedby="agent" value="<?php echo $agent->name ?>">
                            <input type="hidden" readonly="" class="form-control" id="agent_id" name="agent_id" aria-describedby="agent_id" value="<?php echo $app_data->agent_id ?>">
                        </div>


                        <hr>
                        <h4 id="appointment_information">Appointment Information</h4>

                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <label for="venue">Venue</label>
                                </div>
                                <div class="col-sm-10">
                                    <select class="form-control" id="venue_type" name="venue_type" aria-describedby="venue_type">
                                        <option>Home</option>
                                        <option>Company</option>
                                        <option>Other</option>
                                    </select>
                                    <p></p>
                                    <textarea class="form-control" style="display:none;" id="venue" name="venue" placeholder="Appointment Venue"></textarea>
                                </div>
                            </div>
                        </div>
                        <p></p>
                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <label for="date">Date</label>
                                </div>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control datepicker" id="appointment_date" name="appointment_date" aria-describedby="date" placeholder="Date" value="<?php echo date('d/m/Y') ?>">
                                </div>
                            </div>
                        </div>
                        <p></p>

                        <div class="row">
                            <div class="form-group">
                                <div class="col-sm-2">
                                    <label for="venue">Time</label>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" id="appointment_hour" name="appointment_hour" aria-describedby="appointment_hour">
                                        <option>01</option>
                                        <option>02</option>
                                        <option>03</option>
                                        <option>04</option>
                                        <option>05</option>
                                        <option>06</option>
                                        <option>07</option>
                                        <option>08</option>
                                        <option>09</option>
                                        <option>10</option>
                                        <option>11</option>
                                        <option>12</option>
                                    </select>
                                </div>
                                <div class="col-sm-1">
                                    <h3 style="margin-top:0px;">:</h3>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" id="appointment_minute" name="appointment_minute" aria-describedby="appointment_hour">
                                        <option>00</option>
                                        <option>15</option>
                                        <option>30</option>
                                        <option>45</option>
                                    </select>
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control" id="appointment_period" name="appointment_period" aria-describedby="appointment_hour">
                                        <option>AM</option>
                                        <option>PM</option>
                                    </select>
                                </div>
                            </div>

                        </div>

                        <hr>
                        <h4 id="client_information">Lead's Information</h4>

                        <div class="form-group">
                            <label for="name">Name</label>
                            <input type="text" class="form-control" id="client_name" name="client_name" aria-describedby="name" placeholder="Lead's Name">
                        </div>

                        <div class="form-group">
                            <label for="client_company_name">Company Name</label>
                            <input type="text" class="form-control" id="client_company_name" name="company_name" placeholder="Company Name">
                        </div>

                        <div class="form-group">
                            <label for="name">Gender</label>
                            <label class="radio-inline">
                                <input type="radio" id="client_gender_male" name="client_gender" checked value="male">Male
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="client_gender_female" name="client_gender" value="female">Female
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="address">Home Address</label>
                            <textarea class="form-control" id="client_address" name="client_address" placeholder="Lead's Address"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="client_city">City</label>
                            <input type="text" class="form-control" id="client_city" name="client_city" placeholder="Client's City Address">
                        </div>
                        <div class="form-group">
                            <label for="client_zipcode">Zipcode</label>
                            <input type="text" class="form-control" id="client_zipcode" name="client_zipcode" placeholder="Client's Zipcode Address">
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-8">
                                <label for="landline_num" style="margin-top:10px;">Landline Number(s)</label>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="add_landline" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Number</button>
                            </div>
                        </div>

                        <div id="landline_numbers_div">
                            <div class="form-group row" id="client_landline_div0">
                                <div class="col-sm-9">
                                    <input type="text" class="form-control client_landline" id="client_landline0" name="client_landline" style="margin-top:20px;" placeholder="Landline #">
                                </div>
                            </div>
                        </div>
                        <p></p>

                        <div class="form-group row">
                            <div class="col-sm-8">
                                <label for="mobile_num" style="margin-top:10px;">Mobile Number(s)</label>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="add_mobile" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Number</button>
                            </div>
                        </div>

                        <div id="mobile_numbers_div">
                            <div class="form-group row" id="client_mobile_div0">
                                <div class="col-sm-9">
                                    <input type="text" class="form-control client_mobile" id="client_mobile0" name="client_mobile" style="margin-top:20px;" placeholder="Mobile #">
                                </div>
                            </div>
                        </div>
                        <p></p>

                        <div class="form-group row">
                            <div class="col-sm-8">
                                <label for="email" style="margin-top:10px;">Email(s)</label>
                            </div>
                            <div class="col-sm-4">
                                <button type="button" id="add_email" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Email</button>
                            </div>
                        </div>

                        <div id="emails_div">
                            <div class="form-group row" id="client_email_div0">
                                <div class="col-sm-9">
                                    <input type="text" class="form-control client_email" id="client_email0" name="client_email" style="margin-top:20px;" placeholder="Email">
                                </div>
                            </div>
                        </div>
                        <p></p>

                        <div class="form-group">
                            <label for="civil_status">Civil Status</label>
                            <select class="form-control" id="client_civil_status" name="client_civil_status" aria-describedby="civil_status">
                                <option>N/A</option>
                                <option>Single</option>
                                <option>Married</option>
                                <option>DeFacto</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="children">Number of Dependents</label>
                            <select class="form-control" id="client_dependents" name="client_dependents" aria-describedby="client_dependents">
                                <option>N/A</option>
                                <option>0</option>
                                <option>1</option>
                                <option>2</option>
                                <option>3</option>
                                <option>4</option>
                                <option>5</option>
                                <option>5+</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="client_age">Age</label>
                            <select class="form-control" id="client_age" name="client_age" aria-describedby="client_age">
                                <option>N/A</option>
                                <option value="<25">
                                    < 25</option> <option>25-39
                                </option>
                                <option>40-49</option>
                                <option>50-59</option>
                                <option value=">60">> 60</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="client_occupation">Occupation</label>
                            <input type="text" class="form-control" id="client_occupation" name="client_occupation" aria-describedby="client_occupation" placeholder="Lead's Work/Occupation" value="">
                        </div>

                        <div class="form-group">
                            <label for="company_address">Company Address</label>
                            <textarea class="form-control" id="company_address" name="company_address" placeholder="Company Address"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="client_income">Income</label>
                            <select class="form-control" id="client_income" name="client_income" aria-describedby="civil_status">
                                <option>N/A</option>
                                <option>Under $50,000</option>
                                <option>$50,000-$79,000</option>
                                <option>$80,000-$100,000</option>
                                <option>more than $100,000</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="client_citizenship">Citizenship</label>
                            <p></p>
                            <label class="radio-inline">
                                <input type="radio" id="client_nz_citizenship" name="client_citizenship" checked value="NZ Citizen">&nbsp;NZ Citizen
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="client_permanent_resident" name="client_citizenship" value="Permanent Resident">&nbsp;Permanent Resident
                            </label>
                            <label class="radio-inline">
                                <input type="radio" id="client_working_visa" name="client_citizenship" value="Working VISA">&nbsp;Working VISA
                            </label>
                        </div>

                        <div class="form-group working_visa" id="working_visa_years_div" style="display:none;">
                            <label class="form-text-label" for="working_visa_years">Number of Years</label>
                            <select class="form-control" id="working_visa_years" name="working_visa_years" aria-describedby="civil_status">
                                <option value="" disabled="" hidden="" selected="">Select One</option>
                                <option>N/A</option>
                                <option value="<1">
                                    < 1 Year</option> <option value="1">1 Year
                                </option>
                                <option value="2">2 years</option>
                                <option value=">2">> 2 years</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="health">Health Concerns:</label>
                            <input type="text" class="form-control client_health_concerns" id="client_health_concerns" name="client_health_concerns" placeholder="Health Concern 1" value="">
                            <input type="text" class="form-control client_health_concerns" id="client_health_concerns" name="client_health_concerns" placeholder="Health Concern 2" value="">
                            <input type="text" class="form-control client_health_concerns" id="client_health_concerns" name="client_health_concerns" placeholder="Health Concern 3" value="">
                        </div>
                        <p></p>

                        <div class="form-group row">
                            <div class="col-sm-8">
                                <h4 id="client_smoking">Smoker
                                    <div class="pull-right">
                                        <input id="client_is_smoking" name="client_is_smoking" type="checkbox" />
                                    </div>
                                </h4>
                            </div>
                        </div>
                        <p></p>
                        <div id="client_current_insurances">
                            <div class="form-group row">
                                <div class="col-sm-8">
                                    <h4 id="client_current_insurance">Already Has Insurance
                                        <div class="pull-right">
                                            <input id="client_already_has_insurance" name="client_has_insurance" type="checkbox" />
                                        </div>
                                    </h4>
                                </div>
                                <div class="col-sm-4">
                                    <button type="button" style="display:none;" id="add_client_current_insurance" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Insurance</button>
                                </div>
                            </div>


                            <div id="client_current_insurance_div" style="display:none;">
                                <div class="row" id="client_current_insurance0">
                                    <div class="form-group col-sm-5">
                                        <label for="client_current_insurance_type0">Insurance For:</label>
                                        <select class="form-control client_insurance_type" id="client_current_insurance_type0" name="client_current_insurance_type" aria-describedby="civil_status">
                                            <option value="" disabled="" hidden="" selected="">Select One</option>
                                            <option>N/A</option>
                                            <?php
                                            foreach ($app_data->insurance_types as $it) {
                                                $acronym = $it->acronym;

                                                echo "<option>$acronym</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-5">
                                        <label for="client_current_insurance_company0">Insurance Company:</label>
                                        <select class="form-control client_insurance_company" id="client_current_insurance_company0" name="client_current_insurance_company" aria-describedby="civil_status">
                                            <option value="" disabled="" hidden="" selected="">Select One</option>
                                            <option>N/A</option>
                                            <?php
                                            foreach ($app_data->insurance_companies as $ic) {
                                                $acronym = $ic->acronym;

                                                echo "<option>$acronym</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger form-control remove_client_current_insurance" style="margin-top:20px;" data-index="0"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr>
                        <div id="client_interested_in_div">
                            <h4>Client is interested in:</h4>

                            <?php
                            foreach ($app_data->products as $product) {
                                $final_acronym = $product->final_acronym;
                                $interest_id_name = "client_interested_in_" . $product->safe_name;
                                ?>

                            <div class="col-sm-12">
                                <input id="<?php echo $interest_id_name; ?>" name="<?php echo $interest_id_name; ?>" type="checkbox" />
                                <label for="<?php echo $interest_id_name; ?>"> &nbsp; <?php echo $final_acronym ?> </label>
                            </div>
                            <?php
                            }
                            ?>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-sm-8">
                                <h4 id="partner_information">Partner's Information
                                    <div class="material-switch pull-right" style="margin-top:10px;">
                                        <input id="partner_toggle" name="has_partner" type="checkbox" />
                                        <label for="partner_toggle" class="label-primary"></label>
                                    </div>
                                </h4>
                            </div>
                        </div>
                        <div id="partner_div" style="display:none;">

                            <div class="form-group">
                                <label for="name">Partner's Name</label>
                                <input type="text" class="form-control" id="partner_name" name="partner_name" aria-describedby="name" placeholder="Partner's Name">
                            </div>

                            <div class="form-group">
                                <label for="name">Partner's Gender</label>
                                <label class="radio-inline">
                                    <input type="radio" id="partner_gender_male" name="partner_gender" checked value="male">Male
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" id="partner_gender_female" name="partner_gender" value="female">Female
                                </label>
                            </div>

                            <!--
                        <div class="form-group">
                            <label for="address">Partner's Address</label>
                            <textarea class="form-control" id="partner_address" name="partner_address" placeholder="Lead's Address"></textarea>
                        </div>
                        -->

                            <div class="form-group">
                                <label for="contact_tel">Partner's Contact Telephone</label>
                                <input type="text" class="form-control" id="partner_telephone" name="partner_telephone" placeholder="Contact Telephone">
                            </div>

                            <div class="form-group">
                                <label for="mobile_num">Partner's Mobile #</label>
                                <input type="text" class="form-control" id="partner_mobile" name="partner_mobile" placeholder="Mobile #">
                            </div>

                            <div class="form-group">
                                <label for="email">Partner's Email Address</label>
                                <input type="email" class="form-control" id="partner_email" name="partner_email" aria-describedby="email" placeholder="Partner's Email">
                            </div>

                            <div class="form-group">
                                <label for="partner_age">Partner's Age</label>
                                <select class="form-control" id="partner_age" name="partner_age" aria-describedby="client_age">
                                    <option>N/A</option>
                                    <option value="<25">
                                        < 25</option> <option>25-39
                                    </option>
                                    <option>40-49</option>
                                    <option>50-59</option>
                                    <option value=">60">> 60</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="partner_occupation">Partner's Occupation</label>
                                <input type="text" class="form-control" id="partner_occupation" name="partner_occupation" aria-describedby="partner_occupation" placeholder="Partner's Work/Occupation" value="">
                            </div>

                            <div class="form-group">
                                <label for="partner_income">Partner's Income</label>
                                <select class="form-control" id="partner_income" name="partner_income" aria-describedby="civil_status">
                                    <option>N/A</option>
                                    <option>Under $50,000</option>
                                    <option>$50,000-$79,000</option>
                                    <option>$80,000-$100,000</option>
                                    <option>more than $100,000</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="health">Health Concerns:</label>
                                <input type="text" class="form-control partner_health_concerns" id="partner_health_concerns" name="partner_health_concerns" placeholder="Health Concern 1" value="">
                                <input type="text" class="form-control partner_health_concerns" id="partner_health_concerns" name="partner_health_concerns" placeholder="Health Concern 2" value="">
                                <input type="text" class="form-control partner_health_concerns" id="partner_health_concerns" name="partner_health_concerns" placeholder="Health Concern 3" value="">
                            </div>

                            <p></p>

                            <div class="form-group row">
                                <div class="col-sm-8">
                                    <h4 id="partner_smoking">Smoker
                                        <div class="pull-right">
                                            <input id="partner_is_smoking" name="partner_is_smoking" type="checkbox" />
                                        </div>
                                    </h4>
                                </div>
                            </div>
                            <p></p>
                            <div class="form-group row">
                                <div class="col-sm-8">
                                    <h4 id="partner_current_insurance">Already Has Insurance
                                        <div class="pull-right">
                                            <input id="partner_already_has_insurance" name="partner_has_insurance" type="checkbox" />
                                        </div>
                                    </h4>
                                </div>
                                <div class="col-sm-4">
                                    <button type="button" style="display:none;" id="add_partner_current_insurance" class="btn btn-primary form-control"><i class="fas fa-plus"></i> Add Insurance</button>
                                </div>
                            </div>

                            <div id="partner_current_insurance_div" style="display:none;">
                                <div class="row" id="partner_current_insurance0">
                                    <div class="form-group col-sm-5">
                                        <label for="partner_current_insurance_type0">Insurance For:</label>
                                        <select class="form-control partner_insurance_type" id="partner_current_insurance_type0" name="partner_current_insurance_type" aria-describedby="civil_status">
                                            <option value="" disabled="" hidden="" selected="">Select One</option>
                                            <option>N/A</option>
                                            <?php
                                            foreach ($app_data->insurance_types as $it) {
                                                $acronym = $it->acronym;

                                                echo "<option>$acronym</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="form-group col-sm-5">
                                        <label for="partner_current_insurance_company0">Insurance Company:</label>
                                        <select class="form-control partner_insurance_company" id="partner_current_insurance_company0" name="partner_current_insurance_company" aria-describedby="civil_status">
                                            <option value="" disabled="" hidden="" selected="">Select One</option>
                                            <option>N/A</option>
                                            <?php
                                            foreach ($app_data->insurance_companies as $ic) {
                                                $acronym = $ic->acronym;

                                                echo "<option>$acronym</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-2">
                                        <button type="button" class="btn btn-danger form-control remove_partner_current_insurance" style="margin-top:20px;" data-index="0"><i class="fas fa-times"></i></button>
                                    </div>
                                </div>
                            </div>
                            <hr>


                            <div id="partner_interested_in_div">
                                <h4>Partner is interested in:</h4>

                                <?php
                                foreach ($app_data->products as $product) {
                                    $final_acronym = $product->final_acronym;
                                    $interest_id_name = "partner_interested_in_" . $product->safe_name;
                                    ?>

                                <div class="col-sm-12">
                                    <input id="<?php echo $interest_id_name; ?>" name="<?php echo $interest_id_name; ?>" type="checkbox" />
                                    <label for="<?php echo $interest_id_name; ?>"> &nbsp; <?php echo $final_acronym ?> </label>
                                </div>
                                <?php
                                }
                                ?>
                            </div>
                        </div>


                        <hr>

                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" aria-describedby="notes" placeholder="Notes"></textarea>
                        </div>


                        <div class="form-group">
                            <label for="grade">Grade</label>
                            <select class="form-control" id="grade" name="grade" aria-describedby="grade">
                                <option>E</option>
                                <option selected>L</option>
                                <!--<option>I</option>-->
                            </select>
                        </div>

                        <button type="button" id="generate_lead" class="btn btn-success form-control"><i class="fas fa-check"></i> Generate Lead</button>
                        <p></p>
                        <button type="button" id="show_callback_modal_btn" class="btn btn-primary form-control" data-toggle="modal" data-target="#myModal"><i class="fas fa-sync-alt"></i> Callback</button>
                        <p></p>
                        <button type="button" id="reload_btn" onclick="location.reload(true);" class="btn btn-danger form-control"><i class="fas fa-eraser"></i> Reset</button>


                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #286090; ">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
                <h4 class="modal-title" id="myModalLabel" style="color:white;">Callback Editor</h4>
            </div>
            <div class="modal-body">
                <form id="frmCallback" name="frmScript" class="form-horizontal" novalidate="">
                    <div class="form-group">
                        <div class="col-sm-3">
                            <label for="callback_date">Date</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="text" id="callback_date" name="callback_date" class="form-control datepicker" aria-describedby="callback_date" placeholder="Date" value="<?php echo date('d/m/Y') ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-sm-3">
                            <label for="date">Time</label>
                        </div>
                        <div class="col-sm-9">
                            <input type="time" id="callback_time" name="callback_time" class="form-control">
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-3">
                            <label for="notes">Notes</label>
                        </div>
                        <div class="col-sm-9">
                            <textarea class="form-control" id="callback_notes" aria-describedby="callback_notes" placeholder="Notes"></textarea>
                        </div>
                    </div>
                    <button type="button" id="save_callback" class="btn btn-success form-control"><i class="fas fa-save"></i> Save Callback</button>

            </div>
            <input type="hidden" id="callback_id" name="callback_id" value="<?php
                                                                            if ($app_data->isLoadingSavedData)
                                                                                echo $app_data->callback_id;
                                                                            ?>">
            <input type="hidden" id="is_update" name="is_update" value="<?php
                                                                        echo ($app_data->isLoadingSavedData) ? "Yes" : "No";
                                                                        ?>">
            </form>
        </div>
    </div>
</div>
</div>

<script src="js/telemarketer_script.js"></script>
<script>
    $.fn.serializeObject = function() {
        var o = {};
        var a = this.serializeArray();
        $.each(a, function() {
            if (o[this.name]) {
                if (!o[this.name].push) {
                    o[this.name] = [o[this.name]];
                }
                o[this.name].push(this.value || '');
            } else {
                o[this.name] = this.value || '';
            }
        });
        return o;
    };

    $(function() {
        $('.datepicker').datepicker({
            dateFormat: 'dd/mm/yy'
        });

        $('input[type=radio][name=client_citizenship]').change(function() {
            if (this.value == 'Working VISA') {
                $("#working_visa_years_div").slideDown();
            } else {
                $("#working_visa_years_div").slideUp();
                $("#working_visa_years_div").slideUp();
            }
        });

        $("#partner_toggle").on("change", function() {
            var checked = $(this).is(":checked");
            if (checked) {
                $("#partner_div").slideDown();
            } else {
                $("#partner_div").slideUp();
            }
        });

        $("#client_already_has_insurance").on("click", function() {
            var checked = $(this).is(":checked");
            if (checked) {
                $("#add_client_current_insurance").slideDown();
                $("#client_current_insurance_div").slideDown();
            } else {
                $("#add_client_current_insurance").slideUp();
                $("#client_current_insurance_div").slideUp();
            }
        });

        $("#partner_already_has_insurance").on("click", function() {
            var checked = $(this).is(":checked");
            if (checked) {
                $("#add_partner_current_insurance").slideDown();
                $("#partner_current_insurance_div").slideDown();
            } else {
                $("#add_partner_current_insurance").slideUp();
                $("#partner_current_insurance_div").slideUp();
            }
        });

        $("#callback").on("click", function() {
            $('#formtype').val("add");
            $('#frmScript').trigger("reset");
        });

        $("#venue_type").on("change", function() {
            var vtype = $(this).val();
            var venue = $("#venue");
            if (vtype == "Other") {
                venue.val("");
                venue.slideDown();
            } else {
                venue.slideUp();
            }
        });

        $("#save_callback").on("click", function() {
            disableButtons();
            var has_partner = $("#partner_toggle").is(":checked");
            var client_has_insurance = $("#client_already_has_insurance").is(":checked");
            var partner_has_insurance = $("#partner_already_has_insurance").is(":checked");
            var vtype = $("#venue_type").val();
            var venue = $("#venue");
            var venue_address = venue.val();

            if (vtype == "Home") {
                venue_address = $("#client_address").val();
            } else if (vtype == "Company") {
                venue_address = $("#company_address").val();
            }

            venue.val(venue_address);

            console.info("Venue Address:" + venue.val());
            if (!has_partner) {
                $("#partner_div").remove();
            }

            if (!client_has_insurance) {
                $("#client_current_insurance_div").remove();
            }

            if (!partner_has_insurance) {
                $("#partner_current_insurance_div").remove();
            }

            var formData = $("#frmLead").serializeObject();

            var agent_id = $("#agent_id").val();

            //Lead Data
            var lead_data = JSON.stringify(formData);
            var name = $("#client_name").val();
            var notes = $("#notes").val();

            //Callback Data
            var callback_date = $("#callback_date").val();
            var callback_time = $("#callback_time").val();
            var callback_notes = $("#callback_notes").val();
            var callback_id = $("#callback_id").val();
            var is_update = $("#is_update").val();

            var data = {
                name: name,
                lead_data: lead_data,
                notes: notes,
                callback_date: callback_date,
                callback_time: callback_time,
                callback_notes: callback_notes,
                callback_id: callback_id,
                is_update: is_update,
                agent_id: agent_id
            };
            
            createFallback(data);
            
            console.log(lead_data);
            $.ajax({
                data: data,
                type: "post",
                url: "save_callback.php",
                success: function(data) {
                    console.log("Data Saved: ", data);
                    location.replace("main");
                },
                error: function(data) {
                    console.log("Error", data);
                    enableButtons();
                    alert("An error occurred, please contact the IT Support.");
                    $("#report_text").val(data.reason);
                }
            });
        });

        $("#generate_lead").on("click", function() {

            $.confirm({
                title: 'Confirm Lead Generation',
                content: "Are you sure that you want to proceed with generating this lead?",
                buttons: {
                    confirm: function() {
                        disableButtons();
                        this.buttons.confirm.disable();
                        var has_partner = $("#partner_toggle").is(":checked");

                        var client_has_insurance = $("#client_already_has_insurance").is(":checked");
                        var partner_has_insurance = $("#partner_already_has_insurance").is(":checked");
                        var vtype = $("#venue_type").val();
                        var venue = $("#venue");
                        var venue_address = venue.val();

                        if (vtype == "Home") {
                            venue_address = $("#client_address").val();
                        } else if (vtype == "Company") {
                            venue_address = $("#company_address").val();
                        }
                        venue.val(venue_address);

                        console.info("Venue Address" + venue.val());

                        if (!has_partner) {
                            $("#partner_div").remove();
                        }

                        if (!client_has_insurance) {
                            $("#client_current_insurance_div").remove();
                        }

                        if (!partner_has_insurance) {
                            $("#partner_current_insurance_div").remove();
                        }

                        var formData = $("#frmLead").serializeObject();

                        var agent_id = $("#agent_id").val();
                        //Lead Data
                        var lead_data = JSON.stringify(formData);
                        var name = $("#client_name").val();
                        var notes = $("#notes").val();

                        var callback_id = $("#callback_id").val();
                        var is_update = $("#is_update").val();

                        var data = {
                            name: name,
                            lead_data: lead_data,
                            notes: notes,
                            is_update: is_update,
                            callback_id: callback_id,
                            agent_id: agent_id
                        };
                        createFallback(data);
                        $.ajax({
                            data: data,
                            type: "post",
                            url: "telemarketer_generate_lead.php",
                            success: function(data) {
                                console.log("Lead Data ID: ", data);
                                var emailurl = "email_lead_data?id=" + data;

                                $.ajax({
                                    data: data,
                                    type: "get",
                                    url: emailurl,
                                    success: function(data) {
                                        console.log("Feedback: ", data);
                                        location.replace("main");
                                    },
                                    error: function(data) {
                                        console.log("Error Sending Mail", data);
                                        enableButtons();
                                        alert("An error occurred, please contact the IT Support.");

                                    }
                                });

                            },
                            error: function(data) {
                                console.log("Error", data);
                                $("#report_text").val(data.reason);
                            }
                        });
                    },
                    cancel: function() {

                    },

                }
            });

        });

        $("#test_number").on("click", function() {
            var number = $("#number_test").val();

            if (number == "")
                return;

            var data = {
                number: number
            }
            $.ajax({
                data: data,
                type: "post",
                url: "check_number_if_callable.php",
                success: function(data) {
                    console.log("Result: ", data);
                    var dnc = 0;
                    var called = 0;

                    if (Array.isArray(data)) {
                        data.forEach(function(item, index) {
                            if (item.status == "Do Not Call") {
                                dnc++;
                            } else {
                                called++;
                            }
                        });
                    }

                    if (dnc < 1 && called < 1) {
                        $("#number_test_feedback").html("Number is not registered as 'Called' or 'Do Not Call'");
                        $("#number_test_feedback").css("color", "green");
                        $("#frmLead").slideDown();
                    } else if (dnc > 0 && called < 1) {
                        $("#number_test_feedback").html("Number is registered as a \"Do Not Call\"");
                        $("#number_test_feedback").css("color", "red");
                        $("#frmLead").slideUp();
                    } else if (called > 0 && dnc < 1) {
                        $("#number_test_feedback").html("Number is registered as a \"Called\"");
                        $("#number_test_feedback").css("color", "red");
                        $("#frmLead").slideUp();
                    } else {
                        $("#number_test_feedback").html("Number is registered as a \"Do Not Call\"");
                        $("#number_test_feedback").css("color", "red");
                        $("#frmLead").slideUp();
                    }
                },
                error: function(data) {
                    console.log("Error", data);
                }
            });
        });

        <?php
        if ($app_data->isLoadingSavedData) {
            echo "LoadLeadData(" . json_encode($app_data->lead_data) . ");
                    LoadLeadDataIntoForm();";
        }
        ?>
    });

    function enableButtons() {
        document.getElementById("generate_lead").disabled = false;
        document.getElementById("show_callback_modal_btn").disabled = false;
        document.getElementById("reload_btn").disabled = false;
    }

    function disableButtons() {
        document.getElementById("generate_lead").disabled = true;
        document.getElementById("show_callback_modal_btn").disabled = true;
        document.getElementById("reload_btn").disabled = true;

    }

    function objectifyForm(formArray) {

        var returnArray = {};
        for (var i = 0; i < formArray.length; i++) {
            returnArray[formArray[i]['name']] = formArray[i]['value'];
        }
        return returnArray;
    }

    function download(filename, text) {

        var element = document.createElement('a');
        element.setAttribute('href', 'data:text/plain;charset=utf-8,' + encodeURIComponent(text));
        element.setAttribute('download', filename);

        element.style.display = 'none';
        document.body.appendChild(element);

        element.click();

        document.body.removeChild(element);

    }

    function createFallback(data) {

        console.log(data);
        lead_data = JSON.parse(data.lead_data);
        delete lead_data.agent;
        delete lead_data.agent_id;
        var text_data = {
            name: data.name,
            lead_data: lead_data,
            notes: data.notes
        }
        download(data.name + " Data.txt", JSON.stringify(text_data));

    }
</script>
</body>

</html>

<?php


function TelemarketerScriptTranslator($script)
{
    $script = str_replace("{{username}}", $_SESSION['myusername'], $script);

    $time_of_day = date('H');
    $time_of_day_string = "";
    if ($time_of_day < "12") {
        $time_of_day_string = "morning";
    } else
        /* If the time is grater than or equal to 1200 hours, but less than 1700 hours, so good afternoon */
        if ($time_of_day >= "12" && $time_of_day < "17") {
            $time_of_day_string = "afternoon";
        } else
            /* Should the time be between or equal to 1700 and 1900 hours, show good evening */
            if ($time_of_day >= "17" && $time_of_day < "19") {
                $time_of_day_string = "evening";
            } else
                /* Finally, show good night if the time is greater than or equal to 1900 hours */
                if ($time_of_day >= "19") {
                    $time_of_day_string = "night";
                }

    $script = str_replace("{{time_of_day}}", $time_of_day_string, $script);
    return $script;
}
?>