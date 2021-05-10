        <!-- Second Column -->
        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Submissions Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->quarters[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->submissions_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->submissions_data->api), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Submissions Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[4]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[5]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[6]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[7]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[8]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[9]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[10]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->submissions_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->submissions_data->months[11]->api, 2) ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">

            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Issued Policies Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->issued_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->issued_data->api), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Issued Policies Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[4]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[5]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[6]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[7]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[8]->deals ?></td>

                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[8]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[9]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[10]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->issued_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[11]->api, 2) ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Cancellations Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->quarters[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->cancellations_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->cancellations_data->api), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Cancellations Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[4]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[5]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[6]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[7]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[8]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[9]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[10]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->cancellations_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->cancellations_data->months[11]->api, 2) ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> KiwiSavers Quarterly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->quarters[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->quarters[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->quarters[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->quarters[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo ($data->kiwisavers_data->quantity) ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->kiwisavers_data->api), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> KiwiSavers Monthly Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Quantity</th>
                            <th scope="col" style="text-align:center;">API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[4]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[5]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[6]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[7]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[8]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[9]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[10]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->kiwisavers_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->kiwisavers_data->months[11]->api, 2) ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Quarterly Performance Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Quarter</th>
                            <th scope="col" style="text-align:center;">Leads</th>
                            <th scope="col" style="text-align:center;">Net API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">First</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[0]->quantity + $data->telemarketers_data->quarters[0]->quantity + $data->self_gen_data->quarters[0]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[0]->api - $data->cancellations_data->quarters[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Second</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[1]->quantity + $data->telemarketers_data->quarters[1]->quantity + $data->self_gen_data->quarters[1]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[1]->api - $data->cancellations_data->quarters[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Third</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[2]->quantity + $data->telemarketers_data->quarters[2]->quantity + $data->self_gen_data->quarters[2]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[2]->api - $data->cancellations_data->quarters[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Fourth</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quarters[3]->quantity + $data->telemarketers_data->quarters[3]->quantity + $data->self_gen_data->quarters[3]->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->quarters[3]->api - $data->cancellations_data->quarters[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">Total</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->quantity + $data->telemarketers_data->quantity + $data->self_gen_data->quantity ?></td>
                            <td style="text-align:center;">$<?php echo number_format(($data->issued_data->api - $data->cancellations_data->api), 2) ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-12">
                <table class="table">
                    <thead>
                        <tr>
                            <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Monthly Performance Data</th>
                        </tr>
                        <tr>
                            <th scope="col" style="text-align:center;">Month</th>
                            <th scope="col" style="text-align:center;">Leads</th>
                            <th scope="col" style="text-align:center;">Net API</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <th scope="row" style="text-align:center;">January</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[0]->deals + $data->telemarketers_data->months[0]->deals + $data->self_gen_data->months[0]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[0]->api - $data->cancellations_data->months[0]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">February</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[1]->deals + $data->telemarketers_data->months[1]->deals + $data->self_gen_data->months[1]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[1]->api - $data->cancellations_data->months[1]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">March</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[2]->deals + $data->telemarketers_data->months[2]->deals + $data->self_gen_data->months[2]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[2]->api - $data->cancellations_data->months[2]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">April</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[3]->deals + $data->telemarketers_data->months[3]->deals + $data->self_gen_data->months[3]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[3]->api - $data->cancellations_data->months[3]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">May</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[4]->deals + $data->telemarketers_data->months[4]->deals + $data->self_gen_data->months[4]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[4]->api - $data->cancellations_data->months[4]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">June</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[5]->deals + $data->telemarketers_data->months[5]->deals + $data->self_gen_data->months[5]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[5]->api - $data->cancellations_data->months[5]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">July</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[6]->deals + $data->telemarketers_data->months[6]->deals + $data->self_gen_data->months[6]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[6]->api - $data->cancellations_data->months[6]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">August</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[7]->deals + $data->telemarketers_data->months[7]->deals + $data->self_gen_data->months[7]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[7]->api - $data->cancellations_data->months[7]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">September</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[8]->deals + $data->telemarketers_data->months[8]->deals + $data->self_gen_data->months[8]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[8]->api - $data->cancellations_data->months[8]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">October</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[9]->deals + $data->telemarketers_data->months[9]->deals + $data->self_gen_data->months[9]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[9]->api - $data->cancellations_data->months[9]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">November</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[10]->deals + $data->telemarketers_data->months[10]->deals + $data->self_gen_data->months[10]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[10]->api - $data->cancellations_data->months[10]->api, 2) ?></td>
                        </tr>
                        <tr>
                            <th scope="row" style="text-align:center;">December</th>
                            <td style="text-align:center;"><?php echo $data->bdms_data->months[11]->deals + $data->telemarketers_data->months[11]->deals + $data->self_gen_data->months[11]->deals ?></td>
                            <td style="text-align:center;">$<?php echo number_format($data->issued_data->months[11]->api - $data->cancellations_data->months[11]->api, 2) ?></td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>