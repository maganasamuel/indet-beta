
<?php
    //Lead Generation Data
        //BDM
        $bdm_leads_generated_quarters = array();
        $bdm_api_quarters = array();
        $bdm_quarterly_total_generated = 0;
        $bdm_quarterly_total_api = 0;
        foreach($data->bdms_data->quarters as $quarter){
            $bdm_leads_generated_quarters[] = $quarter->quantity;
            $bdm_api_quarters[] = round($quarter->api, 2);

            $bdm_quarterly_total_generated += $quarter->quantity;
            $bdm_quarterly_total_api += round($quarter->api, 2);
        }
        //$bdm_leads_generated_quarters[] = $bdm_quarterly_total_generated;
        //$bdm_api_quarters[] = round($bdm_quarterly_total_api,2);

        $bdm_leads_generated_quarters = json_encode($bdm_leads_generated_quarters);
        $bdm_api_quarters = json_encode($bdm_api_quarters);

        //TM
        $tm_leads_generated_quarters = array();
        $tm_api_quarters = array();
        $tm_quarterly_total_generated = 0;
        $tm_quarterly_total_api = 0;
        foreach($data->telemarketers_data->quarters as $quarter){
            $tm_leads_generated_quarters[] = $quarter->quantity;
            $tm_api_quarters[] = round($quarter->api, 2);

            $tm_quarterly_total_generated += $quarter->quantity;
            $tm_quarterly_total_api += round($quarter->api, 2);
        }
        //$tm_leads_generated_quarters[] = $tm_quarterly_total_generated;
        //$tm_api_quarters[] = round($tm_quarterly_total_api,2);

        $tm_leads_generated_quarters = json_encode($tm_leads_generated_quarters);
        $tm_api_quarters = json_encode($tm_api_quarters);

        //sg
        $sg_leads_generated_quarters = array();
        $sg_api_quarters = array();
        $sg_quarterly_total_generated = 0;
        $sg_quarterly_total_api = 0;
        foreach($data->self_gen_data->quarters as $quarter){
            $sg_leads_generated_quarters[] = $quarter->quantity;
            $sg_api_quarters[] = round($quarter->api, 2);

            $sg_quarterly_total_generated += $quarter->quantity;
            $sg_quarterly_total_api += round($quarter->api, 2);
        }
        //$sg_leads_generated_quarters[] = $sg_quarterly_total_generated;
        //$sg_api_quarters[] = round($sg_quarterly_total_api,2);

        $sg_leads_generated_quarters = json_encode($sg_leads_generated_quarters);
        $sg_api_quarters = json_encode($sg_api_quarters);

    //Lead Generation Data
        //BDM
        $bdm_kiwisaver_deals_quarters = array();
        $bdm_commission_quarters = array();
        $bdm_quarterly_total_kiwisaver_deals = 0;
        $bdm_quarterly_total_commission = 0;
        foreach($data->bdms_kiwisaver_data->quarters as $quarter){
            $bdm_kiwisaver_deals_quarters[] = $quarter->quantity;
            $bdm_commission_quarters[] = round($quarter->api, 2);

            $bdm_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $bdm_quarterly_total_commission += round($quarter->api, 2);
        }
        //$bdm_kiwisaver_deals_quarters[] = $bdm_quarterly_total_kiwisaver_deals;
        //$bdm_commission_quarters[] = round($bdm_quarterly_total_commission,2);

        $bdm_kiwisaver_deals_quarters = json_encode($bdm_kiwisaver_deals_quarters);
        $bdm_commission_quarters = json_encode($bdm_commission_quarters);

        //TM
        $tm_kiwisaver_deals_quarters = array();
        $tm_commission_quarters = array();
        $tm_quarterly_total_kiwisaver_deals = 0;
        $tm_quarterly_total_commission = 0;
        foreach($data->telemarketers_kiwisaver_data->quarters as $quarter){
            $tm_kiwisaver_deals_quarters[] = $quarter->quantity;
            $tm_commission_quarters[] = round($quarter->api, 2);

            $tm_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $tm_quarterly_total_commission += round($quarter->api, 2);
        }
        //$tm_kiwisaver_deals_quarters[] = $tm_quarterly_total_kiwisaver_deals;
        //$tm_commission_quarters[] = round($tm_quarterly_total_commission,2);

        $tm_kiwisaver_deals_quarters = json_encode($tm_kiwisaver_deals_quarters);
        $tm_commission_quarters = json_encode($tm_commission_quarters);

        //sg
        $sg_kiwisaver_deals_quarters = array();
        $sg_commission_quarters = array();
        $sg_quarterly_total_kiwisaver_deals = 0;
        $sg_quarterly_total_commission = 0;
        foreach($data->self_gen_kiwisaver_data->quarters as $quarter){
            $sg_kiwisaver_deals_quarters[] = $quarter->quantity;
            $sg_commission_quarters[] = round($quarter->api, 2);

            $sg_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $sg_quarterly_total_commission += round($quarter->api, 2);
        }
        //$sg_kiwisaver_deals_quarters[] = $sg_quarterly_total_kiwisaver_deals;
        //$sg_commission_quarters[] = round($sg_quarterly_total_commission,2);

        $sg_kiwisaver_deals_quarters = json_encode($sg_kiwisaver_deals_quarters);
        $sg_commission_quarters = json_encode($sg_commission_quarters);

        //Production
        //Submissions Quarterly
        $submissions_quarters = array();
        $submissions_api_quarters = array();
        $submissions_quarters_total = 0;
        $submissions_quarters_total_api = 0;

        foreach($data->submissions_data->quarters as $quarter){
            $submissions_quarters[] = $quarter->quantity;
            $submissions_api_quarters[] = round($quarter->api, 2);

            $submissions_quarters_total += $quarter->quantity;
            $submissions_quarters_total_api += round($quarter->api, 2);
        }
        //$submissions_quarters[] = $submissions_quarters_total;
        //$submissions_api_quarters[] = round($submissions_quarters_total_api,2);

        $submissions_quarters = json_encode($submissions_quarters);
        $submissions_api_quarters = json_encode($submissions_api_quarters);
    
        //Issued Quarterly
        $issued_quarters = array();
        $issued_api_quarters = array();
        $issued_quarters_total = 0;
        $issued_quarters_total_api = 0;

        foreach($data->issued_data->quarters as $quarter){
            $issued_quarters[] = $quarter->quantity;
            $issued_api_quarters[] = round($quarter->api, 2);

            $issued_quarters_total += $quarter->quantity;
            $issued_quarters_total_api += round($quarter->api, 2);
        }
        //$issued_quarters[] = $issued_quarters_total;
        //$issued_api_quarters[] = round($issued_quarters_total_api,2);

        $issued_quarters = json_encode($issued_quarters);
        $issued_api_quarters = json_encode($issued_api_quarters);
        
        //Cancellations Quarterly
        $cancellations_quarters = array();
        $cancellations_api_quarters = array();
        $cancellations_quarters_total = 0;
        $cancellations_quarters_total_api = 0;

        foreach($data->cancellations_data->quarters as $quarter){
            $cancellations_quarters[] = $quarter->quantity;
            $cancellations_api_quarters[] = round($quarter->api, 2);

            $cancellations_quarters_total += $quarter->quantity;
            $cancellations_quarters_total_api += round($quarter->api, 2);
        }
        //$cancellations_quarters[] = $cancellations_quarters_total;
        //$cancellations_api_quarters[] = round($cancellations_quarters_total_api,2);

        $cancellations_quarters = json_encode($cancellations_quarters);
        $cancellations_api_quarters = json_encode($cancellations_api_quarters);

        //Cancellations Quarterly
        $kiwisavers_quarters = array();
        $kiwisavers_api_quarters = array();
        $kiwisavers_quarters_total = 0;
        $kiwisavers_quarters_total_api = 0;

        foreach($data->kiwisavers_data->quarters as $quarter){
            $kiwisavers_quarters[] = $quarter->quantity;
            $kiwisavers_api_quarters[] = round($quarter->api, 2);

            $kiwisavers_quarters_total += $quarter->quantity;
            $kiwisavers_quarters_total_api += round($quarter->api, 2);
        }

        //$cancellations_quarters[] = $cancellations_quarters_total;
        //$cancellations_api_quarters[] = round($cancellations_quarters_total_api,2);

        $kiwisavers_quarters = json_encode($kiwisavers_quarters);
        $kiwisavers_api_quarters = json_encode($kiwisavers_api_quarters);

        //Submissions Monthly
        $submissions_months = array();
        $submissions_api_months = array();
        $submissions_months_total = 0;
        $submissions_months_total_api = 0;

        foreach($data->submissions_data->months as $month){
            $submissions_months[] = $month->deals;
            $submissions_api_months[] = round($month->api, 2);

            $submissions_months_total += $month->deals;
            $submissions_months_total_api += round($month->api, 2);
        }
        //$submissions_months[] = $submissions_months_total;
        //$submissions_api_months[] = round($submissions_months_total_api,2);

        $submissions_months = json_encode($submissions_months);
        $submissions_api_months = json_encode($submissions_api_months);
    
        //Issued Monthly
        $issued_months = array();
        $issued_api_months = array();
        $issued_months_total = 0;
        $issued_months_total_api = 0;

        foreach($data->issued_data->months as $month){
            $issued_months[] = $month->deals;
            $issued_api_months[] = round($month->api, 2);

            $issued_months_total += $month->deals;
            $issued_months_total_api += round($month->api, 2);
        }
        //$issued_months[] = $issued_months_total;
        //$issued_api_months[] = round($issued_months_total_api,2);

        $issued_months = json_encode($issued_months);
        $issued_api_months = json_encode($issued_api_months);
        
        //Cancellations Monthly
        $cancellations_months = array();
        $cancellations_api_months = array();
        $cancellations_months_total = 0;
        $cancellations_months_total_api = 0;

        foreach($data->cancellations_data->months as $month){
            $cancellations_months[] = $month->deals;
            $cancellations_api_months[] = round($month->api, 2);

            $cancellations_months_total += $month->deals;
            $cancellations_months_total_api += round($month->api, 2);
        }
        //$cancellations_months[] = $cancellations_months_total;
        //$cancellations_api_months[] = round($cancellations_months_total_api,2);

        $cancellations_months = json_encode($cancellations_months);
        $cancellations_api_months = json_encode($cancellations_api_months);

        
        //Cancellations Monthly
        $kiwisavers_months = array();
        $kiwisavers_api_months = array();
        $kiwisavers_months_total = 0;
        $kiwisavers_months_total_api = 0;

        foreach($data->kiwisavers_data->months as $month){
            $kiwisavers_months[] = $month->deals;
            $kiwisavers_api_months[] = round($month->api, 2);

            $kiwisavers_months_total += $month->deals;
            $kiwisavers_months_total_api += round($month->api, 2);
        }
        //$kiwisavers_months[] = $kiwisavers_months_total;
        //$kiwisavers_api_months[] = round($kiwisavers_months_total_api,2);

        $kiwisavers_months = json_encode($kiwisavers_months);
        $kiwisavers_api_months = json_encode($kiwisavers_api_months);
?>