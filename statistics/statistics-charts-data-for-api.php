
<?php
    //Lead Generation Data
        //BDM
        $data->bdm_leads_generated_quarters = array();
        $data->bdm_api_quarters = array();
        $data->bdm_quarterly_total_generated = 0;
        $data->bdm_quarterly_total_api = 0;
        foreach($data->bdms_data->quarters as $quarter){
            $data->bdm_leads_generated_quarters[] = $quarter->quantity;
            $data->bdm_api_quarters[] = round($quarter->api, 2);

            $data->bdm_quarterly_total_generated += $quarter->quantity;
            $data->bdm_quarterly_total_api += round($quarter->api, 2);
        }
        //$data->bdm_leads_generated_quarters[] = $data->bdm_quarterly_total_generated;
        //$data->bdm_api_quarters[] = round($data->bdm_quarterly_total_api,2);

        $data->bdm_leads_generated_quarters = json_encode($data->bdm_leads_generated_quarters);
        $data->bdm_api_quarters = json_encode($data->bdm_api_quarters);

        //TM
        $data->tm_leads_generated_quarters = array();
        $data->tm_api_quarters = array();
        $data->tm_quarterly_total_generated = 0;
        $data->tm_quarterly_total_api = 0;
        foreach($data->telemarketers_data->quarters as $quarter){
            $data->tm_leads_generated_quarters[] = $quarter->quantity;
            $data->tm_api_quarters[] = round($quarter->api, 2);

            $data->tm_quarterly_total_generated += $quarter->quantity;
            $data->tm_quarterly_total_api += round($quarter->api, 2);
        }
        //$data->tm_leads_generated_quarters[] = $data->tm_quarterly_total_generated;
        //$data->tm_api_quarters[] = round($data->tm_quarterly_total_api,2);

        $data->tm_leads_generated_quarters = json_encode($data->tm_leads_generated_quarters);
        $data->tm_api_quarters = json_encode($data->tm_api_quarters);

        //sg
        $data->sg_leads_generated_quarters = array();
        $data->sg_api_quarters = array();
        $data->sg_quarterly_total_generated = 0;
        $data->sg_quarterly_total_api = 0;
        foreach($data->self_gen_data->quarters as $quarter){
            $data->sg_leads_generated_quarters[] = $quarter->quantity;
            $data->sg_api_quarters[] = round($quarter->api, 2);

            $data->sg_quarterly_total_generated += $quarter->quantity;
            $data->sg_quarterly_total_api += round($quarter->api, 2);
        }
        //$data->sg_leads_generated_quarters[] = $data->sg_quarterly_total_generated;
        //$data->sg_api_quarters[] = round($data->sg_quarterly_total_api,2);

        $data->sg_leads_generated_quarters = json_encode($data->sg_leads_generated_quarters);
        $data->sg_api_quarters = json_encode($data->sg_api_quarters);

    //Lead Generation Data
        //BDM
        $data->bdm_kiwisaver_deals_quarters = array();
        $data->bdm_commission_quarters = array();
        $data->bdm_quarterly_total_kiwisaver_deals = 0;
        $data->bdm_quarterly_total_commission = 0;
        foreach($data->bdms_kiwisaver_data->quarters as $quarter){
            $data->bdm_kiwisaver_deals_quarters[] = $quarter->quantity;
            $data->bdm_commission_quarters[] = round($quarter->api, 2);

            $data->bdm_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $data->bdm_quarterly_total_commission += round($quarter->api, 2);
        }
        //$data->bdm_kiwisaver_deals_quarters[] = $data->bdm_quarterly_total_kiwisaver_deals;
        //$data->bdm_commission_quarters[] = round($data->bdm_quarterly_total_commission,2);

        $data->bdm_kiwisaver_deals_quarters = json_encode($data->bdm_kiwisaver_deals_quarters);
        $data->bdm_commission_quarters = json_encode($data->bdm_commission_quarters);

        //TM
        $data->tm_kiwisaver_deals_quarters = array();
        $data->tm_commission_quarters = array();
        $data->tm_quarterly_total_kiwisaver_deals = 0;
        $data->tm_quarterly_total_commission = 0;
        foreach($data->telemarketers_kiwisaver_data->quarters as $quarter){
            $data->tm_kiwisaver_deals_quarters[] = $quarter->quantity;
            $data->tm_commission_quarters[] = round($quarter->api, 2);

            $data->tm_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $data->tm_quarterly_total_commission += round($quarter->api, 2);
        }
        //$data->tm_kiwisaver_deals_quarters[] = $data->tm_quarterly_total_kiwisaver_deals;
        //$data->tm_commission_quarters[] = round($data->tm_quarterly_total_commission,2);

        $data->tm_kiwisaver_deals_quarters = json_encode($data->tm_kiwisaver_deals_quarters);
        $data->tm_commission_quarters = json_encode($data->tm_commission_quarters);

        //sg
        $data->sg_kiwisaver_deals_quarters = array();
        $data->sg_commission_quarters = array();
        $data->sg_quarterly_total_kiwisaver_deals = 0;
        $data->sg_quarterly_total_commission = 0;
        foreach($data->self_gen_kiwisaver_data->quarters as $quarter){
            $data->sg_kiwisaver_deals_quarters[] = $quarter->quantity;
            $data->sg_commission_quarters[] = round($quarter->api, 2);

            $data->sg_quarterly_total_kiwisaver_deals += $quarter->quantity;
            $data->sg_quarterly_total_commission += round($quarter->api, 2);
        }
        //$data->sg_kiwisaver_deals_quarters[] = $data->sg_quarterly_total_kiwisaver_deals;
        //$data->sg_commission_quarters[] = round($data->sg_quarterly_total_commission,2);

        $data->sg_kiwisaver_deals_quarters = json_encode($data->sg_kiwisaver_deals_quarters);
        $data->sg_commission_quarters = json_encode($data->sg_commission_quarters);

        //Production
        //Submissions Quarterly
        $data->submissions_quarters = array();
        $data->submissions_api_quarters = array();
        $data->submissions_quarters_total = 0;
        $data->submissions_quarters_total_api = 0;

        foreach($data->submissions_data->quarters as $quarter){
            $data->submissions_quarters[] = $quarter->quantity;
            $data->submissions_api_quarters[] = round($quarter->api, 2);

            $data->submissions_quarters_total += $quarter->quantity;
            $data->submissions_quarters_total_api += round($quarter->api, 2);
        }
        //$data->submissions_quarters[] = $data->submissions_quarters_total;
        //$data->submissions_api_quarters[] = round($data->submissions_quarters_total_api,2);

        $data->submissions_quarters = json_encode($data->submissions_quarters);
        $data->submissions_api_quarters = json_encode($data->submissions_api_quarters);
    
        //Issued Quarterly
        $data->issued_quarters = array();
        $data->issued_api_quarters = array();
        $data->issued_quarters_total = 0;
        $data->issued_quarters_total_api = 0;

        foreach($data->issued_data->quarters as $quarter){
            $data->issued_quarters[] = $quarter->quantity;
            $data->issued_api_quarters[] = round($quarter->api, 2);

            $data->issued_quarters_total += $quarter->quantity;
            $data->issued_quarters_total_api += round($quarter->api, 2);
        }
        //$data->issued_quarters[] = $data->issued_quarters_total;
        //$data->issued_api_quarters[] = round($data->issued_quarters_total_api,2);

        $data->issued_quarters = json_encode($data->issued_quarters);
        $data->issued_api_quarters = json_encode($data->issued_api_quarters);
        
        //Cancellations Quarterly
        $data->cancellations_quarters = array();
        $data->cancellations_api_quarters = array();
        $data->cancellations_quarters_total = 0;
        $data->cancellations_quarters_total_api = 0;

        foreach($data->cancellations_data->quarters as $quarter){
            $data->cancellations_quarters[] = $quarter->quantity;
            $data->cancellations_api_quarters[] = round($quarter->api, 2);

            $data->cancellations_quarters_total += $quarter->quantity;
            $data->cancellations_quarters_total_api += round($quarter->api, 2);
        }
        //$data->cancellations_quarters[] = $data->cancellations_quarters_total;
        //$data->cancellations_api_quarters[] = round($data->cancellations_quarters_total_api,2);

        $data->cancellations_quarters = json_encode($data->cancellations_quarters);
        $data->cancellations_api_quarters = json_encode($data->cancellations_api_quarters);

        //Cancellations Quarterly
        $data->kiwisavers_quarters = array();
        $data->kiwisavers_api_quarters = array();
        $data->kiwisavers_quarters_total = 0;
        $data->kiwisavers_quarters_total_api = 0;

        foreach($data->kiwisavers_data->quarters as $quarter){
            $data->kiwisavers_quarters[] = $quarter->quantity;
            $data->kiwisavers_api_quarters[] = round($quarter->api, 2);

            $data->kiwisavers_quarters_total += $quarter->quantity;
            $data->kiwisavers_quarters_total_api += round($quarter->api, 2);
        }

        //$data->cancellations_quarters[] = $data->cancellations_quarters_total;
        //$data->cancellations_api_quarters[] = round($data->cancellations_quarters_total_api,2);

        $data->kiwisavers_quarters = json_encode($data->kiwisavers_quarters);
        $data->kiwisavers_api_quarters = json_encode($data->kiwisavers_api_quarters);

        //Submissions Monthly
        $data->submissions_months = array();
        $data->submissions_api_months = array();
        $data->submissions_months_total = 0;
        $data->submissions_months_total_api = 0;

        foreach($data->submissions_data->months as $month){
            $data->submissions_months[] = $month->deals;
            $data->submissions_api_months[] = round($month->api, 2);

            $data->submissions_months_total += $month->deals;
            $data->submissions_months_total_api += round($month->api, 2);
        }
        //$data->submissions_months[] = $data->submissions_months_total;
        //$data->submissions_api_months[] = round($data->submissions_months_total_api,2);

        $data->submissions_months = json_encode($data->submissions_months);
        $data->submissions_api_months = json_encode($data->submissions_api_months);
    
        //Issued Monthly
        $data->issued_months = array();
        $data->issued_api_months = array();
        $data->issued_months_total = 0;
        $data->issued_months_total_api = 0;

        foreach($data->issued_data->months as $month){
            $data->issued_months[] = $month->deals;
            $data->issued_api_months[] = round($month->api, 2);

            $data->issued_months_total += $month->deals;
            $data->issued_months_total_api += round($month->api, 2);
        }
        //$data->issued_months[] = $data->issued_months_total;
        //$data->issued_api_months[] = round($data->issued_months_total_api,2);

        $data->issued_months = json_encode($data->issued_months);
        $data->issued_api_months = json_encode($data->issued_api_months);
        
        //Cancellations Monthly
        $data->cancellations_months = array();
        $data->cancellations_api_months = array();
        $data->cancellations_months_total = 0;
        $data->cancellations_months_total_api = 0;

        foreach($data->cancellations_data->months as $month){
            $data->cancellations_months[] = $month->deals;
            $data->cancellations_api_months[] = round($month->api, 2);

            $data->cancellations_months_total += $month->deals;
            $data->cancellations_months_total_api += round($month->api, 2);
        }
        //$data->cancellations_months[] = $data->cancellations_months_total;
        //$data->cancellations_api_months[] = round($data->cancellations_months_total_api,2);

        $data->cancellations_months = json_encode($data->cancellations_months);
        $data->cancellations_api_months = json_encode($data->cancellations_api_months);

        
        //Cancellations Monthly
        $data->kiwisavers_months = array();
        $data->kiwisavers_api_months = array();
        $data->kiwisavers_months_total = 0;
        $data->kiwisavers_months_total_api = 0;

        foreach($data->kiwisavers_data->months as $month){
            $data->kiwisavers_months[] = $month->deals;
            $data->kiwisavers_api_months[] = round($month->api, 2);

            $data->kiwisavers_months_total += $month->deals;
            $data->kiwisavers_months_total_api += round($month->api, 2);
        }
        //$data->kiwisavers_months[] = $data->kiwisavers_months_total;
        //$data->kiwisavers_api_months[] = round($data->kiwisavers_months_total_api,2);

        $data->kiwisavers_months = json_encode($data->kiwisavers_months);
        $data->kiwisavers_api_months = json_encode($data->kiwisavers_api_months);
?>