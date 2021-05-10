<?php
//var_dump($data->debug->client);
?>
<div class="row">
    <div class="col-sm-12">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Face-to-Face Marketer Leads Quarterly Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Generated</th>
                    <th scope="col" style="text-align:center;">Issued API</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->bdms_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->bdms_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->bdms_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->bdms_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->bdms_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->bdms_data->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_data->api, 2) ?></td>
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
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Telemarketer Leads Quarterly Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Generated</th>
                    <th scope="col" style="text-align:center;">Issued API</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->telemarketers_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->telemarketers_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->telemarketers_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->telemarketers_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->telemarketers_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->telemarketers_data->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_data->api, 2) ?></td>
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
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Self-Generated Leads Quarterly Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Generated</th>
                    <th scope="col" style="text-align:center;">Issued API</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->self_gen_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->self_gen_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->self_gen_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->self_gen_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->self_gen_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->self_gen_data->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_data->api, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<!--KiwiSaver-->

<div class="row">
    <div class="col-sm-12">
        <table class="table">
            <thead>
                <tr>
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Face-to-Face Marketer Quarterly KiwiSaver Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Issued KiwiSaver Deals</th>
                    <th scope="col" style="text-align:center;">Payment Amount</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->bdms_kiwisaver_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->bdms_kiwisaver_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->bdms_kiwisaver_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_kiwisaver_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->bdms_kiwisaver_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_kiwisaver_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->bdms_kiwisaver_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_kiwisaver_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->bdms_kiwisaver_data->quantity ?></td>
                    <td>$<?php echo number_format($data->bdms_kiwisaver_data->api, 2) ?></td>
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
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Telemarketer Quarterly KiwiSaver Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Issued KiwiSaver Deals</th>
                    <th scope="col" style="text-align:center;">Payment Amount</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->telemarketers_kiwisaver_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->telemarketers_kiwisaver_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->telemarketers_kiwisaver_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_kiwisaver_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->telemarketers_kiwisaver_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_kiwisaver_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->telemarketers_kiwisaver_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_kiwisaver_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->telemarketers_kiwisaver_data->quantity ?></td>
                    <td>$<?php echo number_format($data->telemarketers_kiwisaver_data->api, 2) ?></td>
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
                    <th colspan="3" style="text-align:center;"><?php echo $data->year ?> Self-Generated Quarterly KiwiSaver Data</th>
                </tr>
                <tr>
                    <th scope="col" style="text-align:center;">Quarter</th>
                    <th scope="col" style="text-align:center;">Issued KiwiSaver Deals</th>
                    <th scope="col" style="text-align:center;">Payment Amount</th>
                </tr>
            </thead>

            <tbody>
                <tr>
                    <th scope="row" style="text-align:center;">First</th>
                    <td style="text-align:center;"><?php echo $data->self_gen_kiwisaver_data->quarters[0]->quantity ?></td>
                    <td style="text-align:center;">$<?php echo number_format($data->self_gen_kiwisaver_data->quarters[0]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Second</th>

                    <td><?php echo $data->self_gen_kiwisaver_data->quarters[1]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_kiwisaver_data->quarters[1]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Third</th>
                    <td><?php echo $data->self_gen_kiwisaver_data->quarters[2]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_kiwisaver_data->quarters[2]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Fourth</th>
                    <td><?php echo $data->self_gen_kiwisaver_data->quarters[3]->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_kiwisaver_data->quarters[3]->api, 2) ?></td>
                </tr>
                <tr>
                    <th scope="row" style="text-align:center;">Total</th>
                    <td><?php echo $data->self_gen_kiwisaver_data->quantity ?></td>
                    <td>$<?php echo number_format($data->self_gen_kiwisaver_data->api, 2) ?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>