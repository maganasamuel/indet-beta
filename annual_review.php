<?php
include_once("libs/api/classes/general.class.php");
include_once("libs/api/controllers/AnnualReview.controller.php");

$app = new General();
$reviewController = new AnnualReviewController();

$client_id = "";
$html = "";
if (!empty($_POST)) {
    $client_id = $app->param($_POST, "client_id");
    
    $data = json_encode($_POST);
    $client_id = base64_decode(urldecode($client_id));

    $dataset = $reviewController->createReview(
        $client_id,
        $data
    );

    $dataset = $dataset->fetch_assoc();

    header("Location:email_annual_review_pdf?id=" . $dataset["id"]);
    
} else {
    if (isset($_GET["id"])) {
        $client_id = $app->param($_GET, "id");
        $html = '
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="row">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-6 text-center p-5 text-primary border border-5 border-primary rounded ">
                                    <h2 class="font-weight-bold">EliteInsure Annual Review Checklist</h2>
                                </div>
                                <div class="col-sm-3"></div>
                            </div>
                        </div>
                    </div>
                    <form id="annual_review_form" name="annual_review_form" method="POST" action="" class="m-5">
                        <input type="hidden" name="client_id" value="' . $client_id . '">
                        
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h3 class="font-weight-bold" style="text-decoration:underline;">In the last 12 months or since your last review:</h3>
                                    </div>
                                </div>
                            </div>
                        </div>
    
                        <!-- Question Number 1 -->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>1.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have any of your contact details changed, i.e. phone numbers, address details,
                                            etc.? If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="new_contact_info" data-show-if="Yes" id="contact_info_changed" name="contact_info_changed">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="new_contact_info" name="new_contact_info" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 2-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>2.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have your financial circumstances changed?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="financial_circumstances" name="financial_circumstances">
                                            <option>Improved</option>
                                            <option selected>Remained much the same</option>
                                            <option>Worsened</option>
                                        </select>
                                        <br>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 3-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>3.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have you had any financial issues and/or concerns? If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="financial_issues_and_concerns_details" data-show-if="Yes" id="financial_issues_and_concerns" name="financial_issues_and_concerns">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="financial_issues_and_concerns_details" name="financial_issues_and_concerns_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 4-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>4.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have your goals changed in any way? If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="new_goals_details" data-show-if="Yes" id="new_goals" name="new_goals">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="new_goals_details" name="new_goals_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 5-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>5.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are you ‘on track’ to achieve those goals? Please give further details.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="on_track_with_goals_details" data-show-if="Yes" id="on_track_with_goals" name="on_track_with_goals">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="on_track_with_goals_details" name="on_track_with_goals_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 6-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>6.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Has your surplus income changed? (This is your net household income less
                                            your total routine expenditure)?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="surplus_income_changed_value" data-show-if="Yes" id="surplus_income_changed" name="surplus_income_changed">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="surplus_income_changed_value" name="surplus_income_changed_value" placeholder="Change Value. Add a minus (-) sign if it decreased, just enter the value if it increased." style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 7-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>7.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Has the value of your property or any other significant assets changed?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="asset_value_changed_value" data-show-if="Yes" id="asset_value_changed" name="asset_value_changed">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="asset_value_changed_value" name="asset_value_changed_value" placeholder="Change Value. Add a minus (-) sign if it decreased, just enter the value if it increased." style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 8-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>8.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Has the amount of your borrowing changed?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="borrowing_amount_changed_value" data-show-if="Yes" id="borrowing_amount_changed" name="borrowing_amount_changed">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="borrowing_amount_changed_value" name="borrowing_amount_changed_value" placeholder="Change Value. Add a minus (-) sign if it decreased, just enter the value if it increased." style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 9-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>9.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Is your mortgage suitably structured and is the rate competitive?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="mortgage_structured_suitably" name="mortgage_structured_suitably">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 10-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>10.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Is your family trust operating correctly?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="family_trust_operating_correctly" name="family_trust_operating_correctly">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                            <option>Not Applicable</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 11-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>11.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Is your Will up to date?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="will_is_updated" name="will_is_updated">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 12-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>12.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are Enduring Powers of Attorneys in place?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="enduring_powers_of_attorneys" name="enduring_powers_of_attorneys">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 13-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>13.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are you maximising taxation benefits?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="maximising_taxation_benefits" name="maximising_taxation_benefits">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 14-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>14.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are you aware of the changes to taxation law and what impact these changes
                                            may have on you (if any)?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="aware_of_taxation_law_changes" name="aware_of_taxation_law_changes">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 15-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>15.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are there any health issues that might impact on your insurances and ongoing
                                            finances?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="has_health_issues" name="has_health_issues">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 16-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>16.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Is your insurance cover appropriate for your needs?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="insurance_appropriate_for_needs" name="insurance_appropriate_for_needs">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 17-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>17.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are your premiums affordable and do you feel you are receiving good value for
                                            your money?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="premiums_are_affordable" name="premiums_are_affordable">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 18-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>18.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have any changes occurred that may mean your insurance cover needs to be
                                            reconsidered?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="reconsidering_insurance_cover" name="reconsidering_insurance_cover">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 19-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>19.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are you happy with your current job(s) or are you considering a job change?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="contented_with_job" name="contented_with_job">
                                            <option>Happy with current job</option>
                                            <option selected>Considering a job change</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 20-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>20.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Has your marital status or family situation changed (i.e. married, divorced,
                                            separated, had children, death of family member?) If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="marital_status_changed_details" data-show-if="Yes" id="marital_status_changed" name="marital_status_changed">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="marital_status_changed_details" name="marital_status_changed_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 21-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>21.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Do you have an appropriate level of fire and general insurance cover?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control" id="fire_and_general_insurance_cover" name="fire_and_general_insurance_cover">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 22-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>22.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Have you been bequeathed any inheritances or received any additional money
                                            since our last review? If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="inherited_money_details" data-show-if="Yes" id="inherited_money" name="inherited_money">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="inherited_money_details" name="inherited_money_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 23-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>23.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Are you aware of any additional money you might receive in the coming 12
                                            months? If yes, please specify.</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <select class="form-control has-sub-option" data-sub-option="expecting_money_details" data-show-if="Yes" id="expecting_money" name="expecting_money">
                                            <option>Yes</option>
                                            <option selected>No</option>
                                        </select>
                                        <br>
                                        <textarea class="form-control" id="expecting_money_details" name="expecting_money_details" placeholder="Details" style="display:none;"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <!-- Question Number 24-->
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-1">
                                        <h4>24.</h4>
                                    </div>
                                    <div class="col-sm-8">
                                        <h4>Is there anything else you wish to advise that may affect your insurance cover
                                            that has not been covered here?</h4>
                                    </div>
                                    <div class="col-sm-3">
                                        <textarea class="form-control" id="extra_information" name="extra_information"></textarea>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
                        <div class="row mt-5">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-9">
                                    </div>
                                    <div class="col-sm-3">
                                        <button type="submit" class="btn btn-primary form-control">Complete Review</button>
                                    </div>
                                </div>
                                <hr>
                            </div>
                        </div>
    
    
                    </form>
                ';
    } else {
        $main_style = "height: 100vh; overflow: auto;";
        if (isset($_GET["message"])) {
            $message = $app->param($_GET, "message");

            switch ($message) {
                case "success":
                    $html = '
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-6 text-center p-5 text-success border border-5 border-success rounded ">
                                        <h2 class="font-weight-bold">Thank you for giving us your feedback.</h2>
                                    </div>
                                    <div class="col-sm-3"></div>
                                </div>
                            </div>
                        </div>
                    ';
                break;
                default:
                $html = '
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="row">
                                    <div class="col-sm-3"></div>
                                    <div class="col-sm-6 text-center p-5 text-danger border border-5 border-danger rounded ">
                                        <h2 class="font-weight-bold">It seems like you\'re lost.</h2>
                                    </div>
                                    <div class="col-sm-3"></div>
                                </div>
                            </div>
                        </div>
                    ';
                break;
            }
        }
        else{            
            $html = '
                <div class="row">
                    <div class="col-sm-12">
                        <div class="row">
                            <div class="col-sm-3"></div>
                            <div class="col-sm-6 text-center p-5 text-danger border border-5 border-danger rounded ">
                                <h2 class="font-weight-bold">It seems like you\'re lost.</h2>
                            </div>
                            <div class="col-sm-3"></div>
                        </div>
                    </div>
                </div>
            ';
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="robots" content="noindex, nofollow" />
    <meta name="description" content="">
    <meta name="author" content="Jesse">
    <!--
		    <link rel="icon" href="img/favicon.ico">
        -->
    <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">

    <title>EliteInsure Review Form</title>

    <!-- CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css">

    <!-- Script -->
    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d06313cddc.js"></script>

    <!-- Script -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js" integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js" integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>

    <!-- Font Awesome -->
    <script src="https://kit.fontawesome.com/d06313cddc.js"></script>
    <style>
        .border-5 {
            border-width: 5px !important;
        }

        .row .col-sm-1 {
            text-align: right;
        }
    </style>
</head>

<body>

    <main role="main" style="<?php echo $main_style; ?>">

        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <a class="navbar-brand" href="#"><img src="logo.png"></a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarText">
                <ul class="navbar-nav mr-auto">
                </ul>
                <span class="navbar-text">
                    <h1>
                        <a href="tel:0508123467" class="ml-5"> <i class="fas fa-phone-square text-primary"></i> 0508 123 467</a>
                        <a href="mailto:admin@eliteinsure.co.nz" class="ml-5"> <i class="fas fa-envelope text-primary"></i> admin@eliteinsure.co.nz </a>
                    </h1>
                </span>
            </div>
        </nav>

        <div class="main mt-5" style="padding-left:10%; padding-right:10%;">
            <?php echo $html ?>
        </div>
    </main><!-- /.container -->
    <footer class="footer">
        <div class="row mt-5">
            <div class="col-sm-12">
                <div class="row">
                    <div class="col-sm-12">
                        <hr>
                        <h4 class="text-right mr-5 text-dark">ELITEINSURE LIMITED: 3G/39 Mackelvie Street Grey Lynn 1021 Auckland NZ</h4>
                        <h4 class="text-right mr-5"><a href="tel:0508123467" class="text-dark">0508 123 467</a> | <a href="mailto:admin@eliteinsure.co.nz" class="text-dark">admin@eliteinsure.co.nz</a> | <a href="www.eliteinsure.co.nz">www.eliteinsure.co.nz</a></h4>
                    </div>
                </div>
                <hr>
            </div>
        </div>
    </footer>
    <script>
        $(function() {
            $('[data-toggle="tooltip"]').tooltip();

            $(".has-sub-option").on("change", function() {
                var value = $(this).val();
                var subopt = $(this).data("sub-option");
                var show_if = $(this).data("show-if");

                if (value == show_if) {
                    $("#" + subopt).slideDown();
                } else {
                    $("#" + subopt).slideUp({
                        complete: function() {
                            $("#" + subopt).val("");
                        }
                    });
                }

            });

            //This will disable all inputs inside the form
            //$("#annual_review_form :input").prop("disabled", true);
        });


        function escapeHtml(text) {
            var map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };

            return text.replace(/[&<>"']/g, function(m) {
                return map[m];
            });
        }
    </script>
</body>

</html>